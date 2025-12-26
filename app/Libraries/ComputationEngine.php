<?php

/**
 * ============================================================================
 * COMPUTATION ENGINE LIBRARY
 * ============================================================================
 * 
 * Path: app/Libraries/ComputationEngine.php
 * 
 * Deskripsi:
 * Library untuk menghitung statistik berdasarkan konfigurasi.
 * Mendukung berbagai metric types dan operasi kompleks.
 * 
 * Supported Metrics:
 * - count: Hitung jumlah data
 * - sum: Total nilai field
 * - average: Rata-rata
 * - min: Nilai minimum
 * - max: Nilai maksimum
 * - percentage: Persentase
 * - ratio: Rasio antar field
 * - growth: Pertumbuhan (YoY, MoM, dll)
 * - ranking: Ranking/urutan
 * - custom_formula: Formula custom
 * 
 * Features:
 * - Group by support
 * - Filter conditions (AND/OR)
 * - Sorting & limiting
 * - Result caching
 * - Multiple aggregations
 * 
 * Used by: Owner/StatisticController, Viewer/StatisticController
 * ============================================================================
 */

namespace App\Libraries;

use App\Models\Owner\DatasetRecordModel;

class ComputationEngine
{
    protected $datasetRecordModel;
    protected $dataset;
    protected $records = [];
    protected $schema = [];

    public function __construct()
    {
        $this->datasetRecordModel = new DatasetRecordModel();
    }

    /**
     * Calculate statistic berdasarkan config
     * 
     * Supports two signatures:
     * 1. calculate($datasetId, $config) - Standard
     * 2. calculate($config) - If config contains dataset_id
     */
    public function calculate($datasetIdOrConfig, $config = null)
    {
        // Detect signature
        if ($config === null && is_array($datasetIdOrConfig)) {
            // Signature 2: calculate($config)
            $config = $datasetIdOrConfig;
            $datasetId = $config['dataset_id'] ?? null;
            
            if (!$datasetId) {
                throw new \Exception("dataset_id required in config");
            }
        } else {
            // Signature 1: calculate($datasetId, $config)
            $datasetId = $datasetIdOrConfig;
        }

        // Load dataset schema
        $datasetModel = new \App\Models\Owner\DatasetModel();
        $this->dataset = $datasetModel->find($datasetId);
        
        if (!$this->dataset) {
            throw new \Exception("Dataset tidak ditemukan");
        }

        $this->schema = json_decode($this->dataset['schema_config'], true);

        // Load data records
        $this->loadRecords($datasetId, $config);

        // Apply filters
        if (!empty($config['filters'])) {
            $this->records = $this->applyFilters($this->records, $config['filters']);
        }

        // Calculate berdasarkan metric type
        $metricType = $config['metric_type'];
        
        switch ($metricType) {
            case 'count':
                $result = $this->calculateCount($config);
                break;
            
            case 'sum':
                $result = $this->calculateSum($config);
                break;
            
            case 'average':
                $result = $this->calculateAverage($config);
                break;
            
            case 'min':
                $result = $this->calculateMin($config);
                break;
            
            case 'max':
                $result = $this->calculateMax($config);
                break;
            
            case 'percentage':
                $result = $this->calculatePercentage($config);
                break;
            
            case 'ratio':
                $result = $this->calculateRatio($config);
                break;
            
            case 'growth':
                $result = $this->calculateGrowth($config);
                break;
            
            case 'ranking':
                $result = $this->calculateRanking($config);
                break;
            
            case 'custom_formula':
                $result = $this->calculateCustomFormula($config);
                break;
            
            default:
                throw new \Exception("Metric type '{$metricType}' tidak didukung");
        }

        // Apply sorting
        if (!empty($config['sort_by'])) {
            $result = $this->applySorting($result, $config);
        }

        // Apply limit
        if (!empty($config['limit_rows'])) {
            $result = $this->applyLimit($result, $config['limit_rows']);
        }

        return [
            'data' => $result,
            'metadata' => [
                'total_rows' => count($result),
                'metric_type' => $metricType,
                'calculated_at' => date('Y-m-d H:i:s'),
                'dataset_name' => $this->dataset['dataset_name']
            ]
        ];
    }

    /**
     * Load records dari database
     */
    protected function loadRecords($datasetId, $config)
    {
        $records = $this->datasetRecordModel->getByDataset($datasetId);
        
        $this->records = [];
        foreach ($records as $record) {
            $this->records[] = json_decode($record['data_json'], true);
        }
    }

