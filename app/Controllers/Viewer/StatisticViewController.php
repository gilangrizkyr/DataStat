<?php

/**
 * ============================================================================
 * VIEWER STATISTIC CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Viewer/StatisticViewController.php
 * 
 * Deskripsi:
 * Controller untuk viewer melihat statistik (read-only).
 * Viewer dapat view list statistik, detail, dan hasil perhitungan tapi tidak bisa edit.
 * 
 * Fitur:
 * - List semua statistik di workspace
 * - View detail statistik dengan hasil perhitungan
 * - Filter statistik by dataset, type, dll
 * - Export hasil statistik (CSV, Excel)
 * - View visualization
 * 
 * Role: Viewer (Read-Only)
 * ============================================================================
 */

namespace App\Controllers\Viewer;

use App\Controllers\BaseController;
use App\Models\Owner\StatisticConfigModel;
use App\Models\Owner\DatasetModel;
use App\Libraries\ComputationEngine;
use App\Libraries\VisualizationRenderer;

class StatisticViewController extends BaseController
{
    protected $statisticModel;
    protected $datasetModel;
    protected $computationEngine;
    protected $visualizationRenderer;

    public function __construct()
    {
        $this->statisticModel = new StatisticConfigModel();
        $this->datasetModel = new DatasetModel();
        $this->computationEngine = new ComputationEngine();
        $this->visualizationRenderer = new VisualizationRenderer();
        helper(['form', 'url']);
    }

