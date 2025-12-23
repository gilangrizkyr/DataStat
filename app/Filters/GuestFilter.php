<?php

/**
 * ============================================================================
 * GUEST FILTER
 * ============================================================================
 * 
 * Path: app/Filters/GuestFilter.php
 * 
 * Deskripsi:
 * Filter untuk memastikan user BELUM login.
 * Redirect ke dashboard jika sudah login.
 * 
 * Used by: Routes /login, /register (halaman public)
 * ============================================================================
 */

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class GuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // If user is already logged in, redirect to dashboard
        if ($session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        // User is not logged in, continue to login/register page
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}