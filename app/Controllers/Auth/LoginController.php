<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\Superadmin\UserModel;
use App\Models\Superadmin\UserRoleModel;
use App\Libraries\AuditLogger;

class LoginController extends BaseController
{
    protected $userModel;
    protected $userRoleModel;
    protected $auditLogger;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
        helper(['form', 'url', 'cookie']);  // ✅ Added 'cookie' helper
    }

    /**
     * Tampilkan halaman login
     */
    public function index()
    {
        // Jika sudah login, redirect ke dashboard sesuai role
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }

        $data = [
            'title' => 'Login',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/login', $data);
    }

    /**
     * Proses login
     */
    public function authenticate()
    {
        // Validasi input
        $rules = [
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Email tidak valid'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 6 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        // Cek user di database
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau password salah');
        }

        // Cek apakah user aktif
        if ($user['is_active'] != 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator');
        }

        // Cek apakah user sudah dihapus (soft delete)
        if ($user['deleted_at'] !== null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda tidak ditemukan');
        }

        // Verifikasi password
        if (!password_verify($password, $user['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau password salah');
        }

        // ✅ PERBAIKAN: Ambil role dari tabel user_roles (pivot table)
        $db = \Config\Database::connect();

        $roleData = $db->table('user_roles')
            ->select('user_roles.*, roles.id as role_id, roles.role_name, roles.description as role_label')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $user['id'])
            ->get()
            ->getRowArray();

        if (!$roleData || !$roleData['role_name']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Anda belum memiliki role. Silakan hubungi administrator');
        }

        // Untuk Owner dan Viewer, ambil application dari user_applications
        $applicationData = null;
        if (in_array($roleData['role_name'], ['owner', 'viewer'])) {
            $applicationData = $db->table('user_applications')
                ->select('user_applications.*, applications.app_name, applications.app_description')
                ->join('applications', 'applications.id = user_applications.application_id')
                ->where('user_applications.user_id', $user['id'])
                ->where('applications.is_active', 1)
                ->get()
                ->getRowArray();

            if (!$applicationData) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Anda belum terdaftar di workspace manapun. Silakan hubungi administrator');
            }
        }

        // Update last login
        $this->userModel->update($user['id'], [
            'last_login' => date('Y-m-d H:i:s')
        ]);

        // Set session
        $sessionData = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'nama_lengkap' => $user['nama_lengkap'],
            'bidang' => $user['bidang'] ?? null,
            'avatar' => $user['avatar'] ?? null,
            'role_id' => $roleData['role_id'],
            'role_name' => $roleData['role_name'],
            'role_label' => $roleData['role_label'] ?? $roleData['role_name'],
            'application_id' => $applicationData['application_id'] ?? null,
            'app_name' => $applicationData['app_name'] ?? null,
            'logged_in' => true
        ];

        session()->set($sessionData);

        // Set remember me cookie jika dicentang
        if ($remember) {
            $this->setRememberMeCookie($user['id']);
        }

        // Log aktivitas
        $this->logActivity('login', 'users', 'User berhasil login', [
            'email' => $email,
            'ip_address' => $this->request->getIPAddress()
        ]);

        // Redirect ke dashboard sesuai role
        return $this->redirectToDashboard();
    }

    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie($userId)
    {
        $token = bin2hex(random_bytes(32));

        // Simpan token ke database (opsional, bisa ditambahkan tabel remember_tokens)
        // Untuk sementara simpan di cookie saja

        set_cookie([
            'name' => 'remember_token',
            'value' => $token,
            'expire' => 30 * 24 * 60 * 60, // 30 hari
            'path' => '/',
            'secure' => false, // set true jika menggunakan HTTPS
            'httponly' => true
        ]);

        set_cookie([
            'name' => 'remember_user',
            'value' => $userId,
            'expire' => 30 * 24 * 60 * 60,
            'path' => '/',
            'secure' => false,
            'httponly' => true
        ]);
    }

    /**
     * Redirect ke dashboard sesuai role
     */
    private function redirectToDashboard()
    {
        $roleName = session()->get('role_name');

        switch ($roleName) {
            case 'superadmin':
                return redirect()->to('/superadmin/dashboard')->with('success', 'Selamat datang, ' . session()->get('nama_lengkap'));

            case 'owner':
                return redirect()->to('/owner/dashboard')->with('success', 'Selamat datang, ' . session()->get('nama_lengkap'));

            case 'viewer':
                return redirect()->to('/viewer/dashboard')->with('success', 'Selamat datang, ' . session()->get('nama_lengkap'));

            default:
                return redirect()->to('/')->with('error', 'Role tidak dikenali');
        }
    }

    /**
     * Log aktivitas user
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