    /**
     * Apply filters
     */
    protected function applyFilters($records, $filters)
    {
        if (empty($filters)) {
            return $records;
        }

        $logic = $filters['logic'] ?? 'AND'; // AND or OR
        $conditions = $filters['conditions'] ?? [];

        return array_filter($records, function($record) use ($conditions, $logic) {
            $results = [];

            foreach ($conditions as $condition) {
                $field = $condition['field'];
                $operator = $condition['operator'];
                $value = $condition['value'];

                $recordValue = $record[$field] ?? null;
                $results[] = $this->evaluateCondition($recordValue, $operator, $value);
            }

            // Apply logic
            if ($logic === 'OR') {
                return in_array(true, $results);
            } else {
                return !in_array(false, $results);
            }
        });
    }

    /**
     * Evaluate single condition
     */
    protected function evaluateCondition($recordValue, $operator, $compareValue)
    {
        switch ($operator) {
            case '=':
            case '==':
                return $recordValue == $compareValue;
            
            case '!=':
                return $recordValue != $compareValue;
            
            case '>':
                return $recordValue > $compareValue;
            
            case '>=':
                return $recordValue >= $compareValue;
            
            case '<':
                return $recordValue < $compareValue;
            
            case '<=':
                return $recordValue <= $compareValue;
            
            case 'contains':
                return stripos($recordValue, $compareValue) !== false;
            
            case 'not_contains':
                return stripos($recordValue, $compareValue) === false;
            
            case 'starts_with':
                return stripos($recordValue, $compareValue) === 0;
            
            case 'ends_with':
                return substr($recordValue, -strlen($compareValue)) === $compareValue;
            
            case 'in':
                $values = is_array($compareValue) ? $compareValue : explode(',', $compareValue);
                return in_array($recordValue, $values);
            
            case 'not_in':
                $values = is_array($compareValue) ? $compareValue : explode(',', $compareValue);
                return !in_array($recordValue, $values);
            
            case 'is_null':
                return $recordValue === null || $recordValue === '';
            
            case 'is_not_null':
                return $recordValue !== null && $recordValue !== '';
            
            default:
                return false;
        }
    }

    /**
     * Calculate COUNT
     */
    protected function calculateCount($config)
    {
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Simple count
            return [
                [
                    'label' => 'Total',
                    'value' => count($this->records)
                ]
            ];
        }

