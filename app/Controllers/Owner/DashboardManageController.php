<?php

/**
 * ============================================================================
 * OWNER DASHBOARD MANAGE CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Owner/DashboardManageController.php
 * 
 * Deskripsi:
 * Controller untuk mengelola dashboard workspace owner.
 * Owner dapat membuat, edit, dan mengatur layout dashboard dengan drag-drop widgets.
 * 
 * Fitur:
 * - List semua dashboard
 * - Create dashboard baru
 * - Edit dashboard
 * - Manage widgets (add, remove, arrange)
 * - Set default dashboard
 * - Share dashboard (public link)
 * 
 * Role: Owner
 * ============================================================================
 */

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\Owner\DashboardModel;
use App\Models\Owner\DashboardWidgetModel;
use App\Models\Owner\StatisticConfigModel;

class DashboardManageController extends BaseController
{
    protected $dashboardModel;
    protected $widgetModel;
    protected $statisticModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->widgetModel = new DashboardWidgetModel();
        $this->statisticModel = new StatisticConfigModel();
        helper(['form', 'url', 'text']);
    }

    /**
     * List semua dashboard
     */
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $dashboards = $this->dashboardModel
            ->select('dashboards.*, users.nama_lengkap as creator_name, 
                     (SELECT COUNT(*) FROM dashboard_widgets WHERE dashboard_id = dashboards.id) as widget_count')
            ->join('users', 'users.id = dashboards.created_by')
            ->where('dashboards.application_id', $applicationId)
            ->where('dashboards.deleted_at', null)
            ->findAll();

        $data = [
            'title' => 'Kelola Dashboard',
            'dashboards' => $dashboards
        ];

        return view('owner/dashboards/index', $data);
    }

    /**
     * Create dashboard baru
     */
    public function create()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Buat Dashboard Baru',
            'validation' => \Config\Services::validation()
        ];

        return view('owner/dashboards/create', $data);
    }

    /**
     * Store dashboard baru
     */
    public function store()
    {
        $rules = [
            'dashboard_name' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $applicationId = session()->get('application_id');
            $userId = session()->get('user_id');
            $dashboardName = $this->request->getPost('dashboard_name');
            $slug = url_title($dashboardName, '-', true) . '-' . uniqid();

            $dashboardData = [
                'application_id' => $applicationId,
                'dashboard_name' => $dashboardName,
                'dashboard_slug' => $slug,
                'description' => $this->request->getPost('description'),
                'is_default' => $this->request->getPost('is_default') ? 1 : 0,
                'is_public' => 0,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Jika set sebagai default, remove default dari dashboard lain
            if ($dashboardData['is_default']) {
                $this->dashboardModel
                    ->where('application_id', $applicationId)
                    ->set(['is_default' => 0])
                    ->update();
            }

            $dashboardId = $this->dashboardModel->insert($dashboardData);

            $this->logActivity('create', 'dashboards', 'Owner create dashboard: ' . $dashboardName, [
                'dashboard_id' => $dashboardId
            ]);

            return redirect()->to('/owner/dashboards/manage/' . $dashboardId)
                ->with('success', 'Dashboard berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Edit dashboard
     */
    public function edit($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $dashboard = $this->dashboardModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dashboard) {
            return redirect()->to('/owner/dashboards')->with('error', 'Dashboard tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Dashboard',
            'dashboard' => $dashboard,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/dashboards/edit', $data);
    }

    /**
     * Update dashboard
     */
    public function update($id)
    {
        $rules = [
            'dashboard_name' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $applicationId = session()->get('application_id');

        $dashboard = $this->dashboardModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->first();

        if (!$dashboard) {
            return redirect()->to('/owner/dashboards')->with('error', 'Dashboard tidak ditemukan');
        }

        try {
            $updateData = [
                'dashboard_name' => $this->request->getPost('dashboard_name'),
                'description' => $this->request->getPost('description'),
                'is_default' => $this->request->getPost('is_default') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($updateData['is_default']) {
                $this->dashboardModel
                    ->where('application_id', $applicationId)
                    ->where('id !=', $id)
                    ->set(['is_default' => 0])
                    ->update();
            }

            $this->dashboardModel->update($id, $updateData);

            $this->logActivity('update', 'dashboards', 'Owner update dashboard', ['dashboard_id' => $id]);

            return redirect()->to('/owner/dashboards')->with('success', 'Dashboard berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Manage dashboard widgets (drag & drop interface)
     */
    public function manage($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $dashboard = $this->dashboardModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dashboard) {
            return redirect()->to('/owner/dashboards')->with('error', 'Dashboard tidak ditemukan');
        }

        // Get widgets
        $widgets = $this->widgetModel
            ->select('dashboard_widgets.*, statistic_configs.stat_name, statistic_configs.visualization_type')
            ->join('statistic_configs', 'statistic_configs.id = dashboard_widgets.statistic_config_id')
            ->where('dashboard_widgets.dashboard_id', $id)
            ->orderBy('dashboard_widgets.sort_order', 'ASC')
            ->findAll();

        // Get available statistics
        $statistics = $this->statisticModel
            ->where('application_id', $applicationId)
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->findAll();

        $data = [
            'title' => 'Kelola Dashboard: ' . $dashboard['dashboard_name'],
            'dashboard' => $dashboard,
            'widgets' => $widgets,
            'statistics' => $statistics
        ];

        return view('owner/dashboards/manage', $data);
    }

    /**
     * Add widget to dashboard
     */
    public function addWidget()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        try {
            $widgetData = [
                'dashboard_id' => $this->request->getPost('dashboard_id'),
                'statistic_config_id' => $this->request->getPost('statistic_id'),
                'widget_title' => $this->request->getPost('widget_title'),
                'width' => $this->request->getPost('width') ?? 6,
                'height' => $this->request->getPost('height') ?? 300,
                'sort_order' => $this->request->getPost('sort_order') ?? 0,
                'is_visible' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $widgetId = $this->widgetModel->insert($widgetData);

            $this->logActivity('create', 'dashboard_widgets', 'Owner add widget ke dashboard', [
                'widget_id' => $widgetId
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Widget berhasil ditambahkan',
                'widget_id' => $widgetId
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update widget layout (posisi, ukuran)
     */
    public function updateWidgetLayout()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        try {
            $widgets = $this->request->getJSON(true);

            foreach ($widgets as $widget) {
                $this->widgetModel->update($widget['id'], [
                    'position_x' => $widget['position_x'],
                    'position_y' => $widget['position_y'],
                    'width' => $widget['width'],
                    'height' => $widget['height'],
                    'sort_order' => $widget['sort_order']
                ]);
            }

            $this->logActivity('update', 'dashboard_widgets', 'Owner update layout widgets', []);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Layout berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove widget
     */
    public function removeWidget($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        try {
            $this->widgetModel->delete($id);

            $this->logActivity('delete', 'dashboard_widgets', 'Owner remove widget', ['widget_id' => $id]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Widget berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete dashboard
     */
    public function delete($id)
    {
        $applicationId = session()->get('application_id');

        $dashboard = $this->dashboardModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dashboard) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Dashboard tidak ditemukan'
                ]);
            } else {
                return redirect()->to('/owner/dashboards')
                    ->with('error', 'Dashboard tidak ditemukan');
            }
        }

        // Handle GET request - show confirmation page
        if ($this->request->getMethod() === 'GET') {
            $data = [
                'title' => 'Konfirmasi Hapus Dashboard',
                'dashboard' => $dashboard
            ];

            return view('owner/dashboards/delete', $data);
        }

        // Handle POST/AJAX request - perform deletion
        try {
            $this->dashboardModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);

            $this->logActivity('delete', 'dashboards', 'Owner delete dashboard: ' . $dashboard['dashboard_name'], ['dashboard_id' => $id]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Dashboard berhasil dihapus'
                ]);
            } else {
                return redirect()->to('/owner/dashboards')
                    ->with('success', 'Dashboard berhasil dihapus');
            }
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            } else {
                return redirect()->to('/owner/dashboards')
                    ->with('error', 'Error: ' . $e->getMessage());
            }
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
