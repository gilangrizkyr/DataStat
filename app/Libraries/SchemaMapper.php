<?php

/**
 * ============================================================================
 * SCHEMA MAPPER LIBRARY
 * ============================================================================
 * 
 * Path: app/Libraries/SchemaMapper.php
 * 
 * Deskripsi:
 * Library untuk mapping dan transformasi schema dataset.
 * Membantu konversi antara Excel columns dan database schema.
 * 
 * Features:
 * - Auto-generate field mapping
 * - Data type conversion
 * - Field validation
 * - Schema normalization
 * - Field aliasing
 * 
 * Used by: Owner/DatasetController (schema management)
 * ============================================================================
 */

namespace App\Libraries;

class SchemaMapper
{
    /**
     * Supported data types
     */
    const SUPPORTED_TYPES = [
        'string',
        'integer',
        'decimal',
        'date',
        'datetime',
        'boolean',
        'text'
    ];

    /**
     * Generate schema mapping dari headers
     */
    public function generateMapping($headers)
    {
        $mapping = [];

        foreach ($headers as $index => $header) {
            $mapping[] = [
                'index' => $index,
                'original_name' => $header,
                'field_name' => $this->sanitizeFieldName($header),
                'display_label' => $this->generateLabel($header),
                'type' => 'string', // Default type
                'required' => false,
                'unique' => false
            ];
        }

        return $mapping;
    }

    /**
     * Detect schema dari headers dan sample data (alias untuk generateMapping)
     */
    public function detectSchema($headers, $sampleData = [])
    {
        // Generate basic mapping
        $schema = $this->generateMapping($headers);
        
        // If sample data provided, try to detect types
        if (!empty($sampleData)) {
            foreach ($schema as &$field) {
                $fieldName = $field['field_name'];
                $type = $this->detectFieldType($fieldName, $sampleData);
                $field['type'] = $type;
            }
        }
        
        return $schema;
    }

