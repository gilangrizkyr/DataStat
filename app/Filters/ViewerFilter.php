<?php

/**
 * ============================================================================
 * VIEWER FILTER
 * ============================================================================
 * 
 * Path: app/Filters/ViewerFilter.php
 * 
 * Deskripsi:
 * Filter untuk memastikan user adalah Viewer (atau Owner/Superadmin).
 * Viewer memiliki akses read-only ke dashboard dan statistics.
 * 
 * Used by: Routes /viewer/*
 * ============================================================================
 */

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Libraries\RoleManager;

class ViewerFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $roleManager = new RoleManager();

        // Superadmin can access viewer pages
        if ($roleManager->isSuperadmin()) {
            return;
        }

        // Owner can access viewer pages
        if ($roleManager->isOwner()) {
            return;
        }

        // Check if user is viewer
        if (!$roleManager->isViewer()) {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda tidak memiliki akses ke halaman ini.');
        }

        // Viewer can access without workspace check
        // No need to check application_id here
        
        // User is viewer (or owner/superadmin), continue
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}