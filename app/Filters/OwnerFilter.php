<?php

/**
 * ============================================================================
 * OWNER FILTER
 * ============================================================================
 * 
 * Path: app/Filters/OwnerFilter.php
 * 
 * Deskripsi:
 * Filter untuk memastikan user adalah Owner workspace.
 * Redirect ke dashboard jika bukan owner.
 * 
 * Used by: Routes /owner/*
 * ============================================================================
 */

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Libraries\RoleManager;

class OwnerFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $roleManager = new RoleManager();

        // Superadmin can access owner pages
        if ($roleManager->isSuperadmin()) {
            return;
        }

        // Check if user is owner
        if (!$roleManager->isOwner()) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak. Anda bukan Owner workspace.');
        }

        // Check if user has application_id in session
        $applicationId = $session->get('application_id');
        if (!$applicationId) {
            return redirect()->to('/dashboard')->with('error', 'Workspace tidak ditemukan. Silakan pilih workspace.');
        }

        // User is owner, continue
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}