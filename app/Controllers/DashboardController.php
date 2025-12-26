<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * Common Dashboard - Smart redirect based on role
     * 
     * This is a common endpoint that automatically redirects users
     * to their role-specific dashboard.
     */
    public function index()
    {
        // Get user role from session
        $role = session()->get('role_name');

        // Redirect based on role
        switch ($role) {
            case 'superadmin':
                return redirect()->to('/superadmin/dashboard');
            
            case 'owner':
                return redirect()->to('/owner/dashboard');
            
            case 'viewer':
                return redirect()->to('/viewer/dashboard');
            
            default:
                // If no role or invalid role, logout and redirect to login
                session()->destroy();
                return redirect()->to('/login')->with('error', 'Role tidak valid. Silakan login kembali.');
        }
    }
}