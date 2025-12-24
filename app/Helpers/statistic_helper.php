<?php

/**
 * ============================================================================
 * STATISTIC HELPER
 * ============================================================================
 * 
 * Path: app/Helpers/statistic_helper.php
 * 
 * Helper functions untuk statistical calculations dan visualizations
 * ============================================================================
 */

if (!function_exists('calculate_sum')) {
    /**
     * Calculate sum of array
     */
    function calculate_sum(array $values): float
    {
        $numericValues = array_filter($values, 'is_numeric');
        return array_sum($numericValues);
    }
}

if (!function_exists('calculate_average')) {
    /**
     * Calculate average of array
     */
    function calculate_average(array $values): float
    {
        $numericValues = array_filter($values, 'is_numeric');
        $count = count($numericValues);
        
        if ($count === 0) {
            return 0;
        }
        
        return array_sum($numericValues) / $count;
    }
}

if (!function_exists('calculate_count')) {
    /**
     * Calculate count of values
     */
    function calculate_count(array $values): int
    {
        return count($values);
    }
}

if (!function_exists('calculate_count_distinct')) {
    /**
     * Calculate count of distinct values
     */
    function calculate_count_distinct(array $values): int
    {
        return count(array_unique($values));
    }
}

if (!function_exists('calculate_min')) {
    /**
     * Calculate minimum value
     */
    function calculate_min(array $values): float
    {
        $numericValues = array_filter($values, 'is_numeric');
        
        if (empty($numericValues)) {
            return 0;
        }
        
        return min($numericValues);
    }
}

if (!function_exists('calculate_max')) {
    /**
     * Calculate maximum value
     */
    function calculate_max(array $values): float
    {
        $numericValues = array_filter($values, 'is_numeric');
        
        if (empty($numericValues)) {
            return 0;
        }
        
        return max($numericValues);
    }
}

if (!function_exists('calculate_median')) {
    /**
     * Calculate median of array
     */
    function calculate_median(array $values): float
    {
        $numericValues = array_filter($values, 'is_numeric');
        $numericValues = array_values($numericValues);
        
        if (empty($numericValues)) {
            return 0;
        }
        
        sort($numericValues);
        $count = count($numericValues);
        $middle = floor($count / 2);
        
        if ($count % 2 == 0) {
            return ($numericValues[$middle - 1] + $numericValues[$middle]) / 2;
        }
        
        return $numericValues[$middle];
    }
}

if (!function_exists('calculate_mode')) {
    /**
     * Calculate mode (most frequent value)
     */
    function calculate_mode(array $values): mixed
    {
        if (empty($values)) {
            return null;
        }
        
        $frequency = array_count_values($values);
        arsort($frequency);
        
        return key($frequency);
    }
}

if (!function_exists('calculate_percentage')) {
    /**
     * Calculate percentage
     */
    function calculate_percentage(float $part, float $total): float
    {
        if ($total == 0) {
            return 0;
        }
        
        return ($part / $total) * 100;
    }
}

if (!function_exists('calculate_growth_rate')) {
    /**
     * Calculate growth rate between two values
     */
    function calculate_growth_rate(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return 0;
        }
        
        return (($newValue - $oldValue) / $oldValue) * 100;
    }
}

if (!function_exists('group_by_field')) {
    /**
     * Group data by field
     */
    function group_by_field(array $data, string $field): array
    {
        $grouped = [];
        
        foreach ($data as $row) {
            $key = $row[$field] ?? 'Unknown';
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            
            $grouped[$key][] = $row;
        }
        
        return $grouped;
    }
}

if (!function_exists('aggregate_data')) {
    /**
     * Aggregate data with calculation type
     */
    function aggregate_data(array $data, string $valueField, string $calculationType): array
    {
        $result = [];
        
        foreach ($data as $key => $items) {
            $values = array_column($items, $valueField);
            
            switch ($calculationType) {
                case 'sum':
                    $result[$key] = calculate_sum($values);
                    break;
                    
                case 'average':
                case 'avg':
                    $result[$key] = calculate_average($values);
                    break;
                    
                case 'count':
                    $result[$key] = calculate_count($values);
                    break;
                    
                case 'count_distinct':
                    $result[$key] = calculate_count_distinct($values);
                    break;
                    
                case 'min':
                    $result[$key] = calculate_min($values);
                    break;
                    
                case 'max':
                    $result[$key] = calculate_max($values);
                    break;
                    
                default:
                    $result[$key] = 0;
            }
        }
        
        return $result;
    }
}

