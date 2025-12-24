<?php

/**
 * ============================================================================
 * SUPERADMIN APPLICATION CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Superadmin/ApplicationController.php
 * 
 * Deskripsi:
 * Controller untuk superadmin mengelola semua aplikasi/workspace di sistem.
 * View, detail, activate/deactivate, view statistics per aplikasi.
 * 
 * Fitur:
 * - List semua aplikasi dengan filter
 * - View detail aplikasi (owner, datasets, statistics, users)
 * - View statistics aplikasi
 * - Activate/deactivate aplikasi
 * - Delete aplikasi (soft delete)
 * 
 * Role: Superadmin
 * ============================================================================
 */

namespace App\Controllers\Superadmin;

use App\Controllers\BaseController;
use App\Models\Owner\ApplicationModel;          // ✅ FIXED: Pakai dari Owner
use App\Models\Owner\DatasetModel;
use App\Models\Owner\StatisticConfigModel;
use App\Models\Superadmin\UserRoleModel;

class ApplicationController extends BaseController
{
    protected $applicationModel;
    protected $datasetModel;
    protected $statisticModel;
    protected $userRoleModel;

    public function __construct()
    {
        $this->applicationModel = new ApplicationModel();
        $this->datasetModel = new DatasetModel();
        $this->statisticModel = new StatisticConfigModel();
        $this->userRoleModel = new UserRoleModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        $builder = $this->applicationModel
            ->select('applications.*, users.nama_lengkap as owner_name,
                     (SELECT COUNT(*) FROM datasets WHERE application_id = applications.id AND deleted_at IS NULL) as dataset_count,
                     (SELECT COUNT(*) FROM statistic_configs WHERE application_id = applications.id AND deleted_at IS NULL) as statistic_count')
            ->join('users', 'users.id = applications.user_id')
            ->where('applications.deleted_at', null);

        if ($search) {
            $builder->groupStart()
                ->like('applications.app_name', $search)
                ->orLike('applications.bidang', $search)
                ->orLike('users.nama_lengkap', $search)
                ->groupEnd();
        }

        if ($status !== null && $status !== '') {
            $builder->where('applications.is_active', $status);
        }

        $applications = $builder->orderBy('applications.created_at', 'DESC')->findAll();

        $data = [
            'title' => 'Kelola Aplikasi',
            'applications' => $applications,
            'search' => $search,
            'status' => $status
        ];

        return view('superadmin/applications/index', $data);
    }

    public function detail($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $application = $this->applicationModel
            ->select('applications.*, users.nama_lengkap as owner_name, users.email as owner_email')
            ->join('users', 'users.id = applications.user_id')
            ->where('applications.id', $id)
            ->first();

        if (!$application) {
            return redirect()->to('/superadmin/applications')->with('error', 'Aplikasi tidak ditemukan');
        }

        $datasets = $this->datasetModel
            ->where('application_id', $id)
            ->where('deleted_at', null)
            ->findAll();

        $statistics = $this->statisticModel
            ->where('application_id', $id)
            ->where('deleted_at', null)
            ->findAll();

        $members = $this->userRoleModel
            ->select('users.nama_lengkap, users.email, roles.role_label, user_roles.created_at as joined_at')
            ->join('users', 'users.id = user_roles.user_id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.application_id', $id)
            ->findAll();

        $data = [
            'title' => 'Detail Aplikasi: ' . $application['app_name'],
            'application' => $application,
            'datasets' => $datasets,
            'statistics' => $statistics,
            'members' => $members
        ];

        return view('superadmin/applications/detail', $data);
    }

    public function statistics($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $application = $this->applicationModel->find($id);
        if (!$application) {
            return redirect()->to('/superadmin/applications')->with('error', 'Aplikasi tidak ditemukan');
        }

        $statistics = $this->statisticModel
            ->select('statistic_configs.*, datasets.dataset_name')
            ->join('datasets', 'datasets.id = statistic_configs.dataset_id')
            ->where('statistic_configs.application_id', $id)
            ->where('statistic_configs.deleted_at', null)
            ->findAll();

        $data = [
            'title' => 'Statistik: ' . $application['app_name'],
            'application' => $application,
            'statistics' => $statistics
        ];

        return view('superadmin/applications/statistics', $data);
    }

    public function toggleActive($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $app = $this->applicationModel->find($id);
        if (!$app) {
            return $this->response->setJSON(['success' => false, 'message' => 'Aplikasi tidak ditemukan']);
        }

        try {
            $newStatus = $app['is_active'] == 1 ? 0 : 1;
            $this->applicationModel->update($id, ['is_active' => $newStatus]);

            $this->logActivity('toggle_status', 'applications', 'Superadmin toggle status aplikasi', [
                'app_id' => $id,
                'new_status' => $newStatus
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Status aplikasi berhasil diubah',
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $app = $this->applicationModel->find($id);
        if (!$app) {
            return $this->response->setJSON(['success' => false, 'message' => 'Aplikasi tidak ditemukan']);
        }

        try {
            $this->applicationModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);

            $this->logActivity('delete', 'applications', 'Superadmin delete aplikasi: ' . $app['app_name'], [
                'app_id' => $id
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Aplikasi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function logActivity($activityType, $module, $description, $data = [])
    {
        $logData = [
            'user_id' => session()->get('user_id'),
            'application_id' => null,
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