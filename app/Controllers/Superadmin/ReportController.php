<?php

/**
 * ============================================================================
 * SUPERADMIN REPORT CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Superadmin/ReportController.php
 * 
 * Deskripsi:
 * Controller untuk generate dan view berbagai reports sistem.
 * 
 * Fitur:
 * - User report
 * - Application report
 * - Activity report
 * - System usage report
 * - Export reports (PDF, Excel)
 * 
 * Role: Superadmin
 * ============================================================================
 */

namespace App\Controllers\Superadmin;

use App\Controllers\BaseController;
use App\Models\Superadmin\SystemReportModel;

class ReportController extends BaseController
{
    protected $reportModel;

    public function __construct()
    {
        $this->reportModel = new SystemReportModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'System Reports'
        ];

        return view('superadmin/reports/index', $data);
    }

    public function userReport()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'User Report'
        ];

        return view('superadmin/reports/user_report', $data);
    }

    public function applicationReport()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Application Report'
        ];

        return view('superadmin/reports/application_report', $data);
    }
}