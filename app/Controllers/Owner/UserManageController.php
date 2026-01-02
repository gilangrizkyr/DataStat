<?php

/**
 * ============================================================================
 * OWNER USER MANAGE CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Owner/UserManageController.php
 * 
 * Deskripsi:
 * Controller untuk owner mengelola user di workspace mereka.
 * Owner dapat invite user lain sebagai viewer atau owner.
 * 
 * Fitur:
 * - List users di workspace
 * - Invite user baru (kirim email invite)
 * - Manage user roles (owner/viewer)
 * - Remove user dari workspace
 * 
 * Role: Owner
 * ============================================================================
 */

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\Superadmin\UserModel;
use App\Models\Superadmin\UserRoleModel;
use App\Models\Superadmin\RoleModel;
use App\Models\Owner\ApplicationModel;
use App\Libraries\MailtrapEmail;

class UserManageController extends BaseController
{
    protected $userModel;
    protected $userRoleModel;
    protected $roleModel;
    protected $applicationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
        $this->roleModel = new RoleModel();
        $this->applicationModel = new ApplicationModel();
        helper(['form', 'url', 'text']);
    }

    /**
     * List users di workspace
     */
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $users = $this->userRoleModel
            ->select('users.id, users.nama_lengkap, users.email, users.bidang, users.is_active,
                     roles.role_name, roles.role_label, user_roles.created_at as joined_at')
            ->join('users', 'users.id = user_roles.user_id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.application_id', $applicationId)
            ->where('users.deleted_at', null)
            ->findAll();

        $data = [
            'title' => 'Kelola User',
            'users' => $users
        ];

        return view('owner/users/index', $data);
    }

    /**
     * Form invite user
     */
    public function invite()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        $roles = $this->roleModel
            ->whereIn('role_name', ['owner', 'viewer'])
            ->findAll();

        $data = [
            'title' => 'Invite User',
            'roles' => $roles,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/users/invite', $data);
    }

    /**
     * Process invite
     */
    public function sendInvite()
    {
        $rules = [
            'email' => 'required|valid_email',
            'role_id' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $email = $this->request->getPost('email');
            $roleId = $this->request->getPost('role_id');
            $message = $this->request->getPost('message');
            $sendEmail = $this->request->getPost('send_email');
            $applicationId = session()->get('application_id');

            // Cek apakah user sudah terdaftar
            $user = $this->userModel->where('email', $email)->first();

            if (!$user) {
                return redirect()->back()->withInput()
                    ->with('error', 'Email tidak terdaftar. User harus register terlebih dahulu.');
            }

            // Cek apakah sudah member
            $existingMember = $this->userRoleModel
                ->where('user_id', $user['id'])
                ->where('application_id', $applicationId)
                ->first();

            if ($existingMember) {
                return redirect()->back()->withInput()
                    ->with('error', 'User sudah menjadi member workspace ini.');
            }

            // Add user ke workspace
            $this->userRoleModel->insert([
                'user_id' => $user['id'],
                'role_id' => $roleId,
                'application_id' => $applicationId,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send email notification if requested
            if ($sendEmail) {
                $emailResult = $this->sendInviteEmail($user, $roleId, $message);
                if (!$emailResult) {
                    log_message('error', 'Failed to send invitation email to: ' . $user['email']);
                    session()->setFlashdata('email_debug', 'Email failed to send to: ' . $user['email']);
                } else {
                    log_message('info', 'Invitation email logged successfully to: ' . $user['email']);
                    session()->setFlashdata('email_debug', 'Email logged successfully to: ' . $user['email']);
                }
            }

            $this->logActivity('invite', 'users', 'Owner invite user: ' . $email, [
                'user_id' => $user['id'],
                'role_id' => $roleId,
                'send_email' => $sendEmail
            ]);

            $successMessage = 'User berhasil ditambahkan ke workspace';
            if ($sendEmail) {
                $successMessage .= ' dan email invitation telah dikirim';
            }

            return redirect()->to('/owner/users')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Manage user roles
     */
    public function manageRoles($userId)
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('/owner/users')->with('error', 'User tidak ditemukan');
        }

        $userRole = $this->userRoleModel
            ->where('user_id', $userId)
            ->where('application_id', $applicationId)
            ->first();

        if (!$userRole) {
            return redirect()->to('/owner/users')->with('error', 'User tidak ada di workspace ini');
        }

        // Add joined_at to user data
        $user['joined_at'] = $userRole['created_at'];

        // Get current role details
        $currentRole = $this->roleModel->find($userRole['role_id']);
        if ($currentRole) {
            $user['role_name'] = $currentRole['role_name'];
            $user['role_label'] = $currentRole['role_label'];
            $user['role_description'] = $currentRole['description'];
        } else {
            $user['role_name'] = 'unknown';
            $user['role_label'] = 'Unknown Role';
            $user['role_description'] = 'Role tidak ditemukan';
        }
        $user['role_id'] = $userRole['role_id'];

        $roles = $this->roleModel->whereIn('role_name', ['owner', 'viewer'])->findAll();

        $data = [
            'title' => 'Kelola Role: ' . $user['nama_lengkap'],
            'user' => $user,
            'user_role' => $userRole,
            'roles' => $roles,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/users/manage_roles', $data);
    }

    /**
     * Update user role
     */
    public function updateRole($userId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        try {
            $roleId = $this->request->getPost('role_id');
            $applicationId = session()->get('application_id');

            $this->userRoleModel
                ->where('user_id', $userId)
                ->where('application_id', $applicationId)
                ->set(['role_id' => $roleId, 'updated_at' => date('Y-m-d H:i:s')])
                ->update();

            $this->logActivity('update', 'users', 'Owner update role user', [
                'user_id' => $userId,
                'new_role_id' => $roleId
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Role berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove user dari workspace
     */
    public function remove($userId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        // Don't allow removing self
        if ($userId == session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus diri sendiri'
            ]);
        }

        try {
            $this->userRoleModel
                ->where('user_id', $userId)
                ->where('application_id', $applicationId)
                ->delete();

            $this->logActivity('remove', 'users', 'Owner remove user dari workspace', [
                'user_id' => $userId
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'User berhasil dihapus dari workspace'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send invitation email
     */
    private function sendInviteEmail($user, $roleId, $message = null)
    {
        $role = $this->roleModel->find($roleId);
        $application = $this->applicationModel->find(session()->get('application_id'));

        $mailtrap = new MailtrapEmail();

        return $mailtrap->sendInvitationEmail($user, $role['role_label'], $application['app_name'], $message);
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
