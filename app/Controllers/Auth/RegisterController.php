<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\Superadmin\UserModel;
use App\Models\Superadmin\RoleModel;
use App\Models\Superadmin\UserRoleModel;

class RegisterController extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $userRoleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        helper(['form', 'url']);
    }

    /**
     * Tampilkan halaman register
     */
    public function index()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }

        $data = [
            'title' => 'Register',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/register', $data);
    }

    /**
     * Proses registrasi user baru
     */
    public function store()
    {
        // Validasi input
        $rules = [
            'nama_lengkap' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama lengkap harus diisi',
                    'min_length' => 'Nama lengkap minimal 3 karakter',
                    'max_length' => 'Nama lengkap maksimal 255 karakter'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Email tidak valid',
                    'is_unique' => 'Email sudah terdaftar'
                ]
            ],
            'bidang' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Bidang/Departemen harus diisi',
                    'min_length' => 'Bidang minimal 3 karakter',
                    'max_length' => 'Bidang maksimal 100 karakter'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[6]|max_length[255]',
                'errors' => [
                    'required' => 'Password harus diisi',
                    'min_length' => 'Password minimal 6 karakter',
                    'max_length' => 'Password maksimal 255 karakter'
                ]
            ],
            'password_confirm' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Konfirmasi password harus diisi',
                    'matches' => 'Konfirmasi password tidak cocok'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Hash password
            $hashedPassword = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);

            // Data user baru
            $userData = [
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'email' => $this->request->getPost('email'),
                'bidang' => $this->request->getPost('bidang'),
                'password' => $hashedPassword,
                'is_active' => 1, // Langsung aktif, atau bisa set 0 jika perlu verifikasi email
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert user
            $userId = $this->userModel->insert($userData);

            if (!$userId) {
                throw new \Exception('Gagal menyimpan data user');
            }

            // Ambil role 'owner' sebagai default untuk user yang register
            $ownerRole = $this->roleModel->where('role_name', 'owner')->first();

            if (!$ownerRole) {
                throw new \Exception('Role owner tidak ditemukan');
            }

            // Assign role owner ke user (application_id = null, user belum buat aplikasi)
            // User akan membuat aplikasi setelah login pertama kali
            $userRoleData = [
                'user_id' => $userId,
                'role_id' => $ownerRole['id'],
                'application_id' => null, // Akan diisi setelah user membuat aplikasi
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->userRoleModel->insert($userRoleData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal');
            }

            // Log aktivitas
            $this->logActivity($userId, 'register', 'users', 'User baru berhasil registrasi', [
                'email' => $userData['email'],
                'nama_lengkap' => $userData['nama_lengkap']
            ]);

            // Redirect ke login dengan pesan sukses
            return redirect()->to('/login')
                ->with('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');

        } catch (\Exception $e) {
            $db->transRollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Registrasi gagal: ' . $e->getMessage());
        }
    }

    /**
     * Redirect ke dashboard sesuai role
     */
    private function redirectToDashboard()
    {
        $roleName = session()->get('role_name');

        switch ($roleName) {
            case 'superadmin':
                return redirect()->to('/superadmin/dashboard');
            
            case 'owner':
                return redirect()->to('/owner/dashboard');
            
            case 'viewer':
                return redirect()->to('/viewer/dashboard');
            
            default:
                return redirect()->to('/');
        }
    }

    /**
     * Log aktivitas user
     */
    private function logActivity($userId, $activityType, $module, $description, $data = [])
    {
        $logData = [
            'user_id' => $userId,
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