<?php

/**
 * ============================================================================
 * DASHBOARD MODEL
 * ============================================================================
 * 
 * Path: app/Models/Owner/DashboardModel.php
 * 
 * Deskripsi:
 * Model untuk mengelola dashboard workspace.
 * Dashboard berisi kumpulan widgets yang menampilkan statistik.
 * 
 * Table: dashboards
 * 
 * Fields:
 * - id (PK)
 * - application_id (FK) - Workspace
 * - dashboard_name - Nama dashboard
 * - dashboard_slug - Unique slug
 * - description - Deskripsi
 * - layout_config (JSON) - Konfigurasi layout
 * - is_default - Dashboard default atau tidak
 * - is_public - Bisa diakses public atau tidak
 * - access_token - Token untuk public access
 * - created_by (FK ke users)
 * - created_at, updated_at, deleted_at
 * 
 * Relations:
 * - belongsTo: applications, users (creator)
 * - hasMany: dashboard_widgets
 * 
 * Used by: Owner, Viewer
 * ============================================================================
 */

namespace App\Models\Owner;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table            = 'dashboards';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'application_id',
        'dashboard_name',
        'dashboard_slug',
        'description',
        'layout_config',
        'is_default',
        'is_public',
        'access_token',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'application_id'  => 'required|integer',
        'dashboard_name'  => 'required|min_length[3]|max_length[255]',
        'dashboard_slug'  => 'required|alpha_dash|max_length[255]',
        'is_default'      => 'permit_empty|in_list[0,1]',
        'is_public'       => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'application_id' => [
            'required' => 'Application ID harus diisi'
        ],
        'dashboard_name' => [
            'required' => 'Nama dashboard harus diisi',
            'min_length' => 'Nama dashboard minimal 3 karakter'
        ]
    ];
    
    protected $skipValidation = false;
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug', 'generateAccessToken'];

    /**
     * Generate slug
     */
    protected function generateSlug(array $data)
    {
        if (!isset($data['data']['dashboard_slug']) && isset($data['data']['dashboard_name'])) {
            $slug = url_title($data['data']['dashboard_name'], '-', true);
            $data['data']['dashboard_slug'] = $slug . '-' . uniqid();
        }
        return $data;
    }

    /**
     * Generate access token untuk public dashboard
     */
    protected function generateAccessToken(array $data)
    {
        if (!isset($data['data']['access_token'])) {
            $data['data']['access_token'] = bin2hex(random_bytes(16));
        }
        return $data;
    }

    /**
     * Get dashboard dengan widget count
     */
    public function getWithWidgetCount($applicationId)
    {
        return $this->select('dashboards.*, 
                             users.nama_lengkap as creator_name,
                             (SELECT COUNT(*) FROM dashboard_widgets WHERE dashboard_id = dashboards.id) as widget_count')
                    ->join('users', 'users.id = dashboards.created_by')
                    ->where('dashboards.application_id', $applicationId)
                    ->where('dashboards.deleted_at', null)
                    ->findAll();
    }

    /**
     * Get default dashboard
     */
    public function getDefault($applicationId)
    {
        return $this->where('application_id', $applicationId)
                    ->where('is_default', 1)
                    ->where('deleted_at', null)
                    ->first();
    }

    /**
     * Get by slug
     */
    public function getBySlug($slug, $applicationId)
    {
        return $this->where('dashboard_slug', $slug)
                    ->where('application_id', $applicationId)
                    ->where('deleted_at', null)
                    ->first();
    }

    /**
     * Get by access token (public)
     */
    public function getByAccessToken($token)
    {
        return $this->where('access_token', $token)
                    ->where('is_public', 1)
                    ->where('deleted_at', null)
                    ->first();
    }

    /**
     * Set as default (unset others)
     */
    public function setAsDefault($dashboardId, $applicationId)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Unset semua default
        $this->where('application_id', $applicationId)
             ->set(['is_default' => 0])
             ->update();

        // Set yang dipilih
        $this->update($dashboardId, ['is_default' => 1]);

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Toggle public access
     */
    public function togglePublic($dashboardId)
    {
        $dashboard = $this->find($dashboardId);
        if (!$dashboard) return false;

        $newStatus = $dashboard['is_public'] == 1 ? 0 : 1;
        return $this->update($dashboardId, ['is_public' => $newStatus]);
    }

    /**
     * Regenerate access token
     */
    public function regenerateAccessToken($dashboardId)
    {
        $newToken = bin2hex(random_bytes(16));
        return $this->update($dashboardId, ['access_token' => $newToken]);
    }
}