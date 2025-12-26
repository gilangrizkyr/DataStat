<?php

namespace App\Controllers;

/**
 * ============================================================================
 * HOME CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Home.php
 * 
 * Deskripsi:
 * Controller untuk halaman utama/landing.
 * Redirect ke login jika belum login, atau ke dashboard jika sudah login.
 * ============================================================================
 */

class Home extends BaseController
{
    /**
     * Index - Landing page
     * Redirect based on authentication status
     */
    public function index()
    {
        // Cek apakah user sudah login
        if (session()->get('logged_in')) {
            // Sudah login, redirect ke dashboard sesuai role
            $role = session()->get('role_name');
            
            switch($role) {
                case 'superadmin':
                    return redirect()->to(base_url('superadmin/dashboard'));
                    
                case 'owner':
                    return redirect()->to(base_url('owner/dashboard'));
                    
                case 'viewer':
                    return redirect()->to(base_url('viewer/dashboard'));
                    
                default:
                    // Role tidak valid, logout dan redirect ke login
                    session()->destroy();
                    return redirect()->to(base_url('login'))->with('error', 'Role tidak valid');
            }
        }
        
        // Belum login, redirect ke login
        return redirect()->to(base_url('login'));
    }
}