    /**
     * List semua statistik
     */
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return redirect()->to('/login')
                ->with('error', 'Anda belum memiliki akses ke workspace');
        }

        // Filter
        $datasetId = $this->request->getGet('dataset_id');
        $metricType = $this->request->getGet('metric_type');
        $search = $this->request->getGet('search');

        $builder = $this->statisticModel
            ->select('statistic_configs.*, datasets.dataset_name, users.nama_lengkap as creator_name')
            ->join('datasets', 'datasets.id = statistic_configs.dataset_id')
            ->join('users', 'users.id = statistic_configs.created_by')
            ->where('statistic_configs.application_id', $applicationId)
            ->where('statistic_configs.is_active', 1)
            ->where('statistic_configs.deleted_at', null);

        if ($datasetId) {
            $builder->where('statistic_configs.dataset_id', $datasetId);
        }

        if ($metricType) {
            $builder->where('statistic_configs.metric_type', $metricType);
        }

        if ($search) {
            $builder->like('statistic_configs.stat_name', $search);
        }

        $statistics = $builder->orderBy('statistic_configs.created_at', 'DESC')->findAll();

        // Get datasets untuk filter
        $datasets = $this->datasetModel
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->findAll();

        $data = [
            'title' => 'Daftar Statistik',
            'statistics' => $statistics,
            'datasets' => $datasets,
            'filters' => [
                'dataset_id' => $datasetId,
                'metric_type' => $metricType,
                'search' => $search
            ]
        ];

        return view('viewer/statistics/index', $data);
    }

    /**
     * View detail statistik dengan hasil perhitungan
     */
    public function view($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        // Get statistik
        $statistic = $this->statisticModel
            ->select('statistic_configs.*, datasets.dataset_name, users.nama_lengkap as creator_name')
            ->join('datasets', 'datasets.id = statistic_configs.dataset_id')
            ->join('users', 'users.id = statistic_configs.created_by')
            ->where('statistic_configs.id', $id)
            ->where('statistic_configs.application_id', $applicationId)
            ->where('statistic_configs.deleted_at', null)
            ->first();

        if (!$statistic) {
            return redirect()->to('/viewer/statistics')
                ->with('error', 'Statistik tidak ditemukan atau Anda tidak memiliki akses');
        }

        // Prepare config for computation engine
        $config = $statistic;
        // Decode JSON fields
        if (!empty($config['group_by_fields']) && is_string($config['group_by_fields'])) {
            $config['group_by_fields'] = json_decode($config['group_by_fields'], true);
        }
        if (!empty($config['filters']) && is_string($config['filters'])) {
            $config['filters'] = json_decode($config['filters'], true);
        }
        if (!empty($config['calculation_config']) && is_string($config['calculation_config'])) {
            $config['calculation_config'] = json_decode($config['calculation_config'], true);
        }
        if (!empty($config['visualization_config']) && is_string($config['visualization_config'])) {
            $config['visualization_config'] = json_decode($config['visualization_config'], true);
        }

        // Calculate statistik
        $result = $this->computationEngine->calculate($config);

        // Render visualization
        $visualization = $this->visualizationRenderer->render(
            $statistic['visualization_type'],
            $result,
            json_decode($statistic['visualization_config'], true)
        );

        // Log aktivitas
        $this->logActivity('view', 'statistics', 'Viewer melihat statistik: ' . $statistic['stat_name'], [
            'statistic_id' => $id
        ]);

        $data = [
            'title' => 'Detail Statistik: ' . $statistic['stat_name'],
            'statistic' => $statistic,
            'result' => $result,
            'visualization' => $visualization
        ];

        return view('viewer/statistics/view', $data);
    }

    /**
     * View by slug
     */
    public function viewBySlug($slug)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $statistic = $this->statisticModel
            ->where('stat_slug', $slug)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            return redirect()->to('/viewer/statistics')
                ->with('error', 'Statistik tidak ditemukan');
        }

        return $this->view($statistic['id']);
    }

    /**
     * Export hasil statistik ke CSV
     */
    public function exportCsv($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $statistic = $this->statisticModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            return redirect()->to('/viewer/statistics')
                ->with('error', 'Statistik tidak ditemukan');
        }

        // Prepare config for computation engine
        $config = $statistic;
        // Decode JSON fields
        if (!empty($config['group_by_fields']) && is_string($config['group_by_fields'])) {
            $config['group_by_fields'] = json_decode($config['group_by_fields'], true);
        }
        if (!empty($config['filters']) && is_string($config['filters'])) {
            $config['filters'] = json_decode($config['filters'], true);
        }
        if (!empty($config['calculation_config']) && is_string($config['calculation_config'])) {
            $config['calculation_config'] = json_decode($config['calculation_config'], true);
        }
        if (!empty($config['visualization_config']) && is_string($config['visualization_config'])) {
            $config['visualization_config'] = json_decode($config['visualization_config'], true);
        }

        // Calculate
        $result = $this->computationEngine->calculate($config);

        // Generate CSV
        $filename = 'statistik-' . $statistic['stat_slug'] . '-' . date('YmdHis') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header
        if (isset($result['data']) && is_array($result['data']) && count($result['data']) > 0) {
            fputcsv($output, array_keys($result['data'][0]));

            // Data
            foreach ($result['data'] as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);

        $this->logActivity('export', 'statistics', 'Viewer export statistik ke CSV: ' . $statistic['stat_name'], [
            'statistic_id' => $id
        ]);

        exit;
    }

    /**
     * Export hasil statistik ke Excel
     */
    public function exportExcel($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $statistic = $this->statisticModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            return redirect()->to('/viewer/statistics')
                ->with('error', 'Statistik tidak ditemukan');
        }

        // TODO: Implement Excel export menggunakan PhpSpreadsheet

        $this->logActivity('export', 'statistics', 'Viewer export statistik ke Excel: ' . $statistic['stat_name'], [
            'statistic_id' => $id
        ]);

        return redirect()->back()->with('info', 'Fitur export Excel akan segera tersedia');
    }

    /**
     * Compare multiple statistics
     */
    public function compare()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        // Get selected statistic IDs from query string
        $statisticIds = $this->request->getGet('ids');

        if (!$statisticIds) {
            return redirect()->to('/viewer/statistics')
                ->with('info', 'Pilih minimal 2 statistik untuk membandingkan');
        }

        $ids = explode(',', $statisticIds);

        if (count($ids) < 2) {
            return redirect()->to('/viewer/statistics')
                ->with('info', 'Pilih minimal 2 statistik untuk membandingkan');
        }

        // Get statistics
        $statistics = $this->statisticModel
            ->select('statistic_configs.*, datasets.dataset_name')
            ->join('datasets', 'datasets.id = statistic_configs.dataset_id')
            ->whereIn('statistic_configs.id', $ids)
            ->where('statistic_configs.application_id', $applicationId)
            ->where('statistic_configs.deleted_at', null)
            ->findAll();

        if (count($statistics) < 2) {
            return redirect()->to('/viewer/statistics')
                ->with('error', 'Statistik yang dipilih tidak valid');
        }

        // Calculate all
        $results = [];
        foreach ($statistics as $stat) {
            // Prepare config for computation engine
            $config = $stat;
            // Decode JSON fields
            if (!empty($config['group_by_fields']) && is_string($config['group_by_fields'])) {
                $config['group_by_fields'] = json_decode($config['group_by_fields'], true);
            }
            if (!empty($config['filters']) && is_string($config['filters'])) {
                $config['filters'] = json_decode($config['filters'], true);
            }
            if (!empty($config['calculation_config']) && is_string($config['calculation_config'])) {
                $config['calculation_config'] = json_decode($config['calculation_config'], true);
            }
            if (!empty($config['visualization_config']) && is_string($config['visualization_config'])) {
                $config['visualization_config'] = json_decode($config['visualization_config'], true);
            }

            $result = $this->computationEngine->calculate($config);
            $results[] = [
                'statistic' => $stat,
                'result' => $result
            ];
        }

        $this->logActivity('compare', 'statistics', 'Viewer membandingkan statistik', [
            'statistic_ids' => $ids
        ]);

        $data = [
            'title' => 'Perbandingan Statistik',
            'results' => $results
        ];

        return view('viewer/statistics/compare', $data);
    }

    /**
     * Get statistic data via AJAX (untuk refresh/update)
     */
    public function getData($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $applicationId = session()->get('application_id');

            $statistic = $this->statisticModel
                ->where('id', $id)
                ->where('application_id', $applicationId)
                ->where('deleted_at', null)
                ->first();

            if (!$statistic) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Statistik tidak ditemukan'
                ]);
            }

            // Prepare config for computation engine
            $config = $statistic;
            // Decode JSON fields
            if (!empty($config['group_by_fields']) && is_string($config['group_by_fields'])) {
                $config['group_by_fields'] = json_decode($config['group_by_fields'], true);
            }
            if (!empty($config['filters']) && is_string($config['filters'])) {
                $config['filters'] = json_decode($config['filters'], true);
            }
            if (!empty($config['calculation_config']) && is_string($config['calculation_config'])) {
                $config['calculation_config'] = json_decode($config['calculation_config'], true);
            }
            if (!empty($config['visualization_config']) && is_string($config['visualization_config'])) {
                $config['visualization_config'] = json_decode($config['visualization_config'], true);
            }

            // Force recalculate
            $forceRecalculate = $this->request->getGet('force') == '1';
            $result = $this->computationEngine->calculate($config, $forceRecalculate);

            // Render visualization
            $visualization = $this->visualizationRenderer->render(
                $statistic['visualization_type'],
                $result,
                json_decode($statistic['visualization_config'], true)
            );

            return $this->response->setJSON([
                'success' => true,
                'data' => $result,
                'visualization' => $visualization,
                'last_updated' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Log aktivitas
     */
    private function logActivity($activityType, $module, $description, $data = [])
    {
        $logData = [
            'user_id' => session()->get('user_id'),
            'application_id' => session()->get('application_id'),
            'activity_type' => $activityType,
            'module' => $module,
            'description' => $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'request_data' => json_encode($data)
        ];

        $db = \Config\Database::connect();
        $db->table('log_activities')->insert($logData);
    }
}
