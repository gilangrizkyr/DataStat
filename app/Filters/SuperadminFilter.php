<?php

/**
 * ============================================================================
 * SUPERADMIN FILTER
 * ============================================================================
 * 
 * Path: app/Filters/SuperadminFilter.php
 * 
 * Deskripsi:
 * Filter untuk memastikan user adalah Superadmin.
 * Redirect ke dashboard jika bukan superadmin.
 * 
 * Used by: Routes /superadmin/*
 * ============================================================================
 */

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Libraries\RoleManager;

class SuperadminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Check if user is superadmin
        $roleManager = new RoleManager();
        if (!$roleManager->isSuperadmin()) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak. Anda bukan Superadmin.');
        }

        // User is superadmin, continue
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}