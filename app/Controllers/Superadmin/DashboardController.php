<?php

/**
 * ============================================================================
 * SUPERADMIN DASHBOARD CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Superadmin/DashboardController.php
 * 
 * Deskripsi:
 * Controller untuk dashboard superadmin dengan overview seluruh sistem.
 * Superadmin dapat melihat statistik global semua aplikasi, user, dan aktivitas.
 * 
 * Fitur:
 * - Overview sistem (total users, applications, datasets, statistics)
 * - Chart pertumbuhan users & aplikasi
 * - Aplikasi terpopuler
 * - Aktivitas terbaru semua user
 * - System health & performance metrics
 * 
 * Role: Superadmin
 * ============================================================================
 */

namespace App\Controllers\Superadmin;

use App\Controllers\BaseController;
use App\Models\Superadmin\UserModel;
use App\Models\Owner\ApplicationModel;          // ✅ FIXED: Pakai dari Owner
use App\Models\Owner\DatasetModel;
use App\Models\Owner\StatisticConfigModel;
use App\Models\Superadmin\LogActivityModel;

class DashboardController extends BaseController
{
    protected $userModel;
    protected $applicationModel;
    protected $datasetModel;
    protected $statisticModel;
    protected $logModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->applicationModel = new ApplicationModel();
        $this->datasetModel = new DatasetModel();
        $this->statisticModel = new StatisticConfigModel();
        $this->logModel = new LogActivityModel();
        
