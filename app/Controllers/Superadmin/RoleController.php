<?php

/**
 * ============================================================================
 * SUPERADMIN ROLE CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Superadmin/RoleController.php
 * 
 * Deskripsi:
 * Controller untuk superadmin mengelola roles dan permissions.
 * CRUD roles, manage permissions.
 * 
 * Fitur:
 * - List semua roles
 * - Create role baru
 * - Edit role & permissions
 * - Delete role
 * 
 * Role: Superadmin
 * ============================================================================
 */

namespace App\Controllers\Superadmin;

use App\Controllers\BaseController;
use App\Models\Superadmin\RoleModel;

class RoleController extends BaseController
{
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $roles = $this->roleModel
            ->select('roles.*, (SELECT COUNT(*) FROM user_roles WHERE role_id = roles.id) as user_count')
            ->findAll();

        $data = [
            'title' => 'Kelola Role',
            'roles' => $roles
        ];

        return view('superadmin/roles/index', $data);
    }

    public function create()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Tambah Role Baru',
            'validation' => \Config\Services::validation()
        ];

        return view('superadmin/roles/create', $data);
    }

    public function store()
    {
        $rules = [
            'role_name' => 'required|is_unique[roles.role_name]|alpha_dash',
            'role_label' => 'required|min_length[3]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $permissions = $this->request->getPost('permissions') ?? [];

            $roleData = [
                'role_name' => $this->request->getPost('role_name'),
                'role_label' => $this->request->getPost('role_label'),
                'description' => $this->request->getPost('description'),
                'permissions' => json_encode($permissions),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->roleModel->insert($roleData);

            $this->logActivity('create', 'roles', 'Superadmin create role: ' . $roleData['role_name']);

            return redirect()->to('/superadmin/roles')->with('success', 'Role berhasil ditambahkan');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->to('/superadmin/roles')->with('error', 'Role tidak ditemukan');
        }

        // Decode permissions
        $role['permissions'] = json_decode($role['permissions'], true) ?? [];

        $data = [
            'title' => 'Edit Role',
            'role' => $role,
            'validation' => \Config\Services::validation()
        ];

        return view('superadmin/roles/edit', $data);
    }

    public function update($id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->to('/superadmin/roles')->with('error', 'Role tidak ditemukan');
        }

        $rules = [
            'role_label' => 'required|min_length[3]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $permissions = $this->request->getPost('permissions') ?? [];

            $updateData = [
                'role_label' => $this->request->getPost('role_label'),
                'description' => $this->request->getPost('description'),
                'permissions' => json_encode($permissions),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->roleModel->update($id, $updateData);

            $this->logActivity('update', 'roles', 'Superadmin update role: ' . $role['role_name']);

            return redirect()->to('/superadmin/roles')->with('success', 'Role berhasil diupdate');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        // Don't allow deleting default roles
        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->response->setJSON(['success' => false, 'message' => 'Role tidak ditemukan']);
        }

        if (in_array($role['role_name'], ['superadmin', 'owner', 'viewer'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Role default tidak dapat dihapus']);
        }

        try {
            $this->roleModel->delete($id);

            $this->logActivity('delete', 'roles', 'Superadmin delete role: ' . $role['role_name']);

            return $this->response->setJSON(['success' => true, 'message' => 'Role berhasil dihapus']);

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