<?php

/**
 * ============================================================================
 * DATASET HELPER
 * ============================================================================
 * 
 * Path: app/Helpers/dataset_helper.php
 * 
 * Helper functions untuk dataset operations
 * ============================================================================
 */

if (!function_exists('format_file_size')) {
    /**
     * Format file size to human readable
     */
    function format_file_size(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

if (!function_exists('get_allowed_file_types')) {
    /**
     * Get allowed file types for dataset upload
     */
    function get_allowed_file_types(): array
    {
        return ['csv', 'xlsx', 'xls'];
    }
}

if (!function_exists('get_max_upload_size')) {
    /**
     * Get maximum upload size in bytes
     */
    function get_max_upload_size(): int
    {
        return 10 * 1024 * 1024; // 10MB
    }
}

if (!function_exists('validate_dataset_file')) {
    /**
     * Validate uploaded dataset file
     */
    function validate_dataset_file($file): array
    {
        $errors = [];

        if (!$file || !$file->isValid()) {
            $errors[] = 'File tidak valid';
            return $errors;
        }

        // Check file extension
        $extension = strtolower($file->getExtension());
        if (!in_array($extension, get_allowed_file_types())) {
            $errors[] = 'Format file tidak didukung. Gunakan: ' . implode(', ', get_allowed_file_types());
        }

        // Check file size
        if ($file->getSize() > get_max_upload_size()) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal: ' . format_file_size(get_max_upload_size());
        }

        return $errors;
    }
}

if (!function_exists('parse_csv_file')) {
    /**
     * Parse CSV file and return data array
     */
    function parse_csv_file(string $filePath, int $maxRows = 1000): array
    {
        $data = [];
        $headers = [];
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Read header
            $headers = fgetcsv($handle);
            
            // Read data rows
            $rowCount = 0;
            while (($row = fgetcsv($handle)) !== false && $rowCount < $maxRows) {
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, $row);
                    $rowCount++;
                }
            }
            
            fclose($handle);
        }
        
        return [
            'headers' => $headers,
            'data' => $data,
            'total_rows' => count($data)
        ];
    }
}

if (!function_exists('detect_column_types')) {
    /**
     * Detect column data types from dataset
     */
    function detect_column_types(array $data, array $headers): array
    {
        $types = [];
        
        foreach ($headers as $header) {
            $types[$header] = 'text'; // Default type
            
            // Sample first few values
            $sampleValues = array_slice(array_column($data, $header), 0, 10);
            
            $allNumeric = true;
            $allDate = true;
            
            foreach ($sampleValues as $value) {
                if (!is_numeric($value)) {
                    $allNumeric = false;
                }
                
                if (!strtotime($value)) {
                    $allDate = false;
                }
            }
            
            if ($allNumeric) {
                $types[$header] = strpos(implode('', $sampleValues), '.') !== false ? 'decimal' : 'integer';
            } elseif ($allDate) {
                $types[$header] = 'date';
            }
        }
        
        return $types;
    }
}

if (!function_exists('sanitize_column_name')) {
    /**
     * Sanitize column name for database
     */
    function sanitize_column_name(string $name): string
    {
        // Remove special characters, convert to lowercase, replace spaces with underscore
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9_]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        return trim($name, '_');
    }
}

if (!function_exists('generate_dataset_slug')) {
    /**
     * Generate unique slug for dataset
     */
    function generate_dataset_slug(string $name): string
    {
        $slug = url_title($name, '-', true);
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        return $slug . '-' . substr(md5(uniqid()), 0, 8);
    }
}

if (!function_exists('get_dataset_icon')) {
    /**
     * Get icon for dataset based on type or content
     */
    function get_dataset_icon(string $type = 'csv'): string
    {
        $icons = [
            'csv' => 'fa-file-csv',
            'xlsx' => 'fa-file-excel',
            'xls' => 'fa-file-excel',
            'json' => 'fa-file-code',
            'default' => 'fa-table'
        ];
        
        return $icons[$type] ?? $icons['default'];
    }
}

if (!function_exists('format_dataset_date')) {
    /**
     * Format dataset date to readable format
     */
    function format_dataset_date(?string $date): string
    {
        if (!$date) {
            return '-';
        }
        
        return date('d M Y, H:i', strtotime($date));
    }
}

if (!function_exists('get_dataset_status_badge')) {
    /**
     * Get HTML badge for dataset status
     */
    function get_dataset_status_badge(string $status): string
    {
        $badges = [
            'active' => '<span class="badge bg-success">Aktif</span>',
            'processing' => '<span class="badge bg-warning">Diproses</span>',
            'error' => '<span class="badge bg-danger">Error</span>',
            'inactive' => '<span class="badge bg-secondary">Tidak Aktif</span>'
        ];
        
        return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('calculate_dataset_stats')) {
    /**
     * Calculate basic statistics for numeric column
     */
    function calculate_dataset_stats(array $values): array
    {
        $numericValues = array_filter($values, 'is_numeric');
        
        if (empty($numericValues)) {
            return [
                'count' => 0,
                'sum' => 0,
                'avg' => 0,
                'min' => 0,
                'max' => 0
            ];
        }
        
        return [
            'count' => count($numericValues),
            'sum' => array_sum($numericValues),
            'avg' => array_sum($numericValues) / count($numericValues),
            'min' => min($numericValues),
            'max' => max($numericValues)
        ];
    }
}

if (!function_exists('export_dataset_to_csv')) {
    /**
     * Export dataset to CSV file
     */
    function export_dataset_to_csv(array $data, array $headers, string $filename): string
    {
        $filepath = WRITEPATH . 'uploads/' . $filename . '.csv';
        
        $fp = fopen($filepath, 'w');
        
        // Write header
        fputcsv($fp, $headers);
        
        // Write data
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        
        fclose($fp);
        
        return $filepath;
    }
}

if (!function_exists('get_dataset_preview')) {
    /**
     * Get preview of dataset (first N rows)
     */
    function get_dataset_preview(array $data, int $limit = 10): array
    {
        return array_slice($data, 0, $limit);
    }
}

if (!function_exists('search_dataset')) {
    /**
     * Search within dataset
     */
    function search_dataset(array $data, string $keyword): array
    {
        if (empty($keyword)) {
            return $data;
        }
        
        return array_filter($data, function($row) use ($keyword) {
            foreach ($row as $value) {
                if (stripos($value, $keyword) !== false) {
                    return true;
                }
            }
            return false;
        });
    }
}