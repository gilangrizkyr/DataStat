<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class LogoutController extends BaseController
{
    /**
     * Proses logout user
     */
    public function index()
    {
        // Log aktivitas sebelum logout
        $this->logActivity('logout', 'users', 'User berhasil logout');

        // Hapus semua session
        $sessionData = [
            'user_id',
            'email',
            'nama_lengkap',
            'bidang',
            'avatar',
            'role_id',
            'role_name',
            'role_label',
            'application_id',
            'app_name',
            'app_slug',
            'logged_in'
        ];

        session()->remove($sessionData);
        
        // Destroy session
        session()->destroy();

        // Hapus remember me cookie jika ada
        $this->deleteRememberMeCookie();

        // Redirect ke halaman login dengan pesan
        return redirect()->to('/login')
            ->with('success', 'Anda berhasil logout');
    }

    /**
     * Hapus remember me cookie
     */
    private function deleteRememberMeCookie()
    {
        helper('cookie');  // ✅ Load cookie helper
        
        // Hapus cookie remember_token
        delete_cookie('remember_token');
        
        // Hapus cookie remember_user
        delete_cookie('remember_user');
    }

    /**
     * Log aktivitas user
     */
    private function logActivity($activityType, $module, $description, $data = [])
    {
        // Pastikan user_id masih ada di session
        $userId = session()->get('user_id');
        
        if (!$userId) {
            return;
        }

        $logData = [
            'user_id' => $userId,
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