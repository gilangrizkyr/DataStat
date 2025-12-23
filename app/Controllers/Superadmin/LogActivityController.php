<?php

/**
 * ============================================================================
 * SUPERADMIN LOG ACTIVITY CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Superadmin/LogActivityController.php
 * 
 * Deskripsi:
 * Controller untuk view dan manage activity logs seluruh sistem.
 * 
 * Fitur:
 * - List semua activity logs dengan filter
 * - View detail log
 * - Export logs
 * - Clear old logs
 * 
 * Role: Superadmin
 * ============================================================================
 */

namespace App\Controllers\Superadmin;

use App\Controllers\BaseController;
use App\Models\Superadmin\LogActivityModel;

class LogActivityController extends BaseController
{
    protected $logModel;

    public function __construct()
    {
        $this->logModel = new LogActivityModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/login');
        }

        $activityType = $this->request->getGet('activity_type');
        $module = $this->request->getGet('module');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $perPage = 50;

        $builder = $this->logModel
            ->select('log_activities.*, users.nama_lengkap, applications.app_name')
            ->join('users', 'users.id = log_activities.user_id', 'left')
            ->join('applications', 'applications.id = log_activities.application_id', 'left');

        if ($activityType) {
            $builder->where('log_activities.activity_type', $activityType);
        }

        if ($module) {
            $builder->where('log_activities.module', $module);
        }

        if ($dateFrom) {
            $builder->where('DATE(log_activities.created_at) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(log_activities.created_at) <=', $dateTo);
        }

        $logs = $builder->orderBy('log_activities.created_at', 'DESC')
            ->paginate($perPage);

        $data = [
            'title' => 'Activity Logs',
            'logs' => $logs,
            'pager' => $this->logModel->pager,
            'activity_type' => $activityType,
            'module' => $module,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];

        return view('superadmin/logs/index', $data);
    }
}