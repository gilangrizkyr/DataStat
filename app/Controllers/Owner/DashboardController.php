<?php

/**
 * ============================================================================
 * OWNER DASHBOARD CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Owner/DashboardController.php
 * 
 * Deskripsi:
 * Controller untuk menampilkan dashboard owner workspace.
 * Owner dapat melihat ringkasan statistik, dataset, dan aktivitas terbaru.
 * 
 * Fitur:
 * - Tampilkan overview aplikasi/workspace
 * - Statistik jumlah dataset, statistik, dashboard
 * - Grafik aktivitas terbaru
 * - Quick access ke fitur utama
 * 
 * Role: Owner
 * ============================================================================
 */

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\Owner\ApplicationModel;
use App\Models\Owner\DatasetModel;
use App\Models\Owner\StatisticConfigModel;
use App\Models\Owner\DashboardModel;
use App\Models\Superadmin\LogActivityModel;

class DashboardController extends BaseController
{
    protected $applicationModel;
    protected $datasetModel;
    protected $statisticModel;
    protected $dashboardModel;
    protected $logModel;

    public function __construct()
    {
        $this->applicationModel = new ApplicationModel();
        $this->datasetModel = new DatasetModel();
        $this->statisticModel = new StatisticConfigModel();
        $this->dashboardModel = new DashboardModel();
        $this->logModel = new LogActivityModel();
        
        helper(['form', 'url', 'text']);
    }

    /**
     * Tampilkan dashboard owner
     */
    public function index()
    {
        // Cek apakah user sudah login dan role owner
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $userId = session()->get('user_id');
        $applicationId = session()->get('application_id');

        // Jika belum punya aplikasi, redirect ke halaman buat aplikasi
        if (!$applicationId) {
            return redirect()->to('/owner/application/create')
                ->with('info', 'Silakan buat aplikasi/workspace terlebih dahulu');
        }

        // Ambil data aplikasi
        $application = $this->applicationModel->find($applicationId);

        if (!$application) {
            return redirect()->to('/owner/application/create')
                ->with('error', 'Aplikasi tidak ditemukan');
        }

        // Hitung statistik overview
        $totalDatasets = $this->datasetModel
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->countAllResults();

        $totalStatistics = $this->statisticModel
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->countAllResults();

        $totalDashboards = $this->dashboardModel
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->countAllResults();

        // Total rows dari semua dataset
        $totalDataRows = $this->datasetModel
            ->select('SUM(total_rows) as total')
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->where('upload_status', 'completed')
            ->first();

        // Dataset terbaru
        $recentDatasets = $this->datasetModel
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->find();

        // Statistik terbaru
        $recentStatistics = $this->statisticModel
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->find();

        // Aktivitas terbaru
        $recentActivities = $this->logModel
            ->where('user_id', $userId)
            ->where('application_id', $applicationId)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->find();

        // Chart data - Upload trend (7 hari terakhir)
        $uploadTrend = $this->getUploadTrend($applicationId, 7);

        // Chart data - Statistik per tipe
        $statisticsByType = $this->getStatisticsByType($applicationId);

        $data = [
            'title' => 'Dashboard Owner',
            'application' => $application,
            'overview' => [
                'total_datasets' => $totalDatasets,
                'total_statistics' => $totalStatistics,
                'total_dashboards' => $totalDashboards,
                'total_data_rows' => $totalDataRows['total'] ?? 0
            ],
            'recent_datasets' => $recentDatasets,
            'recent_statistics' => $recentStatistics,
            'recent_activities' => $recentActivities,
            'upload_trend' => $uploadTrend,
            'statistics_by_type' => $statisticsByType
        ];

        return view('owner/dashboard/index', $data);
    }

    /**
     * Get upload trend data untuk chart
     */
    private function getUploadTrend($applicationId, $days = 7)
    {
        $dates = [];
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = date('d M', strtotime($date));

            $count = $this->datasetModel
                ->where('application_id', $applicationId)
                ->where('DATE(created_at)', $date)
                ->countAllResults();

            $counts[] = $count;
        }

        return [
            'labels' => $dates,
            'data' => $counts
        ];
    }

    /**
     * Get statistik by type untuk pie chart
     */
    private function getStatisticsByType($applicationId)
    {
        $types = ['count', 'sum', 'average', 'min', 'max', 'percentage', 'ratio', 'growth', 'ranking', 'custom_formula'];
        $labels = [];
        $data = [];
        $colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
        ];

        foreach ($types as $index => $type) {
            $count = $this->statisticModel
                ->where('application_id', $applicationId)
                ->where('metric_type', $type)
                ->where('deleted_at', null)
                ->countAllResults();

            if ($count > 0) {
                $labels[] = ucfirst(str_replace('_', ' ', $type));
                $data[] = $count;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels))
        ];
    }

    /**
     * Get quick stats untuk API/AJAX
     */
    public function getQuickStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return $this->response->setJSON(['error' => 'No application found']);
        }

        $stats = [
            'datasets' => $this->datasetModel
                ->where('application_id', $applicationId)
                ->where('deleted_at', null)
                ->countAllResults(),
            
            'statistics' => $this->statisticModel
                ->where('application_id', $applicationId)
                ->where('deleted_at', null)
                ->countAllResults(),
            
            'dashboards' => $this->dashboardModel
                ->where('application_id', $applicationId)
                ->where('deleted_at', null)
                ->countAllResults(),
            
            'active_statistics' => $this->statisticModel
                ->where('application_id', $applicationId)
                ->where('is_active', 1)
                ->where('deleted_at', null)
                ->countAllResults()
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats
        ]);
    }
}