    /**
     * Detect field type dari sample data
     */
    protected function detectFieldType($fieldName, $sampleData, $maxSamples = 100)
    {
        $types = [];
        $count = 0;
        
        foreach ($sampleData as $row) {
            if ($count >= $maxSamples) break;
            
            $value = $row[$fieldName] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            
            $types[] = $this->guessDataType($value);
            $count++;
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
        $lowerValue = strtolower(trim($value));
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
    public function sanitizeFieldName($name)
    {
        // Convert to lowercase
        $name = strtolower(trim($name));
        
        // Replace spaces and special chars
        $name = preg_replace('/[^a-z0-9_]/', '_', $name);
        
        // Remove multiple underscores
        $name = preg_replace('/_+/', '_', $name);
        
        // Trim underscores
        $name = trim($name, '_');
        
        // Ensure starts with letter
        if (!preg_match('/^[a-z]/', $name)) {
            $name = 'field_' . $name;
        }

        // Max length 64 characters
        if (strlen($name) > 64) {
            $name = substr($name, 0, 64);
        }

        return $name;
    }

    /**
     * Generate display label dari field name
     */
    public function generateLabel($fieldName)
    {
        // Remove underscores and capitalize
        $label = str_replace('_', ' ', $fieldName);
        $label = ucwords($label);
        
        return $label;
    }

    /**
     * Normalize schema structure
     */
    public function normalizeSchema($schema)
    {
        $normalized = [];

        foreach ($schema as $field) {
            $normalized[] = [
                'field_name' => $field['field_name'] ?? '',
                'original_name' => $field['original_name'] ?? $field['field_name'],
                'type' => $this->normalizeType($field['type'] ?? 'string'),
                'label' => $field['label'] ?? $this->generateLabel($field['field_name']),
                'required' => (bool)($field['required'] ?? false),
                'unique' => (bool)($field['unique'] ?? false),
                'description' => $field['description'] ?? ''
            ];
        }

        return $normalized;
    }

    /**
     * Normalize data type
     */
    public function normalizeType($type)
    {
        $type = strtolower(trim($type));

        // Map common variations
        $typeMap = [
            'int' => 'integer',
            'number' => 'decimal',
            'float' => 'decimal',
            'double' => 'decimal',
            'bool' => 'boolean',
            'timestamp' => 'datetime',
            'varchar' => 'string',
            'char' => 'string'
        ];

        $type = $typeMap[$type] ?? $type;

        // Validate type
        if (!in_array($type, self::SUPPORTED_TYPES)) {
            $type = 'string';
        }

        return $type;
    }

    /**
     * Convert value berdasarkan type
     */
    public function convertValue($value, $type)
    {
        if ($value === null || $value === '') {
            return null;
        }

        switch ($type) {
            case 'integer':
                return (int)$value;

            case 'decimal':
                return (float)$value;

            case 'boolean':
                return $this->convertToBoolean($value);

            case 'date':
                return $this->convertToDate($value);

            case 'datetime':
                return $this->convertToDatetime($value);

            case 'string':
            case 'text':
            default:
                return (string)$value;
        }
    }

    /**
     * Convert to boolean
     */
    protected function convertToBoolean($value)
    {
        $lowerValue = strtolower(trim($value));
        
        $trueValues = ['true', 'yes', '1', 'ya', 'y', 't'];
        $falseValues = ['false', 'no', '0', 'tidak', 'n', 'f'];

        if (in_array($lowerValue, $trueValues)) {
            return true;
        }

        if (in_array($lowerValue, $falseValues)) {
            return false;
        }

        // Default to boolean cast
        return (bool)$value;
    }

    /**
     * Convert to date
     */
    protected function convertToDate($value)
    {
        try {
            $timestamp = strtotime($value);
            if ($timestamp === false) {
                return null;
            }
            return date('Y-m-d', $timestamp);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert to datetime
     */
    protected function convertToDatetime($value)
    {
        try {
            $timestamp = strtotime($value);
            if ($timestamp === false) {
                return null;
            }
            return date('Y-m-d H:i:s', $timestamp);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Transform row data berdasarkan schema
     */
    public function transformRow($rowData, $schema)
    {
        $transformed = [];

        foreach ($schema as $field) {
            $fieldName = $field['field_name'];
            $originalName = $field['original_name'] ?? $fieldName;
            $type = $field['type'];

            // Get value from original name or field name
            $value = $rowData[$originalName] ?? $rowData[$fieldName] ?? null;

            // Convert value
            $transformed[$fieldName] = $this->convertValue($value, $type);
        }

        return $transformed;
    }

    /**
     * Validate schema structure
     */
    public function validateSchema($schema)
    {
        $errors = [];

        if (empty($schema)) {
            $errors[] = 'Schema tidak boleh kosong';
            return $errors;
        }

        $fieldNames = [];

        foreach ($schema as $index => $field) {
            // Check required fields
            if (empty($field['field_name'])) {
                $errors[] = "Field #{$index}: field_name harus diisi";
            }

            // Check duplicate field names
            $fieldName = $field['field_name'];
            if (in_array($fieldName, $fieldNames)) {
                $errors[] = "Field #{$index}: field_name '{$fieldName}' duplikat";
            }
            $fieldNames[] = $fieldName;

            // Check type
            if (!empty($field['type'])) {
                $type = $this->normalizeType($field['type']);
                if (!in_array($type, self::SUPPORTED_TYPES)) {
                    $errors[] = "Field #{$index}: type '{$field['type']}' tidak didukung";
                }
            }

            // Check field name format
            if (!preg_match('/^[a-z][a-z0-9_]*$/', $fieldName)) {
                $errors[] = "Field #{$index}: field_name '{$fieldName}' format tidak valid (harus lowercase, alphanumeric + underscore, diawali huruf)";
            }
        }

        return $errors;
    }

    /**
     * Get field by name
     */
    public function getField($schema, $fieldName)
    {
        foreach ($schema as $field) {
            if ($field['field_name'] === $fieldName) {
                return $field;
            }
        }
        return null;
    }

    /**
     * Add field to schema
     */
    public function addField($schema, $field)
    {
        // Normalize field
        $field = [
            'field_name' => $field['field_name'],
            'original_name' => $field['original_name'] ?? $field['field_name'],
            'type' => $this->normalizeType($field['type'] ?? 'string'),
            'label' => $field['label'] ?? $this->generateLabel($field['field_name']),
            'required' => (bool)($field['required'] ?? false),
            'unique' => (bool)($field['unique'] ?? false),
            'description' => $field['description'] ?? ''
        ];

        $schema[] = $field;
        return $schema;
    }

    /**
     * Update field in schema
     */
    public function updateField($schema, $fieldName, $updates)
    {
        foreach ($schema as &$field) {
            if ($field['field_name'] === $fieldName) {
                $field = array_merge($field, $updates);
                
                // Normalize type if updated
                if (isset($updates['type'])) {
                    $field['type'] = $this->normalizeType($updates['type']);
                }
                
                break;
            }
        }
        return $schema;
    }

    /**
     * Remove field from schema
     */
    public function removeField($schema, $fieldName)
    {
        return array_filter($schema, function($field) use ($fieldName) {
            return $field['field_name'] !== $fieldName;
        });
    }

    /**
     * Get schema summary
     */
    public function getSchemaSummary($schema)
    {
        $typeCounts = [];
        $requiredCount = 0;
        $uniqueCount = 0;

        foreach ($schema as $field) {
            $type = $field['type'];
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;

            if ($field['required'] ?? false) {
                $requiredCount++;
            }

            if ($field['unique'] ?? false) {
                $uniqueCount++;
            }
        }

        return [
            'total_fields' => count($schema),
            'type_distribution' => $typeCounts,
            'required_fields' => $requiredCount,
            'unique_fields' => $uniqueCount
        ];
    }
}