        helper(['form', 'url']);
    }

    /**
     * Tampilkan dashboard superadmin
     */
    public function index()
    {
        // Cek apakah user sudah login dan role superadmin
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai superadmin');
        }

        // Overview statistik global
        $totalUsers = $this->userModel
            ->where('deleted_at', null)
            ->countAllResults();

        $totalApplications = $this->applicationModel
            ->where('deleted_at', null)
            ->countAllResults();

        $totalDatasets = $this->datasetModel
            ->where('deleted_at', null)
            ->countAllResults();

        $totalStatistics = $this->statisticModel
            ->where('deleted_at', null)
            ->countAllResults();

        // Users baru minggu ini
        $newUsersThisWeek = $this->userModel
            ->where('DATE(created_at) >=', date('Y-m-d', strtotime('-7 days')))
            ->where('deleted_at', null)
            ->countAllResults();

        // Aplikasi aktif
        $activeApplications = $this->applicationModel
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->countAllResults();

        // Aplikasi terbaru
        $recentApplications = $this->applicationModel
            ->select('applications.*, users.nama_lengkap as owner_name')
            ->join('users', 'users.id = applications.user_id')
            ->where('applications.deleted_at', null)
            ->orderBy('applications.created_at', 'DESC')
            ->limit(10)
            ->find();

        // Users terbaru
        $recentUsers = $this->userModel
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->find();

        // Aktivitas terbaru (global)
        $recentActivities = $this->logModel
            ->select('log_activities.*, users.nama_lengkap, applications.app_name')
            ->join('users', 'users.id = log_activities.user_id', 'left')
            ->join('applications', 'applications.id = log_activities.application_id', 'left')
            ->orderBy('log_activities.created_at', 'DESC')
            ->limit(20)
            ->find();

        // Chart data - User growth (30 hari terakhir)
        $userGrowth = $this->getUserGrowthData(30);

        // Chart data - Application growth
        $appGrowth = $this->getApplicationGrowthData(30);

        // Chart data - Aktivitas by module
        $activityByModule = $this->getActivityByModule();

        // Top applications by dataset count
        $topApplications = $this->getTopApplicationsByDatasets(10);

        $data = [
            'title' => 'Dashboard Superadmin',
            'overview' => [
                'total_users' => $totalUsers,
                'total_applications' => $totalApplications,
                'total_datasets' => $totalDatasets,
                'total_statistics' => $totalStatistics,
                'new_users_week' => $newUsersThisWeek,
                'active_applications' => $activeApplications
            ],
            'recent_applications' => $recentApplications,
            'recent_users' => $recentUsers,
            'recent_activities' => $recentActivities,
            'user_growth' => $userGrowth,
            'app_growth' => $appGrowth,
            'activity_by_module' => $activityByModule,
            'top_applications' => $topApplications
        ];

        return view('superadmin/dashboard/index', $data);
    }

    /**
     * Get user growth data untuk chart
     */
    private function getUserGrowthData($days = 30)
    {
        $dates = [];
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = date('d M', strtotime($date));

            $count = $this->userModel
                ->where('DATE(created_at)', $date)
                ->where('deleted_at', null)
                ->countAllResults();

            $counts[] = $count;
        }

        return [
            'labels' => $dates,
            'data' => $counts
        ];
    }

    /**
     * Get application growth data
     */
    private function getApplicationGrowthData($days = 30)
    {
        $dates = [];
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = date('d M', strtotime($date));

            $count = $this->applicationModel
                ->where('DATE(created_at)', $date)
                ->where('deleted_at', null)
                ->countAllResults();

            $counts[] = $count;
        }

        return [
            'labels' => $dates,
            'data' => $counts
        ];
    }

    /**
     * Get activity by module untuk pie chart
     */
    private function getActivityByModule()
    {
        $modules = ['users', 'applications', 'datasets', 'statistics', 'dashboards'];
        $labels = [];
        $data = [];
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];

        foreach ($modules as $index => $module) {
            $count = $this->logModel
                ->where('module', $module)
                ->where('DATE(created_at) >=', date('Y-m-d', strtotime('-30 days')))
                ->countAllResults();

            if ($count > 0) {
                $labels[] = ucfirst($module);
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
     * Get top applications by datasets
     */
    private function getTopApplicationsByDatasets($limit = 10)
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                a.id,
                a.app_name,
                a.bidang,
                u.nama_lengkap as owner_name,
                COUNT(d.id) as dataset_count,
                SUM(d.total_rows) as total_data_rows
            FROM applications a
            JOIN users u ON u.id = a.user_id
            LEFT JOIN datasets d ON d.application_id = a.id AND d.deleted_at IS NULL
            WHERE a.deleted_at IS NULL
            GROUP BY a.id, a.app_name, a.bidang, u.nama_lengkap
            ORDER BY dataset_count DESC, total_data_rows DESC
            LIMIT ?
        ", [$limit]);

        return $query->getResultArray();
    }

    /**
     * Get system stats untuk AJAX
     */
    public function getSystemStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $stats = [
            'users' => [
                'total' => $this->userModel->where('deleted_at', null)->countAllResults(),
                'active' => $this->userModel->where('is_active', 1)->where('deleted_at', null)->countAllResults(),
                'inactive' => $this->userModel->where('is_active', 0)->where('deleted_at', null)->countAllResults()
            ],
            'applications' => [
                'total' => $this->applicationModel->where('deleted_at', null)->countAllResults(),
                'active' => $this->applicationModel->where('is_active', 1)->where('deleted_at', null)->countAllResults()
            ],
            'datasets' => [
                'total' => $this->datasetModel->where('deleted_at', null)->countAllResults(),
                'completed' => $this->datasetModel->where('upload_status', 'completed')->where('deleted_at', null)->countAllResults(),
                'processing' => $this->datasetModel->where('upload_status', 'processing')->countAllResults()
            ],
            'statistics' => [
                'total' => $this->statisticModel->where('deleted_at', null)->countAllResults(),
                'active' => $this->statisticModel->where('is_active', 1)->where('deleted_at', null)->countAllResults()
            ]
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get activity trends untuk chart
     */
    public function getActivityTrends()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $days = $this->request->getGet('days') ?? 7;
        
        $dates = [];
        $loginCounts = [];
        $uploadCounts = [];
        $createCounts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = date('d M', strtotime($date));

            // Count logins
            $logins = $this->logModel
                ->where('activity_type', 'login')
                ->where('DATE(created_at)', $date)
                ->countAllResults();
            $loginCounts[] = $logins;

            // Count uploads
            $uploads = $this->logModel
                ->where('activity_type', 'upload')
                ->where('DATE(created_at)', $date)
                ->countAllResults();
            $uploadCounts[] = $uploads;

            // Count creates
            $creates = $this->logModel
                ->where('activity_type', 'create')
                ->where('DATE(created_at)', $date)
                ->countAllResults();
            $createCounts[] = $creates;
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'Logins',
                        'data' => $loginCounts
                    ],
                    [
                        'label' => 'Uploads',
                        'data' => $uploadCounts
                    ],
                    [
                        'label' => 'Creates',
                        'data' => $createCounts
                    ]
                ]
            ]
        ]);
    }
}