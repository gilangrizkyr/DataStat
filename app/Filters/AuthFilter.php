<?php

/**
 * ============================================================================
 * AUTH FILTER
 * ============================================================================
 * 
 * Path: app/Filters/AuthFilter.php
 * 
 * Deskripsi:
 * Filter untuk memastikan user sudah login.
 * Redirect ke halaman login jika belum login.
 * 
 * Used by: Semua routes yang memerlukan authentication
 * ============================================================================
 */

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            // Redirect to login page
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        // User is authenticated, continue
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}