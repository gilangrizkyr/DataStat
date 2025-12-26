<?php

/**
 * ============================================================================
 * VIEWER DASHBOARD CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Viewer/DashboardController.php
 * 
 * Deskripsi:
 * Controller untuk dashboard viewer (read-only access).
 * Viewer dapat melihat dashboard yang sudah dibuat owner, tapi tidak bisa edit.
 * 
 * Fitur:
 * - View dashboard default workspace
 * - View semua dashboard yang tersedia
 * - View dashboard by ID
 * - View dashboard by slug
 * - View public dashboard (via access token)
 * - Filter & refresh data
 * 
 * Role: Viewer (Read-Only)
 * ============================================================================
 */

namespace App\Controllers\Viewer;

use App\Controllers\BaseController;
use App\Models\Owner\DashboardModel;
use App\Models\Owner\DashboardWidgetModel;
use App\Models\Owner\StatisticConfigModel;
use App\Libraries\ComputationEngine;
use App\Libraries\VisualizationRenderer;

class DashboardController extends BaseController
{
    protected $dashboardModel;
    protected $widgetModel;
    protected $statisticModel;
    protected $computationEngine;
    protected $visualizationRenderer;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->widgetModel = new DashboardWidgetModel();
        $this->statisticModel = new StatisticConfigModel();
        $this->computationEngine = new ComputationEngine();
        $this->visualizationRenderer = new VisualizationRenderer();

