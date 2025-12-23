<?php

/**
 * ============================================================================
 * SUPERADMIN SYSTEM SETTING CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Superadmin/SystemSettingController.php
 * 
 * Deskripsi:
 * Controller untuk pengaturan sistem global.
 * 
 * Fitur:
 * - View system settings
 * - Update system config
 * - Maintenance mode
 * - System limits
 * 
 * Role: Superadmin
 * ============================================================================
 */

namespace App\Controllers\Superadmin;

use App\Controllers\BaseController;

class SystemSettingController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'System Settings',
            'validation' => \Config\Services::validation()
        ];

        return view('superadmin/settings/index', $data);
    }
}