<?php

/**
 * ============================================================================
 * VIEWER PROFILE CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Viewer/ProfileController.php
 * 
 * Deskripsi:
 * Controller untuk viewer mengelola profile sendiri.
 * Viewer bisa update data pribadi, change password, upload avatar.
 * 
 * Fitur:
 * - View profile
 * - Update profile (nama, email, bidang)
 * - Change password
 * - Upload avatar
 * 
 * Role: Viewer
 * ============================================================================
 */

namespace App\Controllers\Viewer;

use App\Controllers\BaseController;
use App\Models\Superadmin\UserModel;

class ProfileController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url', 'filesystem']);
    }

    /**
     * View profile
     */
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'viewer') {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'Profile Saya',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        return view('viewer/profile/index', $data);
    }

    /**
     * Update profile
     */
    public function update()
    {
        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email',
            'bidang' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $email = $this->request->getPost('email');

            // Cek email unique (except current user)
            $existingEmail = $this->userModel
                ->where('email', $email)
                ->where('id !=', $userId)
                ->first();

            if ($existingEmail) {
                return redirect()->back()->withInput()
                    ->with('error', 'Email sudah digunakan user lain');
            }

            $updateData = [
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'email' => $email,
                'bidang' => $this->request->getPost('bidang'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->userModel->update($userId, $updateData);

            // Update session
            session()->set([
                'email' => $email,
                'nama_lengkap' => $updateData['nama_lengkap'],
                'bidang' => $updateData['bidang']
            ]);

            $this->logActivity('update', 'profile', 'Viewer update profile');

            return redirect()->back()->with('success', 'Profile berhasil diupdate');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $user = $this->userModel->find($userId);

            // Verify current password
            if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
                return redirect()->back()->with('error', 'Password lama salah');
            }

            // Update password
            $newPassword = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
            
            $this->userModel->update($userId, [
                'password' => $newPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->logActivity('update', 'profile', 'Viewer change password');

            return redirect()->back()->with('success', 'Password berhasil diubah');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar()
    {
        $rules = [
            'avatar' => 'uploaded[avatar]|is_image[avatar]|mime_in[avatar,image/jpg,image/jpeg,image/png]|max_size[avatar,2048]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $user = $this->userModel->find($userId);

            $avatar = $this->request->getFile('avatar');

            if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
                // Delete old avatar
                if ($user['avatar'] && file_exists(FCPATH . $user['avatar'])) {
                    unlink(FCPATH . $user['avatar']);
                }

                // Upload new avatar
                $newName = $avatar->getRandomName();
                $avatar->move(FCPATH . 'uploads/avatars', $newName);
                $avatarPath = 'uploads/avatars/' . $newName;

                $this->userModel->update($userId, [
                    'avatar' => $avatarPath,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Update session
                session()->set('avatar', $avatarPath);

                $this->logActivity('update', 'profile', 'Viewer upload avatar');

                return redirect()->back()->with('success', 'Avatar berhasil diupload');
            }

            return redirect()->back()->with('error', 'Gagal upload avatar');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
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