        helper(['form', 'url']);
    }

    /**
     * Tampilkan default dashboard
     */
    public function index()
    {
        // Cek apakah user sudah login dan role viewer
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai viewer');
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return redirect()->to('/login')
                ->with('error', 'Anda belum memiliki akses ke workspace manapun. Hubungi owner untuk mendapat akses.');
        }

        // Get default dashboard
        $dashboard = $this->dashboardModel
            ->where('application_id', $applicationId)
            ->where('is_default', 1)
            ->where('deleted_at', null)
            ->first();

        // Jika tidak ada default, ambil yang pertama
        if (!$dashboard) {
            $dashboard = $this->dashboardModel
                ->where('application_id', $applicationId)
                ->where('deleted_at', null)
                ->orderBy('created_at', 'ASC')
                ->first();
        }

        if (!$dashboard) {
            return view('viewer/dashboard/index', [
                'title' => 'Dashboard',
                'message' => 'Belum ada dashboard yang tersedia. Hubungi owner untuk membuat dashboard.'
            ]);
        }

        // Redirect ke dashboard view
        return redirect()->to('/viewer/dashboard/view/' . $dashboard['id']);
    }

    /**
     * List semua dashboard yang tersedia
     */
    public function list()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $dashboards = $this->dashboardModel
            ->select('dashboards.*, users.nama_lengkap as creator_name,
                     (SELECT COUNT(*) FROM dashboard_widgets WHERE dashboard_id = dashboards.id) as widget_count')
            ->join('users', 'users.id = dashboards.created_by')
            ->where('dashboards.application_id', $applicationId)
            ->where('dashboards.deleted_at', null)
            ->orderBy('dashboards.is_default', 'DESC')
            ->orderBy('dashboards.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Daftar Dashboard',
            'dashboards' => $dashboards
        ];

        return view('viewer/dashboard/list', $data);
    }

    /**
     * View dashboard by ID
     */
    public function view($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        // Get dashboard
        $dashboard = $this->dashboardModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dashboard) {
            return redirect()->to('/viewer/dashboard')
                ->with('error', 'Dashboard tidak ditemukan atau Anda tidak memiliki akses');
        }

        // Get widgets dengan statistic data
        $widgets = $this->widgetModel
            ->select('dashboard_widgets.*, statistic_configs.*')
            ->join('statistic_configs', 'statistic_configs.id = dashboard_widgets.statistic_config_id')
            ->where('dashboard_widgets.dashboard_id', $id)
            ->where('dashboard_widgets.is_visible', 1)
            ->orderBy('dashboard_widgets.sort_order', 'ASC')
            ->findAll();

        // Calculate semua statistik dan render visualization
        $renderedWidgets = [];
        foreach ($widgets as $widget) {
            // Prepare config for computation engine
            $config = $widget;
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
                $widget['visualization_type'],
                $result,
                json_decode($widget['visualization_config'], true)
            );

            $renderedWidgets[] = [
                'widget' => $widget,
                'result' => $result,
                'visualization' => $visualization
            ];
        }

        // Log aktivitas view
        $this->logActivity('view', 'dashboards', 'Viewer melihat dashboard: ' . $dashboard['dashboard_name'], [
            'dashboard_id' => $id
        ]);

        $data = [
            'title' => $dashboard['dashboard_name'],
            'dashboard' => $dashboard,
            'widgets' => $renderedWidgets
        ];

        return view('viewer/dashboard/view', $data);
    }

    /**
     * View dashboard by slug
     */
    public function viewBySlug($slug)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $dashboard = $this->dashboardModel
            ->where('dashboard_slug', $slug)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dashboard) {
            return redirect()->to('/viewer/dashboard')
                ->with('error', 'Dashboard tidak ditemukan');
        }

        return $this->view($dashboard['id']);
    }

    /**
     * View public dashboard (via access token, no login required)
     */
    public function publicView($token)
    {
        $dashboard = $this->dashboardModel
            ->where('access_token', $token)
            ->where('is_public', 1)
            ->where('deleted_at', null)
            ->first();

        if (!$dashboard) {
            return view('errors/html/error_404', [
                'message' => 'Dashboard tidak ditemukan atau tidak tersedia untuk publik'
            ]);
        }

        // Get widgets
        $widgets = $this->widgetModel
            ->select('dashboard_widgets.*, statistic_configs.*')
            ->join('statistic_configs', 'statistic_configs.id = dashboard_widgets.statistic_config_id')
            ->where('dashboard_widgets.dashboard_id', $dashboard['id'])
            ->where('dashboard_widgets.is_visible', 1)
            ->orderBy('dashboard_widgets.sort_order', 'ASC')
            ->findAll();

        // Render widgets
        $renderedWidgets = [];
        foreach ($widgets as $widget) {
            // Prepare config for computation engine
            $config = $widget;
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
            $visualization = $this->visualizationRenderer->render(
                $widget['visualization_type'],
                $result,
                json_decode($widget['visualization_config'], true)
            );

            $renderedWidgets[] = [
                'widget' => $widget,
                'result' => $result,
                'visualization' => $visualization
            ];
        }

        $data = [
            'title' => $dashboard['dashboard_name'] . ' (Public View)',
            'dashboard' => $dashboard,
            'widgets' => $renderedWidgets,
            'is_public' => true
        ];

        return view('viewer/dashboard/public_view', $data);
    }

    /**
     * Refresh widget data via AJAX
     */
    public function refreshWidget($widgetId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $applicationId = session()->get('application_id');

            // Get widget dengan statistic
            $widget = $this->widgetModel
                ->select('dashboard_widgets.*, statistic_configs.*, dashboards.application_id')
                ->join('statistic_configs', 'statistic_configs.id = dashboard_widgets.statistic_config_id')
                ->join('dashboards', 'dashboards.id = dashboard_widgets.dashboard_id')
                ->where('dashboard_widgets.id', $widgetId)
                ->first();

            if (!$widget || $widget['application_id'] != $applicationId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Widget tidak ditemukan'
                ]);
            }

            // Recalculate
            $result = $this->computationEngine->calculate($widget, true); // Force recalculate

            // Render visualization
            $visualization = $this->visualizationRenderer->render(
                $widget['visualization_type'],
                $result,
                json_decode($widget['visualization_config'], true)
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
     * Export dashboard as PDF
     */
    public function exportPdf($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $dashboard = $this->dashboardModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dashboard) {
            return redirect()->to('/viewer/dashboard')
                ->with('error', 'Dashboard tidak ditemukan');
        }

        // TODO: Implement PDF export menggunakan library seperti TCPDF atau mPDF
        // Get widgets, render, dan generate PDF

        $this->logActivity('export', 'dashboards', 'Viewer export dashboard ke PDF: ' . $dashboard['dashboard_name'], [
            'dashboard_id' => $id
        ]);

        return redirect()->back()->with('info', 'Fitur export PDF akan segera tersedia');
    }

    /**
     * Print dashboard
     */
    public function print($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $dashboard = $this->dashboardModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dashboard) {
            return redirect()->to('/viewer/dashboard')
                ->with('error', 'Dashboard tidak ditemukan');
        }

        // Get widgets
        $widgets = $this->widgetModel
            ->select('dashboard_widgets.*, statistic_configs.*')
            ->join('statistic_configs', 'statistic_configs.id = dashboard_widgets.statistic_config_id')
            ->where('dashboard_widgets.dashboard_id', $id)
            ->where('dashboard_widgets.is_visible', 1)
            ->orderBy('dashboard_widgets.sort_order', 'ASC')
            ->findAll();

        // Render widgets
        $renderedWidgets = [];
        foreach ($widgets as $widget) {
            // Prepare config for computation engine
            $config = $widget;
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
            $visualization = $this->visualizationRenderer->render(
                $widget['visualization_type'],
                $result,
                json_decode($widget['visualization_config'], true)
            );

            $renderedWidgets[] = [
                'widget' => $widget,
                'result' => $result,
                'visualization' => $visualization
            ];
        }

        $this->logActivity('print', 'dashboards', 'Viewer print dashboard: ' . $dashboard['dashboard_name'], [
            'dashboard_id' => $id
        ]);

        $data = [
            'title' => $dashboard['dashboard_name'],
            'dashboard' => $dashboard,
            'widgets' => $renderedWidgets,
            'print_mode' => true
        ];

        return view('viewer/dashboard/print', $data);
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
