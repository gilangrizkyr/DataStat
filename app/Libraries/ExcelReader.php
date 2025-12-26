<?php

/**
 * ============================================================================
 * EXCEL READER LIBRARY
 * ============================================================================
 * 
 * Path: app/Libraries/ExcelReader.php
 * 
 * Deskripsi:
 * Library untuk membaca file Excel (.xlsx, .xls) menggunakan PhpSpreadsheet.
 * Menghandle auto-detect schema, read data, validasi, dan konversi ke array.
 * 
 * Dependencies:
 * - PhpSpreadsheet (composer require phpoffice/phpspreadsheet)
 * 
 * Features:
 * - Read Excel files (xlsx, xls)
 * - Auto-detect column headers
 * - Auto-detect data types
 * - Get schema information
 * - Get data as array
 * - Pagination support
 * - Memory efficient (for large files)
 * 
 * Used by: Owner/DatasetController (untuk upload dataset)
 * ============================================================================
 */

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExcelReader
{
    protected $spreadsheet;
    protected $worksheet;
    protected $headers = [];
    protected $totalRows = 0;
    protected $totalColumns = 0;
    protected $filePath;

    /**
     * Load Excel file
     */
    public function load($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File tidak ditemukan: {$filePath}");
        }

        $this->filePath = $filePath;

        try {
            // Load spreadsheet
            $this->spreadsheet = IOFactory::load($filePath);
            $this->worksheet = $this->spreadsheet->getActiveSheet();

            // Get dimensions
            $highestRow = $this->worksheet->getHighestRow();
            $highestColumn = $this->worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $this->totalRows = $highestRow - 1; // Exclude header row
            $this->totalColumns = $highestColumnIndex;

            // Read headers (first row)
            $this->readHeaders();

            return $this;
        } catch (\Exception $e) {
            throw new \Exception("Error membaca file Excel: " . $e->getMessage());
        }
    }

    /**
     * Read headers dari row pertama
     */
    protected function readHeaders()
    {
        $this->headers = [];

        for ($col = 1; $col <= $this->totalColumns; $col++) {
            $coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1';
            $cellValue = $this->worksheet->getCell($coordinate)->getValue();

            // Clean header name
            $header = trim($cellValue);
            if (empty($header)) {
                $header = 'Column_' . $col;
            }

            $this->headers[] = $header;
        }
    }

    /**
     * Get headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get total rows (excluding header)
     */
    public function getTotalRows()
    {
        return $this->totalRows;
    }

    /**
     * Get total columns
     */
    public function getTotalColumns()
    {
        return $this->totalColumns;
    }

    /**
     * Auto-detect schema dari data
     */
    public function detectSchema($sampleRows = 100)
    {
        $schema = [];

        foreach ($this->headers as $index => $header) {
            $colIndex = $index + 1;

            $schema[] = [
                'field_name' => $this->sanitizeFieldName($header),
                'original_name' => $header,
                'type' => $this->detectColumnType($colIndex, $sampleRows),
                'label' => $header
            ];
        }

        return $schema;
    }

    /**
     * Detect column data type
     */
    protected function detectColumnType($colIndex, $sampleRows = 100)
    {
        $types = [];
        $maxRows = min($sampleRows + 1, $this->totalRows + 1); // +1 karena skip header

        for ($row = 2; $row <= $maxRows; $row++) {
            $coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row;
            $cellValue = $this->worksheet->getCell($coordinate)->getValue();

            if ($cellValue === null || $cellValue === '') {
                continue;
            }

            $types[] = $this->guessDataType($cellValue);
        }

        // Majority voting
        if (empty($types)) {
            return 'string';
        }

        $typeCounts = array_count_values($types);
        arsort($typeCounts);

        return key($typeCounts);
    }

    /**
     * Guess data type dari value
     */
    protected function guessDataType($value)
    {
        // Check if numeric
        if (is_numeric($value)) {
            // Check if integer
            if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
                return 'integer';
            }
            return 'decimal';
        }

        // Check if date
        if ($this->isDate($value)) {
            return 'date';
        }

        // Check if boolean
        $lowerValue = strtolower($value);
        if (in_array($lowerValue, ['true', 'false', 'yes', 'no', '1', '0', 'ya', 'tidak'])) {
            return 'boolean';
        }

        // Default to string
        return 'string';
    }

    /**
     * Check if value is date
     */
    protected function isDate($value)
    {
        // Try to parse as date
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return false;
        }

        // Check if valid date format
        $date = date('Y-m-d', $timestamp);
        return strtotime($date) !== false;
    }

    /**
     * Sanitize field name untuk database
     */
    protected function sanitizeFieldName($name)
    {
        // Convert to lowercase
        $name = strtolower($name);

        // Replace spaces and special chars with underscore
        $name = preg_replace('/[^a-z0-9_]/', '_', $name);

        // Remove multiple underscores
        $name = preg_replace('/_+/', '_', $name);

        // Trim underscores
        $name = trim($name, '_');

        // Ensure starts with letter
        if (!preg_match('/^[a-z]/', $name)) {
            $name = 'col_' . $name;
        }

        return $name;
    }

    /**
     * Get all data as array
     */
    public function getData($startRow = 2, $limit = null)
    {
        $data = [];
        $endRow = $limit ? min($startRow + $limit - 1, $this->totalRows + 1) : $this->totalRows + 1;

        for ($row = $startRow; $row <= $endRow; $row++) {
            $rowData = [];

            for ($col = 1; $col <= $this->totalColumns; $col++) {
                $coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $cellValue = $this->worksheet->getCell($coordinate)->getValue();
                $fieldName = $this->sanitizeFieldName($this->headers[$col - 1]);
                $rowData[$fieldName] = $cellValue;
            }

            $data[] = $rowData;
        }

        return $data;
    }

    /**
     * Get data chunk (untuk batch processing)
     */
    public function getDataChunk($chunkSize = 500, $offset = 0)
    {
        $startRow = $offset + 2; // +2 karena row 1 = header, start from 2
        return $this->getData($startRow, $chunkSize);
    }

    /**
     * Get row count untuk chunking
     */
    public function getTotalChunks($chunkSize = 500)
    {
        return ceil($this->totalRows / $chunkSize);
    }

    /**
     * Read file dan return format lengkap (helper method untuk backward compatibility)
     */
    public function read($filePath)
    {
        $this->load($filePath);

        return [
            'headers' => $this->getHeaders(),
            'data' => $this->getData(),
            'total_rows' => $this->getTotalRows(),
            'total_columns' => $this->getTotalColumns(),
            'file_info' => $this->getFileInfo()
        ];
    }

    /**
     * Get data dengan original column names
     */
    public function getDataWithOriginalNames($startRow = 2, $limit = null)
    {
        $data = [];
        $endRow = $limit ? min($startRow + $limit - 1, $this->totalRows + 1) : $this->totalRows + 1;

        for ($row = $startRow; $row <= $endRow; $row++) {
            $rowData = [];

            for ($col = 1; $col <= $this->totalColumns; $col++) {
                $coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $cellValue = $this->worksheet->getCell($coordinate)->getValue();
                $rowData[$this->headers[$col - 1]] = $cellValue;
            }

            $data[] = $rowData;
        }

        return $data;
    }

    /**
     * Validate file format
     */
    public static function validateFile($filePath)
    {
        $errors = [];

        // Check file exists
        if (!file_exists($filePath)) {
            $errors[] = 'File tidak ditemukan';
            return $errors;
        }

        // Check file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls'])) {
            $errors[] = 'Format file harus .xlsx atau .xls';
        }

        // Check file size (max 10MB)
        $fileSize = filesize($filePath);
        if ($fileSize > 10 * 1024 * 1024) {
            $errors[] = 'Ukuran file maksimal 10MB';
        }

        // Try to load
        try {
            $reader = new self();
            $reader->load($filePath);

            // Check if has data
            if ($reader->getTotalRows() == 0) {
                $errors[] = 'File Excel kosong';
            }
        } catch (\Exception $e) {
            $errors[] = 'File Excel tidak valid: ' . $e->getMessage();
        }

        return $errors;
    }

    /**
     * Get file info
     */
    public function getFileInfo()
    {
        return [
            'file_path' => $this->filePath,
            'file_name' => basename($this->filePath),
            'file_size' => filesize($this->filePath),
            'total_rows' => $this->totalRows,
            'total_columns' => $this->totalColumns,
            'headers' => $this->headers,
            'sheet_name' => $this->worksheet->getTitle()
        ];
    }

    /**
     * Close and free memory
     */
    public function close()
    {
        if ($this->spreadsheet) {
            $this->spreadsheet->disconnectWorksheets();
            unset($this->spreadsheet);
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}
