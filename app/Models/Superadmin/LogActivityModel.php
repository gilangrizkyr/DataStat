<?php

/**
 * ============================================================================
 * LOG ACTIVITY MODEL
 * ============================================================================
 * 
 * Path: app/Models/Superadmin/LogActivityModel.php
 * 
 * Deskripsi:
 * Model untuk audit trail / activity logging di sistem.
 * Mencatat semua aktivitas penting user.
 * 
 * Table: log_activities
 * 
 * Fields:
 * - id (PK)
 * - user_id (FK → users)
 * - application_id (FK → applications, nullable)
 * - activity_type - login, logout, create, update, delete, view, export, dll
 * - module - users, applications, datasets, statistics, dashboards, dll
 * - description - Deskripsi aktivitas
 * - ip_address - IP address user
 * - user_agent - Browser user agent
 * - request_data (JSON) - Data request
 * - response_data (JSON) - Data response
 * - created_at
 * 
 * Relations:
 * - belongsTo: users, applications
 * 
 * Used by: Semua roles (untuk logging)
 * ============================================================================
 */

namespace App\Models\Superadmin;

use CodeIgniter\Model;

class LogActivityModel extends Model
{
    protected $table            = 'log_activities';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'application_id',
        'activity_type',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'request_data',
        'response_data',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    protected $validationRules = [
        'user_id'       => 'permit_empty|integer',
        'activity_type' => 'required|max_length[50]',
        'module'        => 'required|max_length[50]'
    ];

    /**
     * Log aktivitas (static method untuk mudah dipanggil)
     */
    public static function log($data)
    {
        $model = new self();
        
        $logData = [
            'user_id' => $data['user_id'] ?? null,
            'application_id' => $data['application_id'] ?? null,
            'activity_type' => $data['activity_type'],
            'module' => $data['module'],
            'description' => $data['description'] ?? '',
            'ip_address' => $data['ip_address'] ?? '',
            'user_agent' => $data['user_agent'] ?? '',
            'request_data' => isset($data['request_data']) ? json_encode($data['request_data']) : null,
            'response_data' => isset($data['response_data']) ? json_encode($data['response_data']) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $model->insert($logData);
    }

    /**
     * Get logs dengan user dan application info
     */
    public function getWithDetails($limit = 100, $offset = 0)
    {
        return $this->select('log_activities.*, 
                             users.nama_lengkap, users.email,
                             applications.app_name')
                    ->join('users', 'users.id = log_activities.user_id', 'left')
                    ->join('applications', 'applications.id = log_activities.application_id', 'left')
                    ->orderBy('log_activities.created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }

    /**
     * Get logs by user
     */
    public function getByUser($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get logs by application
     */
    public function getByApplication($applicationId, $limit = 50)
    {
        return $this->where('application_id', $applicationId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get logs by activity type
     */
    public function getByActivityType($activityType, $limit = 50)
    {
        return $this->where('activity_type', $activityType)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get logs by module
     */
    public function getByModule($module, $limit = 50)
    {
        return $this->where('module', $module)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get logs by date range
     */
    public function getByDateRange($startDate, $endDate, $limit = 100)
    {
        return $this->where('DATE(created_at) >=', $startDate)
                    ->where('DATE(created_at) <=', $endDate)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get recent activities (global)
     */
    public function getRecentActivities($limit = 20)
    {
        return $this->select('log_activities.*, 
                             users.nama_lengkap, 
                             applications.app_name')
                    ->join('users', 'users.id = log_activities.user_id', 'left')
                    ->join('applications', 'applications.id = log_activities.application_id', 'left')
                    ->orderBy('log_activities.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Search logs
     */
    public function searchLogs($keyword, $limit = 50)
    {
        return $this->select('log_activities.*, 
                             users.nama_lengkap, 
                             applications.app_name')
                    ->join('users', 'users.id = log_activities.user_id', 'left')
                    ->join('applications', 'applications.id = log_activities.application_id', 'left')
                    ->groupStart()
                        ->like('log_activities.description', $keyword)
                        ->orLike('log_activities.activity_type', $keyword)
                        ->orLike('log_activities.module', $keyword)
                        ->orLike('users.nama_lengkap', $keyword)
                    ->groupEnd()
                    ->orderBy('log_activities.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get activity statistics
     */
    public function getActivityStatistics($days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return [
            'total' => $this->where('DATE(created_at) >=', $startDate)->countAllResults(),
            'by_type' => $this->select('activity_type, COUNT(*) as count')
                              ->where('DATE(created_at) >=', $startDate)
                              ->groupBy('activity_type')
                              ->findAll(),
            'by_module' => $this->select('module, COUNT(*) as count')
                                ->where('DATE(created_at) >=', $startDate)
                                ->groupBy('module')
                                ->findAll(),
            'by_day' => $this->select('DATE(created_at) as date, COUNT(*) as count')
                             ->where('DATE(created_at) >=', $startDate)
                             ->groupBy('DATE(created_at)')
                             ->orderBy('date', 'ASC')
                             ->findAll()
        ];
    }

    /**
     * Get login activities
     */
    public function getLoginActivities($limit = 50)
    {
        return $this->select('log_activities.*, users.nama_lengkap, users.email')
                    ->join('users', 'users.id = log_activities.user_id', 'left')
                    ->where('log_activities.activity_type', 'login')
                    ->orderBy('log_activities.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get failed login attempts
     */
    public function getFailedLogins($limit = 50)
    {
        return $this->where('activity_type', 'failed_login')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Clean old logs (untuk maintenance)
     */
    public function cleanOldLogs($days = 90)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->where('DATE(created_at) <', $cutoffDate)->delete();
    }

    /**
     * Export logs to array (untuk CSV/Excel)
     */
    public function exportLogs($filters = [])
    {
        $builder = $this->select('log_activities.*, 
                                 users.nama_lengkap, users.email,
                                 applications.app_name')
                        ->join('users', 'users.id = log_activities.user_id', 'left')
                        ->join('applications', 'applications.id = log_activities.application_id', 'left');

        if (isset($filters['start_date'])) {
            $builder->where('DATE(log_activities.created_at) >=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $builder->where('DATE(log_activities.created_at) <=', $filters['end_date']);
        }

        if (isset($filters['activity_type'])) {
            $builder->where('log_activities.activity_type', $filters['activity_type']);
        }

        if (isset($filters['module'])) {
            $builder->where('log_activities.module', $filters['module']);
        }

        if (isset($filters['user_id'])) {
            $builder->where('log_activities.user_id', $filters['user_id']);
        }

        return $builder->orderBy('log_activities.created_at', 'DESC')->findAll();
    }
}