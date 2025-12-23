<?php

/**
 * ============================================================================
 * VISUALIZATION RENDERER LIBRARY
 * ============================================================================
 * 
 * Path: app/Libraries/VisualizationRenderer.php
 * 
 * Deskripsi:
 * Library untuk render visualisasi dari hasil statistik.
 * Generate HTML/JSON untuk berbagai tipe chart.
 * 
 * Supported Visualizations:
 * - table: Data table
 * - bar_chart: Bar chart (vertical)
 * - pie_chart: Pie chart
 * - line_chart: Line chart
 * - area_chart: Area chart
 * - kpi_card: KPI card (single value)
 * - progress_bar: Progress bar
 * - donut_chart: Donut chart
 * - scatter_chart: Scatter plot
 * 
 * Chart Library: Chart.js (via CDN)
 * 
 * Used by: Owner/StatisticController, Viewer/DashboardController
 * ============================================================================
 */

namespace App\Libraries;

class VisualizationRenderer
{
    /**
     * Render visualization
     */
    public function render($data, $visualizationType, $config = [])
    {
        switch ($visualizationType) {
            case 'table':
                return $this->renderTable($data, $config);
            
            case 'bar_chart':
                return $this->renderBarChart($data, $config);
            
            case 'pie_chart':
                return $this->renderPieChart($data, $config);
            
            case 'line_chart':
                return $this->renderLineChart($data, $config);
            
            case 'area_chart':
                return $this->renderAreaChart($data, $config);
            
            case 'kpi_card':
                return $this->renderKPICard($data, $config);
            
            case 'progress_bar':
                return $this->renderProgressBar($data, $config);
            
            case 'donut_chart':
                return $this->renderDonutChart($data, $config);
            
            case 'scatter_chart':
                return $this->renderScatterChart($data, $config);
            
            default:
                return $this->renderTable($data, $config);
        }
    }

    /**
     * Render TABLE
     */
    protected function renderTable($data, $config)
    {
        if (empty($data)) {
            return '<p class="text-muted">Tidak ada data</p>';
        }

        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-striped table-hover">';
        
        // Headers
        $html .= '<thead><tr>';
        $columns = array_keys($data[0]);
        foreach ($columns as $col) {
            $html .= '<th>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $col))) . '</th>';
        }
        $html .= '</tr></thead>';

        // Body
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render BAR CHART
     */
    protected function renderBarChart($data, $config)
    {
        $chartId = 'chart-' . uniqid();
        $labels = array_column($data, 'label');
        $values = array_column($data, 'value');

        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $config['label'] ?? 'Data',
                        'data' => $values,
                        'backgroundColor' => $config['color'] ?? 'rgba(54, 162, 235, 0.6)',
                        'borderColor' => $config['borderColor'] ?? 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];

        $html = '<div style="height: ' . ($config['height'] ?? 400) . 'px;">';
        $html .= '<canvas id="' . $chartId . '"></canvas>';
        $html .= '</div>';
        $html .= '<script>';
        $html .= 'new Chart(document.getElementById("' . $chartId . '"), ' . json_encode($chartConfig) . ');';
        $html .= '</script>';

        return $html;
    }

