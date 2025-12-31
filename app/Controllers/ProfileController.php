<?php

/**
 * ============================================================================
 * PROFILE CONTROLLER
 * ============================================================================
 *
 * Path: app/Controllers/ProfileController.php
 *
 * Deskripsi:
 * Controller untuk semua user mengelola profile sendiri.
 * Update data pribadi, change password, upload avatar, settings.
 *
 * Fitur:
 * - View profile
 * - Update profile (nama, email, bidang)
 * - Change password
 * - Upload avatar
 * - Settings (theme, language, timezone)
 *
 * Role: All authenticated users
 * ============================================================================
 */

namespace App\Controllers;

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
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User tidak ditemukan');
        }

        $data = [
            'title' => 'Profile Saya',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        // Load role-specific profile view
        $role = session()->get('role_name') ?? 'viewer';
        $viewPath = $role . '/profile/index';
        $filePath = APPPATH . 'Views/' . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php';

        // Check if role-specific view exists, otherwise fallback to generic
        if (file_exists($filePath)) {
            return view($viewPath, $data);
        }

        return view('profile/index', $data);
    }

    /**
     * Edit profile form
     */
    public function edit()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Profile',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        // Load role-specific edit view
        $role = session()->get('role_name') ?? 'viewer';
        $viewPath = $role . '/profile/edit';
        $filePath = APPPATH . 'Views/' . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php';

        // Check if role-specific view exists, otherwise fallback to generic
        if (file_exists($filePath)) {
            return view($viewPath, $data);
        }

        return view('profile/edit', $data);
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

            $this->logActivity('update', 'profile', 'User update profile');

            return redirect()->back()->with('success', 'Profile berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Change password form
     */
    public function changePasswordForm()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $data = [
            'title' => 'Ubah Password',
            'validation' => \Config\Services::validation()
        ];

        // Load role-specific change password view
        $role = session()->get('role_name') ?? 'viewer';
        $viewPath = $role . '/profile/change_password';
        $filePath = APPPATH . 'Views/' . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php';

        // Check if role-specific view exists, otherwise fallback to generic
        if (file_exists($filePath)) {
            return view($viewPath, $data);
        }

        return view('profile/change_password', $data);
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');
            $currentPassword = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');

            // Cek password saat ini
            $user = $this->userModel->find($userId);
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Password saat ini salah');
            }

            // Cek apakah password baru sama dengan yang lama
            if (password_verify($newPassword, $user['password'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Password baru tidak boleh sama dengan password lama');
            }

            // Update password
            $this->userModel->update($userId, [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Log aktivitas
            $this->logActivity('update', 'profile', 'User mengubah password', [
                'user_id' => $userId
            ]);

            return redirect()->to('/profile')
                ->with('success', 'Password berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengubah password: ' . $e->getMessage());
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

                $this->logActivity('update', 'profile', 'User upload avatar');

                return redirect()->back()->with('success', 'Avatar berhasil diupload');
            }

            return redirect()->back()->with('error', 'Gagal upload avatar');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar()
    {
        try {
            $userId = session()->get('user_id');
            $user = $this->userModel->find($userId);

            if ($user['avatar'] && file_exists(FCPATH . $user['avatar'])) {
                unlink(FCPATH . $user['avatar']);
            }

            $this->userModel->update($userId, [
                'avatar' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Update session
            session()->set('avatar', null);

            $this->logActivity('delete', 'profile', 'User delete avatar');

            return redirect()->back()->with('success', 'Avatar berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus avatar: ' . $e->getMessage());
        }
    }

    /**
     * Settings page
     */
    public function settings()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User tidak ditemukan');
        }

        $data = [
            'title' => 'Pengaturan Akun',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        // Load role-specific settings view
        $role = session()->get('role_name') ?? 'viewer';
        $viewPath = $role . '/profile/settings';
        $filePath = APPPATH . 'Views/' . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php';

        // Check if role-specific view exists, otherwise fallback to generic
        if (file_exists($filePath)) {
            return view($viewPath, $data);
        }

        return view('profile/settings', $data);
    }

    /**
     * Update settings
     */
    public function updateSettings()
    {
        $rules = [
            'theme' => [
                'rules' => 'required|in_list[light,dark,auto]',
                'errors' => [
                    'required' => 'Tema harus dipilih',
                    'in_list' => 'Tema tidak valid'
                ]
            ],
            'language' => [
                'rules' => 'required|in_list[id,en]',
                'errors' => [
                    'required' => 'Bahasa harus dipilih',
                    'in_list' => 'Bahasa tidak valid'
                ]
            ],
            'timezone' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Timezone harus dipilih'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $userId = session()->get('user_id');

            // Update user settings
            $updateData = [
                'theme' => $this->request->getPost('theme'),
                'language' => $this->request->getPost('language'),
                'timezone' => $this->request->getPost('timezone'),
                'sidebar_collapsed' => $this->request->getPost('sidebar_collapsed') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->userModel->update($userId, $updateData);

            // Log aktivitas
            $this->logActivity('update', 'profile', 'User mengubah pengaturan akun', [
                'user_id' => $userId
            ]);

            return redirect()->to('/profile/settings')
                ->with('success', 'Pengaturan berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
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
