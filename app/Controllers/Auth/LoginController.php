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

        // Ambil role user dari user_roles
        $db = \Config\Database::connect();
        $userRole = $db->table('user_roles')
            ->select('user_roles.*, roles.role_name, roles.description as role_label, applications.id as app_id, applications.app_name')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->join('applications', 'applications.id = user_roles.application_id', 'left')
            ->where('user_roles.user_id', $user['id'])
            ->where('user_roles.is_active', 1)
            ->get()
            ->getRowArray();

        if (!$userRole) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Anda belum memiliki role. Silakan hubungi administrator');
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
            'bidang' => $user['bidang'],
            'avatar' => $user['avatar'],
            'role_id' => $userRole['role_id'],
            'role_name' => $userRole['role_name'],
            'role_label' => $userRole['role_label'],
            'application_id' => $userRole['application_id'] ?? null,
            'app_name' => $userRole['app_name'] ?? null,
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