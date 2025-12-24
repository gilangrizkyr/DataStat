<?php

/**
 * ============================================================================
 * SYSTEM REPORT MODEL
 * ============================================================================
 * 
 * Path: app/Models/Superadmin/SystemReportModel.php
 * 
 * Deskripsi:
 * Model untuk generate system reports dan analytics.
 * Tidak punya table sendiri, menggunakan query ke multiple tables.
 * 
 * Used by: Superadmin (untuk reporting & analytics)
 * ============================================================================
 */

namespace App\Models\Superadmin;

use CodeIgniter\Model;

class SystemReportModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get user growth report
     */
    public function getUserGrowthReport($startDate, $endDate)
    {
        $query = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_users,
                SUM(COUNT(*)) OVER (ORDER BY DATE(created_at)) as cumulative_users
            FROM users
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND deleted_at IS NULL
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ";

        return $this->db->query($query, [$startDate, $endDate])->getResultArray();
    }

    /**
     * Get application growth report
     */
    public function getApplicationGrowthReport($startDate, $endDate)
    {
        $query = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_applications,
                SUM(COUNT(*)) OVER (ORDER BY DATE(created_at)) as cumulative_applications
            FROM applications
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND deleted_at IS NULL
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ";

        return $this->db->query($query, [$startDate, $endDate])->getResultArray();
    }

    /**
     * Get user activity report
     */
    public function getUserActivityReport($userId, $startDate, $endDate)
    {
        $query = "
            SELECT 
                DATE(created_at) as date,
                activity_type,
                module,
                COUNT(*) as activity_count
            FROM log_activities
            WHERE user_id = ?
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at), activity_type, module
            ORDER BY date DESC, activity_count DESC
        ";

        return $this->db->query($query, [$userId, $startDate, $endDate])->getResultArray();
    }

    /**
     * Get application usage report
     */
    public function getApplicationUsageReport()
    {
        $query = "
            SELECT 
                a.id,
                a.app_name,
                a.bidang,
                u.nama_lengkap as owner_name,
                a.created_at,
                (SELECT COUNT(*) FROM datasets WHERE application_id = a.id AND deleted_at IS NULL) as dataset_count,
                (SELECT COUNT(*) FROM statistic_configs WHERE application_id = a.id AND deleted_at IS NULL) as statistic_count,
                (SELECT COUNT(*) FROM dashboards WHERE application_id = a.id AND deleted_at IS NULL) as dashboard_count,
                (SELECT COUNT(*) FROM user_roles WHERE application_id = a.id) as member_count,
                (SELECT COUNT(*) FROM log_activities WHERE application_id = a.id AND DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as activity_30days
            FROM applications a
            JOIN users u ON u.id = a.user_id
            WHERE a.deleted_at IS NULL
            ORDER BY activity_30days DESC, dataset_count DESC
        ";

        return $this->db->query($query)->getResultArray();
    }

    /**
     * Get dataset statistics report
     */
    public function getDatasetStatisticsReport()
    {
        $query = "
            SELECT 
                a.app_name,
                d.dataset_name,
                d.total_rows,
                d.total_columns,
                d.file_size,
                d.created_at,
                u.nama_lengkap as uploaded_by,
                (SELECT COUNT(*) FROM statistic_configs WHERE dataset_id = d.id AND deleted_at IS NULL) as statistic_count
            FROM datasets d
            JOIN applications a ON a.id = d.application_id
            JOIN users u ON u.id = d.uploaded_by
            WHERE d.deleted_at IS NULL
            ORDER BY d.total_rows DESC
        ";

        return $this->db->query($query)->getResultArray();
    }

    /**
     * Get most active users
     */
    public function getMostActiveUsers($days = 30, $limit = 10)
    {
        $query = "
            SELECT 
                u.id,
                u.nama_lengkap,
                u.email,
                u.bidang,
                COUNT(la.id) as activity_count,
                MAX(la.created_at) as last_activity
            FROM users u
            LEFT JOIN log_activities la ON la.user_id = u.id 
                AND DATE(la.created_at) >= DATE_SUB(NOW(), INTERVAL ? DAY)
            WHERE u.deleted_at IS NULL
            GROUP BY u.id, u.nama_lengkap, u.email, u.bidang
            ORDER BY activity_count DESC
            LIMIT ?
        ";

        return $this->db->query($query, [$days, $limit])->getResultArray();
    }

    /**
     * Get popular statistics
     */
    public function getPopularStatistics($limit = 20)
    {
        $query = "
            SELECT 
                sc.id,
                sc.stat_name,
                a.app_name,
                d.dataset_name,
                sc.metric_type,
                sc.visualization_type,
                (SELECT COUNT(*) FROM dashboard_widgets WHERE statistic_config_id = sc.id) as used_in_dashboards,
                (SELECT COUNT(*) FROM log_activities 
                 WHERE module = 'statistics' 
                 AND activity_type IN ('view', 'export')
                 AND CAST(JSON_EXTRACT(request_data, '$.statistic_id') AS UNSIGNED) = sc.id
                 AND DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as views_30days
            FROM statistic_configs sc
            JOIN applications a ON a.id = sc.application_id
            JOIN datasets d ON d.id = sc.dataset_id
            WHERE sc.deleted_at IS NULL
            ORDER BY views_30days DESC, used_in_dashboards DESC
            LIMIT ?
        ";

        return $this->db->query($query, [$limit])->getResultArray();
    }

    /**
     * Get system overview
     */
    public function getSystemOverview()
    {
        $overview = [];

        // Total users
        $overview['total_users'] = $this->db->table('users')
            ->where('deleted_at', null)
            ->countAllResults();

        $overview['active_users'] = $this->db->table('users')
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->countAllResults();

        // Total applications
        $overview['total_applications'] = $this->db->table('applications')
            ->where('deleted_at', null)
            ->countAllResults();

        $overview['active_applications'] = $this->db->table('applications')
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->countAllResults();

        // Total datasets
        $overview['total_datasets'] = $this->db->table('datasets')
            ->where('deleted_at', null)
            ->countAllResults();

        // Total data rows
        $result = $this->db->table('datasets')
            ->select('SUM(total_rows) as total')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();
        $overview['total_data_rows'] = $result['total'] ?? 0;

        // Total statistics
        $overview['total_statistics'] = $this->db->table('statistic_configs')
            ->where('deleted_at', null)
            ->countAllResults();

        // Total dashboards
        $overview['total_dashboards'] = $this->db->table('dashboards')
            ->where('deleted_at', null)
            ->countAllResults();

        // New users this month
        $overview['new_users_month'] = $this->db->table('users')
            ->where('DATE(created_at) >=', date('Y-m-01'))
            ->where('deleted_at', null)
            ->countAllResults();

        // Activities today
        $overview['activities_today'] = $this->db->table('log_activities')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();

        return $overview;
    }

    /**
     * Get login statistics
     */
    public function getLoginStatistics($days = 30)
    {
        $query = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as login_count,
                COUNT(DISTINCT user_id) as unique_users
            FROM log_activities
            WHERE activity_type = 'login'
            AND DATE(created_at) >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ";

        return $this->db->query($query, [$days])->getResultArray();
    }

    /**
     * Get role distribution
     */
    public function getRoleDistribution()
    {
        $query = "
            SELECT 
                r.role_label,
                r.role_name,
                COUNT(ur.id) as user_count,
                COUNT(DISTINCT ur.application_id) as application_count
            FROM roles r
            LEFT JOIN user_roles ur ON ur.role_id = r.id
            GROUP BY r.id, r.role_label, r.role_name
            ORDER BY user_count DESC
        ";

        return $this->db->query($query)->getResultArray();
    }

    /**
     * Get storage usage
     */
    public function getStorageUsage()
    {
        $query = "
            SELECT 
                a.app_name,
                a.bidang,
                COUNT(d.id) as dataset_count,
                SUM(d.file_size) as total_storage,
                SUM(d.total_rows) as total_rows
            FROM applications a
            LEFT JOIN datasets d ON d.application_id = a.id AND d.deleted_at IS NULL
            WHERE a.deleted_at IS NULL
            GROUP BY a.id, a.app_name, a.bidang
            ORDER BY total_storage DESC
        ";

        return $this->db->query($query)->getResultArray();
    }

    /**
     * Get error logs (aktivitas dengan status failed/error)
     */
    public function getErrorLogs($days = 7, $limit = 50)
    {
        $query = "
            SELECT 
                la.*,
                u.nama_lengkap,
                u.email,
                a.app_name
            FROM log_activities la
            LEFT JOIN users u ON u.id = la.user_id
            LEFT JOIN applications a ON a.id = la.application_id
            WHERE DATE(la.created_at) >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND (la.activity_type LIKE '%fail%' 
                 OR la.activity_type LIKE '%error%'
                 OR la.description LIKE '%error%'
                 OR la.description LIKE '%fail%')
            ORDER BY la.created_at DESC
            LIMIT ?
        ";

        return $this->db->query($query, [$days, $limit])->getResultArray();
    }
}