if (!function_exists('format_number')) {
    /**
     * Format number with thousands separator
     */
    function format_number(float $number, int $decimals = 0): string
    {
        return number_format($number, $decimals, ',', '.');
    }
}

if (!function_exists('format_percentage')) {
    /**
     * Format percentage
     */
    function format_percentage(float $percentage, int $decimals = 2): string
    {
        return number_format($percentage, $decimals, ',', '.') . '%';
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency (Indonesian Rupiah)
     */
    function format_currency(float $amount, bool $showPrefix = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');
        return $showPrefix ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('get_chart_colors')) {
    /**
     * Get predefined chart colors
     */
    function get_chart_colors(int $count = 10): array
    {
        $colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#858796', '#5a5c69', '#6f42c1', '#e83e8c', '#fd7e14'
        ];
        
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % count($colors)];
        }
        
        return $result;
    }
}

if (!function_exists('prepare_chart_data')) {
    /**
     * Prepare data for chart visualization
     */
    function prepare_chart_data(array $labels, array $values): array
    {
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $values,
                    'backgroundColor' => get_chart_colors(count($values)),
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2
                ]
            ]
        ];
    }
}

if (!function_exists('get_statistic_icon')) {
    /**
     * Get icon for statistic type
     */
    function get_statistic_icon(string $type): string
    {
        $icons = [
            'sum' => 'fa-plus',
            'average' => 'fa-chart-line',
            'count' => 'fa-hashtag',
            'min' => 'fa-arrow-down',
            'max' => 'fa-arrow-up',
            'percentage' => 'fa-percent',
            'growth' => 'fa-chart-line',
            'default' => 'fa-chart-bar'
        ];
        
        return $icons[$type] ?? $icons['default'];
    }
}

if (!function_exists('get_visualization_types')) {
    /**
     * Get available visualization types
     */
    function get_visualization_types(): array
    {
        return [
            'bar' => [
                'name' => 'Bar Chart',
                'icon' => 'fa-chart-bar',
                'description' => 'Compare values across categories'
            ],
            'line' => [
                'name' => 'Line Chart',
                'icon' => 'fa-chart-line',
                'description' => 'Show trends over time'
            ],
            'pie' => [
                'name' => 'Pie Chart',
                'icon' => 'fa-chart-pie',
                'description' => 'Show proportions of a whole'
            ],
            'doughnut' => [
                'name' => 'Doughnut Chart',
                'icon' => 'fa-circle-notch',
                'description' => 'Like pie chart with center hole'
            ],
            'table' => [
                'name' => 'Table',
                'icon' => 'fa-table',
                'description' => 'Display data in rows and columns'
            ],
            'card' => [
                'name' => 'Info Card',
                'icon' => 'fa-square',
                'description' => 'Show single metric value'
            ]
        ];
    }
}

if (!function_exists('get_calculation_types')) {
    /**
     * Get available calculation types
     */
    function get_calculation_types(): array
    {
        return [
            'sum' => 'Sum (Total)',
            'average' => 'Average (Mean)',
            'count' => 'Count',
            'count_distinct' => 'Count Distinct',
            'min' => 'Minimum',
            'max' => 'Maximum',
            'median' => 'Median',
            'percentage' => 'Percentage'
        ];
    }
}

if (!function_exists('format_statistic_result')) {
    /**
     * Format statistic result based on type
     */
    function format_statistic_result(mixed $value, string $type, string $format = 'number'): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }
        
        switch ($format) {
            case 'currency':
                return format_currency($value);
                
            case 'percentage':
                return format_percentage($value);
                
            case 'number':
            default:
                $decimals = in_array($type, ['average', 'median']) ? 2 : 0;
                return format_number($value, $decimals);
        }
    }
}

if (!function_exists('calculate_trend')) {
    /**
     * Calculate trend direction
     */
    function calculate_trend(array $values): string
    {
        if (count($values) < 2) {
            return 'stable';
        }
        
        $first = reset($values);
        $last = end($values);
        
        if ($last > $first) {
            return 'up';
        } elseif ($last < $first) {
            return 'down';
        }
        
        return 'stable';
    }
}

if (!function_exists('get_trend_icon')) {
    /**
     * Get icon for trend
     */
    function get_trend_icon(string $trend): string
    {
        $icons = [
            'up' => '<i class="fas fa-arrow-up text-success"></i>',
            'down' => '<i class="fas fa-arrow-down text-danger"></i>',
            'stable' => '<i class="fas fa-minus text-secondary"></i>'
        ];
        
        return $icons[$trend] ?? $icons['stable'];
    }
}