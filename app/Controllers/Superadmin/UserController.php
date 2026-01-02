<?php

/**
 * ============================================================================
 * SUPERADMIN USER CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Superadmin/UserController.php
 * 
 * Deskripsi:
 * Controller untuk superadmin mengelola semua user di sistem.
 * CRUD user, activate/deactivate, reset password, view detail, dll.
 * 
 * Fitur:
 * - List semua user dengan filter & search
 * - Create user baru
 * - Edit user
 * - View detail user (info, roles, aktivitas)
 * - Activate/deactivate user
 * - Reset password user
 * - Delete user (soft delete)
 * 
 * Role: Superadmin
 * ============================================================================
 */

namespace App\Controllers\Superadmin;

use App\Controllers\BaseController;
use App\Models\Superadmin\UserModel;
use App\Models\Superadmin\UserRoleModel;
use App\Models\Superadmin\LogActivityModel;

class UserController extends BaseController
{
    protected $userModel;
    protected $userRoleModel;
    protected $logModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
        $this->logModel = new LogActivityModel();
        helper(['form', 'url']);
    }

    /**
     * List semua user
     */
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        // Filter & search
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        $builder = $this->userModel
            ->select('users.*, 
                     (SELECT COUNT(*) FROM user_roles WHERE user_id = users.id) as role_count,
                     (SELECT COUNT(*) FROM applications WHERE user_id = users.id AND deleted_at IS NULL) as app_count')
            ->where('users.deleted_at', null);

        if ($search) {
            $builder->groupStart()
                ->like('users.nama_lengkap', $search)
                ->orLike('users.email', $search)
                ->orLike('users.bidang', $search)
                ->groupEnd();
        }

        if ($status !== null && $status !== '') {
            $builder->where('users.is_active', $status);
        }

        $users = $builder->orderBy('users.created_at', 'DESC')->findAll();

        $data = [
            'title' => 'Kelola User',
            'users' => $users,
            'search' => $search,
            'status' => $status
        ];

        return view('superadmin/users/index', $data);
    }

    /**
     * Form create user
     */
    public function create()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Tambah User Baru',
            'validation' => \Config\Services::validation()
        ];

        return view('superadmin/users/create', $data);
    }

    /**
     * Store user baru
     */
    public function store()
    {
        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'bidang' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $userData = [
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'email' => $this->request->getPost('email'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'bidang' => $this->request->getPost('bidang'),
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->userModel->insert($userData);

            $this->logActivity('create', 'users', 'Superadmin create user: ' . $userData['email'], [
                'user_id' => $userId
            ]);

            return redirect()->to('/superadmin/users')->with('success', 'User berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Form edit user
     */
    public function edit($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $user = $this->userModel
            ->select('users.*,
                     (SELECT COUNT(*) FROM user_roles WHERE user_id = users.id) as role_count,
                     (SELECT COUNT(*) FROM applications WHERE user_id = users.id AND deleted_at IS NULL) as app_count')
            ->find($id);

        if (!$user) {
            return redirect()->to('/superadmin/users')->with('error', 'User tidak ditemukan');
        }

        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        return view('superadmin/users/edit', $data);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/superadmin/users')->with('error', 'User tidak ditemukan');
        }

        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[255]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'bidang' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $updateData = [
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'email' => $this->request->getPost('email'),
                'bidang' => $this->request->getPost('bidang'),
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update password jika diisi
            $newPassword = $this->request->getPost('password');
            if (!empty($newPassword)) {
                $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $this->userModel->update($id, $updateData);

            $this->logActivity('update', 'users', 'Superadmin update user: ' . $updateData['email'], [
                'user_id' => $id
            ]);

            return redirect()->to('/superadmin/users')->with('success', 'User berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Detail user
     */
    public function detail($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/superadmin/users')->with('error', 'User tidak ditemukan');
        }

        // Get user roles
        $roles = $this->userRoleModel
            ->select('user_roles.*, roles.role_name, roles.role_label, applications.app_name')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->join('applications', 'applications.id = user_roles.application_id', 'left')
            ->where('user_roles.user_id', $id)
            ->findAll();

        // Get user activities
        $activities = $this->logModel
            ->where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->findAll();

        $data = [
            'title' => 'Detail User: ' . $user['nama_lengkap'],
            'user' => $user,
            'roles' => $roles,
            'activities' => $activities
        ];

        return view('superadmin/users/detail', $data);
    }

    /**
     * Toggle active/inactive
     */
    public function toggleActive($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan']);
        }

        try {
            $newStatus = $user['is_active'] == 1 ? 0 : 1;
            $this->userModel->update($id, ['is_active' => $newStatus]);

            $this->logActivity('toggle_status', 'users', 'Superadmin toggle user status', [
                'user_id' => $id,
                'new_status' => $newStatus
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Status user berhasil diubah',
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset password user
     */
    public function resetPassword($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan']);
        }

        try {
            // Generate random password
            $newPassword = bin2hex(random_bytes(4)); // 8 karakter
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $this->userModel->update($id, ['password' => $hashedPassword]);

            $this->logActivity('reset_password', 'users', 'Superadmin reset password user', [
                'user_id' => $id
            ]);

            // TODO: Send email dengan password baru

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Password berhasil direset',
                'new_password' => $newPassword
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete user (soft delete)
     */
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        // Don't allow deleting self
        if ($id == session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri'
            ]);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan']);
        }

        try {
            $this->userModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);

            $this->logActivity('delete', 'users', 'Superadmin delete user: ' . $user['email'], [
                'user_id' => $id
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Log aktivitas
     */
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
