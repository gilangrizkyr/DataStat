<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\Superadmin\UserModel;

class ForgotPasswordController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url', 'text']);
    }

    /**
     * Tampilkan halaman forgot password
     */
    public function index()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard();
        }

        $data = [
            'title' => 'Lupa Password',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/forgot_password', $data);
    }

    /**
     * Kirim link reset password ke email
     */
    public function sendResetLink()
    {
        // Validasi email
        $rules = [
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Email tidak valid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');

        // Cek apakah email terdaftar
        $user = $this->userModel->where('email', $email)->first();

        // Untuk keamanan, selalu tampilkan pesan sukses meskipun email tidak terdaftar
        // Ini mencegah orang mencoba-coba email yang terdaftar
        if (!$user) {
            return redirect()->back()
                ->with('success', 'Jika email terdaftar, link reset password akan dikirim ke email Anda');
        }

        // Cek apakah user aktif
        if ($user['is_active'] != 1 || $user['deleted_at'] !== null) {
            return redirect()->back()
                ->with('success', 'Jika email terdaftar, link reset password akan dikirim ke email Anda');
        }

        // Generate token reset password
        $token = bin2hex(random_bytes(32));
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token berlaku 1 jam

        // Simpan token ke database (bisa buat tabel password_resets atau tambah kolom di users)
        // Untuk sementara, kita simpan di session
        session()->set('reset_token_' . $user['id'], [
            'token' => $token,
            'expiry' => $tokenExpiry
        ]);

        // Kirim email dengan link reset
        $resetLink = base_url('reset-password/' . $token . '/' . base64_encode($email));
        
        $emailSent = $this->sendResetEmail($email, $user['nama_lengkap'], $resetLink);

        if ($emailSent) {
            // Log aktivitas
            $this->logActivity($user['id'], 'forgot_password', 'users', 'User request reset password', [
                'email' => $email
            ]);

            return redirect()->back()
                ->with('success', 'Link reset password telah dikirim ke email Anda. Silakan cek email Anda.');
        } else {
            return redirect()->back()
                ->with('error', 'Gagal mengirim email. Silakan coba lagi nanti.');
        }
    }

    /**
     * Tampilkan halaman reset password
     */
    public function resetPassword($token, $encodedEmail)
    {
        $email = base64_decode($encodedEmail);

        // Validasi token
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->to('/login')
                ->with('error', 'Link reset password tidak valid');
        }

        // Cek token di session
        $storedToken = session()->get('reset_token_' . $user['id']);

        if (!$storedToken || $storedToken['token'] !== $token) {
            return redirect()->to('/login')
                ->with('error', 'Link reset password tidak valid');
        }

        // Cek apakah token sudah expired
        if (strtotime($storedToken['expiry']) < time()) {
            session()->remove('reset_token_' . $user['id']);
            return redirect()->to('/forgot-password')
                ->with('error', 'Link reset password sudah kadaluarsa. Silakan request ulang.');
        }

        $data = [
            'title' => 'Reset Password',
            'token' => $token,
            'email' => $email,
            'validation' => \Config\Services::validation()
        ];

        return view('auth/reset_password', $data);
    }

    /**
     * Proses update password baru
     */
    public function updatePassword()
    {
        // Validasi input
        $rules = [
            'token' => 'required',
            'email' => 'required|valid_email',
            'password' => [
                'rules' => 'required|min_length[6]|max_length[255]',
                'errors' => [
                    'required' => 'Password baru harus diisi',
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

        $token = $this->request->getPost('token');
        $email = $this->request->getPost('email');
        $newPassword = $this->request->getPost('password');

        // Validasi token
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->to('/login')
                ->with('error', 'User tidak ditemukan');
        }

        // Cek token di session
        $storedToken = session()->get('reset_token_' . $user['id']);

        if (!$storedToken || $storedToken['token'] !== $token) {
            return redirect()->to('/login')
                ->with('error', 'Token tidak valid');
        }

        // Cek expired
        if (strtotime($storedToken['expiry']) < time()) {
            session()->remove('reset_token_' . $user['id']);
            return redirect()->to('/forgot-password')
                ->with('error', 'Token sudah kadaluarsa');
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $updated = $this->userModel->update($user['id'], [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($updated) {
            // Hapus token dari session
            session()->remove('reset_token_' . $user['id']);

            // Log aktivitas
            $this->logActivity($user['id'], 'reset_password', 'users', 'User berhasil reset password', [
                'email' => $email
            ]);

            return redirect()->to('/login')
                ->with('success', 'Password berhasil diubah. Silakan login dengan password baru Anda.');
        } else {
            return redirect()->back()
                ->with('error', 'Gagal mengubah password. Silakan coba lagi.');
        }
    }

    /**
     * Kirim email reset password
     * NOTE: Ini contoh sederhana. Untuk production, gunakan library email yang proper
     */
    private function sendResetEmail($to, $name, $resetLink)
    {
        // Konfigurasi email
        $email = \Config\Services::email();

        $email->setFrom('noreply@yourdomain.com', 'Aplikasi Statistik');
        $email->setTo($to);
        $email->setSubject('Reset Password - Aplikasi Statistik');

        $message = "
        <html>
        <body>
            <h2>Reset Password</h2>
            <p>Halo {$name},</p>
            <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.</p>
            <p>Klik link di bawah untuk reset password Anda:</p>
            <p><a href='{$resetLink}' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
            <p>Link ini akan kadaluarsa dalam 1 jam.</p>
            <p>Jika Anda tidak melakukan permintaan ini, abaikan email ini.</p>
            <br>
            <p>Terima kasih,<br>Tim Aplikasi Statistik</p>
        </body>
        </html>
        ";

        $email->setMessage($message);

        // Untuk development, return true
        // Untuk production, uncomment baris berikut:
        // return $email->send();
        
        // Sementara return true untuk testing
        return true;
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