    /**
     * Render PIE CHART
     */
    protected function renderPieChart($data, $config)
    {
        $chartId = 'chart-' . uniqid();
        $labels = array_column($data, 'label');
        $values = array_column($data, 'value');

        // Generate colors
        $colors = $this->generateColors(count($data));

        $chartConfig = [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $values,
                        'backgroundColor' => $colors
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom'
                    ]
                ]
            ]
        ];

        $html = '<div style="height: ' . ($config['height'] ?? 400) . 'px;">';
        $html .= '<canvas id="' . $chartId . '"></canvas>';
        $html .= '</div>';
        $html .= '<script>';
        $html .= 'new Chart(document.getElementById("' . $chartId . '"), ' . json_encode($chartConfig) . ');';
        $html .= '</script>';

        return $html;
    }

    /**
     * Render LINE CHART
     */
    protected function renderLineChart($data, $config)
    {
        $chartId = 'chart-' . uniqid();
        $labels = array_column($data, 'label');
        $values = array_column($data, 'value');

        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $config['label'] ?? 'Data',
                        'data' => $values,
                        'borderColor' => $config['color'] ?? 'rgba(75, 192, 192, 1)',
                        'backgroundColor' => $config['backgroundColor'] ?? 'rgba(75, 192, 192, 0.2)',
                        'tension' => 0.4,
                        'fill' => false
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];

        $html = '<div style="height: ' . ($config['height'] ?? 400) . 'px;">';
        $html .= '<canvas id="' . $chartId . '"></canvas>';
        $html .= '</div>';
        $html .= '<script>';
        $html .= 'new Chart(document.getElementById("' . $chartId . '"), ' . json_encode($chartConfig) . ');';
        $html .= '</script>';

        return $html;
    }

    /**
     * Render AREA CHART
     */
    protected function renderAreaChart($data, $config)
    {
        $chartId = 'chart-' . uniqid();
        $labels = array_column($data, 'label');
        $values = array_column($data, 'value');

        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $config['label'] ?? 'Data',
                        'data' => $values,
                        'borderColor' => $config['color'] ?? 'rgba(153, 102, 255, 1)',
                        'backgroundColor' => $config['backgroundColor'] ?? 'rgba(153, 102, 255, 0.4)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];

        $html = '<div style="height: ' . ($config['height'] ?? 400) . 'px;">';
        $html .= '<canvas id="' . $chartId . '"></canvas>';
        $html .= '</div>';
        $html .= '<script>';
        $html .= 'new Chart(document.getElementById("' . $chartId . '"), ' . json_encode($chartConfig) . ');';
        $html .= '</script>';

        return $html;
    }

    /**
     * Render KPI CARD
     */
    protected function renderKPICard($data, $config)
    {
        if (empty($data)) {
            return '<p class="text-muted">Tidak ada data</p>';
        }

        $value = $data[0]['value'] ?? 0;
        $label = $config['label'] ?? 'KPI';

        // Format number
        $formattedValue = $this->formatNumber($value, $config);

        $html = '<div class="card text-center">';
        $html .= '<div class="card-body">';
        $html .= '<h5 class="card-title text-muted mb-3">' . htmlspecialchars($label) . '</h5>';
        $html .= '<h2 class="display-3 mb-0">' . $formattedValue . '</h2>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render PROGRESS BAR
     */
    protected function renderProgressBar($data, $config)
    {
        if (empty($data)) {
            return '<p class="text-muted">Tidak ada data</p>';
        }

        $value = $data[0]['value'] ?? 0;
        $max = $config['max_value'] ?? 100;
        $percentage = ($value / $max) * 100;
        $percentage = min(100, max(0, $percentage)); // Clamp 0-100

        $label = $config['label'] ?? 'Progress';

        $html = '<div class="mb-3">';
        $html .= '<div class="d-flex justify-content-between mb-1">';
        $html .= '<span>' . htmlspecialchars($label) . '</span>';
        $html .= '<span>' . number_format($value, 0) . ' / ' . number_format($max, 0) . '</span>';
        $html .= '</div>';
        $html .= '<div class="progress" style="height: 25px;">';
        $html .= '<div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%">';
        $html .= number_format($percentage, 1) . '%';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render DONUT CHART
     */
    protected function renderDonutChart($data, $config)
    {
        $chartId = 'chart-' . uniqid();
        $labels = array_column($data, 'label');
        $values = array_column($data, 'value');

        $colors = $this->generateColors(count($data));

        $chartConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $values,
                        'backgroundColor' => $colors
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom'
                    ]
                ]
            ]
        ];

        $html = '<div style="height: ' . ($config['height'] ?? 400) . 'px;">';
        $html .= '<canvas id="' . $chartId . '"></canvas>';
        $html .= '</div>';
        $html .= '<script>';
        $html .= 'new Chart(document.getElementById("' . $chartId . '"), ' . json_encode($chartConfig) . ');';
        $html .= '</script>';

        return $html;
    }

    /**
     * Render SCATTER CHART
     */
    protected function renderScatterChart($data, $config)
    {
        $chartId = 'chart-' . uniqid();

        // Data format: [{x: value, y: value}]
        $points = [];
        foreach ($data as $item) {
            $points[] = [
                'x' => $item['x'] ?? $item['value'] ?? 0,
                'y' => $item['y'] ?? $item['value'] ?? 0
            ];
        }

        $chartConfig = [
            'type' => 'scatter',
            'data' => [
                'datasets' => [
                    [
                        'label' => $config['label'] ?? 'Data',
                        'data' => $points,
                        'backgroundColor' => $config['color'] ?? 'rgba(255, 99, 132, 0.6)'
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'x' => [
                        'type' => 'linear',
                        'position' => 'bottom'
                    ]
                ]
            ]
        ];

        $html = '<div style="height: ' . ($config['height'] ?? 400) . 'px;">';
        $html .= '<canvas id="' . $chartId . '"></canvas>';
        $html .= '</div>';
        $html .= '<script>';
        $html .= 'new Chart(document.getElementById("' . $chartId . '"), ' . json_encode($chartConfig) . ');';
        $html .= '</script>';

        return $html;
    }

    /**
     * Generate colors for charts
     */
    protected function generateColors($count)
    {
        $colors = [
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 99, 132, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)',
            'rgba(199, 199, 199, 0.6)',
            'rgba(83, 102, 255, 0.6)',
            'rgba(255, 102, 196, 0.6)',
            'rgba(128, 255, 128, 0.6)'
        ];

        // Repeat if needed
        while (count($colors) < $count) {
            $colors = array_merge($colors, $colors);
        }

        return array_slice($colors, 0, $count);
    }

    /**
     * Format number
     */
    protected function formatNumber($value, $config = [])
    {
        $decimals = $config['decimals'] ?? 0;
        $prefix = $config['prefix'] ?? '';
        $suffix = $config['suffix'] ?? '';

        return $prefix . number_format($value, $decimals) . $suffix;
    }

    /**
     * Get chart JSON (for API/AJAX)
     */
    public function getChartJson($data, $visualizationType, $config = [])
    {
        switch ($visualizationType) {
            case 'bar_chart':
            case 'line_chart':
            case 'area_chart':
                return [
                    'labels' => array_column($data, 'label'),
                    'values' => array_column($data, 'value')
                ];
            
            case 'pie_chart':
            case 'donut_chart':
                return [
                    'labels' => array_column($data, 'label'),
                    'values' => array_column($data, 'value'),
                    'colors' => $this->generateColors(count($data))
                ];
            
            default:
                return $data;
        }
    }
}