        // Group by
        return $this->groupAndCount($groupByFields);
    }

    /**
     * Group and count
     */
    protected function groupAndCount($groupByFields)
    {
        $grouped = $this->groupRecords($groupByFields);
        
        $result = [];
        foreach ($grouped as $key => $records) {
            $result[] = [
                'label' => $key,
                'value' => count($records)
            ];
        }

        return $result;
    }

    /**
     * Calculate SUM
     */
    protected function calculateSum($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Simple sum
            $sum = 0;
            foreach ($this->records as $record) {
                $sum += floatval($record[$targetField] ?? 0);
            }

            return [
                [
                    'label' => 'Total',
                    'value' => $sum
                ]
            ];
        }

        // Group by and sum
        return $this->groupAndAggregate($groupByFields, $targetField, 'sum');
    }

    /**
     * Calculate AVERAGE
     */
    protected function calculateAverage($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Simple average
            $sum = 0;
            $count = 0;
            
            foreach ($this->records as $record) {
                $value = floatval($record[$targetField] ?? 0);
                $sum += $value;
                $count++;
            }

            $average = $count > 0 ? $sum / $count : 0;

            return [
                [
                    'label' => 'Average',
                    'value' => round($average, 2)
                ]
            ];
        }

        // Group by and average
        return $this->groupAndAggregate($groupByFields, $targetField, 'average');
    }

    /**
     * Calculate MIN
     */
    protected function calculateMin($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Simple min
            $min = null;
            
            foreach ($this->records as $record) {
                $value = floatval($record[$targetField] ?? 0);
                if ($min === null || $value < $min) {
                    $min = $value;
                }
            }

            return [
                [
                    'label' => 'Minimum',
                    'value' => $min ?? 0
                ]
            ];
        }

        // Group by and min
        return $this->groupAndAggregate($groupByFields, $targetField, 'min');
    }

    /**
     * Calculate MAX
     */
    protected function calculateMax($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];

        if (empty($groupByFields)) {
            // Simple max
            $max = null;
            
            foreach ($this->records as $record) {
                $value = floatval($record[$targetField] ?? 0);
                if ($max === null || $value > $max) {
                    $max = $value;
                }
            }

            return [
                [
                    'label' => 'Maximum',
                    'value' => $max ?? 0
                ]
            ];
        }

        // Group by and max
        return $this->groupAndAggregate($groupByFields, $targetField, 'max');
    }

    /**
     * Calculate PERCENTAGE
     */
    protected function calculatePercentage($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];

        $grouped = $this->groupRecords($groupByFields);
        $total = count($this->records);

        $result = [];
        foreach ($grouped as $key => $records) {
            $count = count($records);
            $percentage = $total > 0 ? ($count / $total) * 100 : 0;

            $result[] = [
                'label' => $key,
                'value' => round($percentage, 2),
                'count' => $count,
                'total' => $total
            ];
        }

        return $result;
    }

    /**
     * Calculate RATIO
     */
    protected function calculateRatio($config)
    {
        $numeratorField = $config['calculation_config']['numerator_field'] ?? '';
        $denominatorField = $config['calculation_config']['denominator_field'] ?? '';

        if (empty($numeratorField) || empty($denominatorField)) {
            throw new \Exception("Ratio memerlukan numerator_field dan denominator_field");
        }

        $numeratorSum = 0;
        $denominatorSum = 0;

        foreach ($this->records as $record) {
            $numeratorSum += floatval($record[$numeratorField] ?? 0);
            $denominatorSum += floatval($record[$denominatorField] ?? 0);
        }

        $ratio = $denominatorSum > 0 ? $numeratorSum / $denominatorSum : 0;

        return [
            [
                'label' => "Ratio ({$numeratorField} / {$denominatorField})",
                'value' => round($ratio, 4),
                'numerator' => $numeratorSum,
                'denominator' => $denominatorSum
            ]
        ];
    }

    /**
     * Calculate GROWTH
     */
    protected function calculateGrowth($config)
    {
        $targetField = $config['target_field'];
        $calculationConfig = $config['calculation_config'] ?? [];
        $periodField = $calculationConfig['period_field'] ?? '';

        if (empty($periodField)) {
            throw new \Exception("Growth memerlukan period_field dalam calculation_config");
        }

        // Group by period
        $grouped = [];
        foreach ($this->records as $record) {
            $period = $record[$periodField] ?? 'Unknown';
            $value = floatval($record[$targetField] ?? 0);

            if (!isset($grouped[$period])) {
                $grouped[$period] = 0;
            }
            $grouped[$period] += $value;
        }

        // Sort by period
        ksort($grouped);

        // Calculate growth
        $result = [];
        $previousValue = null;

        foreach ($grouped as $period => $value) {
            $growth = null;
            $growthPercentage = null;

            if ($previousValue !== null && $previousValue > 0) {
                $growth = $value - $previousValue;
                $growthPercentage = ($growth / $previousValue) * 100;
            }

            $result[] = [
                'label' => $period,
                'value' => $value,
                'growth' => $growth,
                'growth_percentage' => $growthPercentage !== null ? round($growthPercentage, 2) : null
            ];

            $previousValue = $value;
        }

        return $result;
    }

    /**
     * Calculate RANKING
     */
    protected function calculateRanking($config)
    {
        $targetField = $config['target_field'];
        $groupByFields = $config['group_by_fields'] ?? [];
        $order = $config['sort_order'] ?? 'DESC'; // DESC = highest first

        $grouped = $this->groupAndAggregate($groupByFields, $targetField, 'sum');

        // Sort by value
        usort($grouped, function($a, $b) use ($order) {
            if ($order === 'DESC') {
                return $b['value'] <=> $a['value'];
            } else {
                return $a['value'] <=> $b['value'];
            }
        });

        // Add rank
        $result = [];
        foreach ($grouped as $index => $item) {
            $result[] = [
                'rank' => $index + 1,
                'label' => $item['label'],
                'value' => $item['value']
            ];
        }

        return $result;
    }

    /**
     * Calculate CUSTOM FORMULA
     */
    protected function calculateCustomFormula($config)
    {
        $formula = $config['custom_formula'] ?? '';
        
        if (empty($formula)) {
            throw new \Exception("Custom formula tidak boleh kosong");
        }

        // Parse formula and calculate
        // For now, simple implementation
        // TODO: Implement proper formula parser

        return [
            [
                'label' => 'Custom Calculation',
                'value' => 0,
                'formula' => $formula,
                'note' => 'Custom formula calculation not yet implemented'
            ]
        ];
    }

    /**
     * Group records by fields
     */
    protected function groupRecords($groupByFields)
    {
        $grouped = [];

        foreach ($this->records as $record) {
            // Build group key
            $keyParts = [];
            foreach ($groupByFields as $field) {
                $keyParts[] = $record[$field] ?? 'Unknown';
            }
            $key = implode(' - ', $keyParts);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }

            $grouped[$key][] = $record;
        }

        return $grouped;
    }

    /**
     * Group and aggregate
     */
    protected function groupAndAggregate($groupByFields, $targetField, $aggregateType)
    {
        $grouped = $this->groupRecords($groupByFields);
        
        $result = [];
        foreach ($grouped as $key => $records) {
            $value = 0;

            switch ($aggregateType) {
                case 'sum':
                    foreach ($records as $record) {
                        $value += floatval($record[$targetField] ?? 0);
                    }
                    break;

                case 'average':
                    $sum = 0;
                    $count = count($records);
                    foreach ($records as $record) {
                        $sum += floatval($record[$targetField] ?? 0);
                    }
                    $value = $count > 0 ? $sum / $count : 0;
                    break;

                case 'min':
                    $value = null;
                    foreach ($records as $record) {
                        $recordValue = floatval($record[$targetField] ?? 0);
                        if ($value === null || $recordValue < $value) {
                            $value = $recordValue;
                        }
                    }
                    break;

                case 'max':
                    $value = null;
                    foreach ($records as $record) {
                        $recordValue = floatval($record[$targetField] ?? 0);
                        if ($value === null || $recordValue > $value) {
                            $value = $recordValue;
                        }
                    }
                    break;

                case 'count':
                    $value = count($records);
                    break;
            }

            $result[] = [
                'label' => $key,
                'value' => is_float($value) ? round($value, 2) : $value
            ];
        }

        return $result;
    }

    /**
     * Apply sorting
     */
    protected function applySorting($result, $config)
    {
        $sortBy = $config['sort_by'] ?? 'value';
        $sortOrder = strtoupper($config['sort_order'] ?? 'DESC');

        usort($result, function($a, $b) use ($sortBy, $sortOrder) {
            $aVal = $a[$sortBy] ?? 0;
            $bVal = $b[$sortBy] ?? 0;

            if ($sortOrder === 'DESC') {
                return $bVal <=> $aVal;
            } else {
                return $aVal <=> $bVal;
            }
        });

        return $result;
    }

    /**
     * Apply limit
     */
    protected function applyLimit($result, $limit)
    {
        return array_slice($result, 0, $limit);
    }

    /**
     * Format result untuk visualization
     */
    public function formatForVisualization($result, $visualizationType)
    {
        switch ($visualizationType) {
            case 'bar_chart':
            case 'line_chart':
            case 'area_chart':
                return $this->formatForChart($result);
            
            case 'pie_chart':
            case 'donut_chart':
                return $this->formatForPieChart($result);
            
            case 'kpi_card':
                return $this->formatForKPI($result);
            
            case 'table':
                return $this->formatForTable($result);
            
            default:
                return $result;
        }
    }

    /**
     * Format untuk chart (labels & values)
     */
    protected function formatForChart($result)
    {
        return [
            'labels' => array_column($result, 'label'),
            'values' => array_column($result, 'value')
        ];
    }

    /**
     * Format untuk pie chart
     */
    protected function formatForPieChart($result)
    {
        $formatted = [];
        foreach ($result as $item) {
            $formatted[] = [
                'name' => $item['label'],
                'value' => $item['value']
            ];
        }
        return $formatted;
    }

    /**
     * Format untuk KPI card
     */
    protected function formatForKPI($result)
    {
        if (empty($result)) {
            return ['value' => 0];
        }

        // Ambil value pertama
        return [
            'value' => $result[0]['value'] ?? 0,
            'label' => $result[0]['label'] ?? '',
            'metadata' => $result[0]
        ];
    }

    /**
     * Format untuk table
     */
    protected function formatForTable($result)
    {
        return [
            'columns' => !empty($result) ? array_keys($result[0]) : [],
            'rows' => $result
        ];
    }
}