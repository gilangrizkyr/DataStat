<?php

/**
 * ============================================================================
 * APPLICATION MODEL
 * ============================================================================
 *
 * Path: app/Models/Owner/ApplicationModel.php
 *
 * Deskripsi:
 * Model untuk tabel applications (workspace/aplikasi owner).
 * Menangani CRUD aplikasi dengan soft delete support.
 *
 * Table: applications
 * Primary Key: id
 *
 * Fields:
 * - id, user_id, app_name, app_slug, bidang, description
 * - logo, color_theme, settings (JSON)
 * - is_active, created_at, updated_at, deleted_at
 *
 * Features:
 * - Soft delete support
 * - JSON casting untuk settings
 * - Validation rules
 * - Before insert/update callbacks
 *
 * Used by: Owner, Superadmin
 * ============================================================================
 */

namespace App\Models\Owner;

use CodeIgniter\Model;

class ApplicationModel extends Model
{
    protected $table            = 'applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'app_name',
        'app_slug',
        'bidang',
        'description',
        'logo',
        'color_theme',
        'settings',
        'is_active',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = false; // Manual timestamps
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_id'     => 'required|integer',
        'app_name'    => 'required|min_length[3]|max_length[255]',
        'app_slug'    => 'permit_empty|alpha_dash|max_length[255]',
        'bidang'      => 'required|min_length[3]|max_length[100]',
        'color_theme' => 'permit_empty|in_list[blue,green,red,purple,orange,teal,indigo,pink]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer'  => 'User ID harus berupa angka'
        ],
        'app_name' => [
            'required'   => 'Nama aplikasi harus diisi',
            'min_length' => 'Nama aplikasi minimal 3 karakter',
            'max_length' => 'Nama aplikasi maksimal 255 karakter'
        ],
        'bidang' => [
            'required'   => 'Bidang harus diisi',
            'min_length' => 'Bidang minimal 3 karakter'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug'];
    protected $beforeUpdate   = ['generateSlug'];

    // Cast fields
    protected $casts = [
        'settings'  => 'json',
        'is_active' => 'boolean'
    ];

    /**
     * Generate slug sebelum insert/update jika belum ada
     */
    protected function generateSlug(array $data)
    {
        if (isset($data['data']['app_name']) && empty($data['data']['app_slug'])) {
            $slug = url_title($data['data']['app_name'], '-', true);
            
            // Check unique slug
            $existingSlug = $this->where('app_slug', $slug)
                                 ->where('deleted_at', null)
                                 ->first();
            
            if ($existingSlug) {
                $slug = $slug . '-' . uniqid();
            }
            
            $data['data']['app_slug'] = $slug;
        }

        return $data;
    }

    /**
     * Get aplikasi by user
     */
    public function getByUser($userId, $withDeleted = false)
    {
        $builder = $this->where('user_id', $userId);
        
        if (!$withDeleted) {
            $builder->where('deleted_at', null);
        }
        
        return $builder->findAll();
    }

    /**
     * Get aplikasi by slug
     */
    public function getBySlug($slug)
    {
        return $this->where('app_slug', $slug)
                    ->where('deleted_at', null)
                    ->first();
    }

    /**
     * Get active aplikasi
     */
    public function getActive($userId = null)
    {
        $builder = $this->where('is_active', 1)
                        ->where('deleted_at', null);
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }
        
        return $builder->findAll();
    }

    /**
     * Get aplikasi dengan detail owner
     */
    public function getWithOwner($applicationId = null)
    {
        $builder = $this->select('applications.*, users.nama_lengkap as owner_name, users.email as owner_email')
                        ->join('users', 'users.id = applications.user_id')
                        ->where('applications.deleted_at', null);
        
        if ($applicationId) {
            $builder->where('applications.id', $applicationId);
            return $builder->first();
        }
        
        return $builder->findAll();
    }

    /**
     * Get aplikasi dengan statistik
     */
    public function getWithStats($applicationId)
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                a.*,
                u.nama_lengkap as owner_name,
                u.email as owner_email,
                (SELECT COUNT(*) FROM datasets WHERE application_id = a.id AND deleted_at IS NULL) as dataset_count,
                (SELECT COUNT(*) FROM statistic_configs WHERE application_id = a.id AND deleted_at IS NULL) as statistic_count,
                (SELECT COUNT(*) FROM dashboards WHERE application_id = a.id AND deleted_at IS NULL) as dashboard_count,
                (SELECT COUNT(*) FROM user_roles WHERE application_id = a.id) as member_count
            FROM applications a
            JOIN users u ON u.id = a.user_id
            WHERE a.id = ? AND a.deleted_at IS NULL
        ", [$applicationId]);
        
        return $query->getRowArray();
    }

    /**
     * Update settings (merge dengan existing)
     */
    public function updateSettings($applicationId, array $newSettings)
    {
        $application = $this->find($applicationId);
        
        if (!$application) {
            return false;
        }
        
        $currentSettings = is_array($application['settings']) 
            ? $application['settings'] 
            : json_decode($application['settings'] ?? '{}', true);
        
        $mergedSettings = array_merge($currentSettings, $newSettings);
        
        return $this->update($applicationId, [
            'settings' => json_encode($mergedSettings),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Activate/Deactivate aplikasi
     */
    public function toggleActive($applicationId)
    {
        $application = $this->find($applicationId);
        
        if (!$application) {
            return false;
        }
        
        $newStatus = $application['is_active'] == 1 ? 0 : 1;
        
        return $this->update($applicationId, [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Search aplikasi
     */
    public function search($keyword, $userId = null)
    {
        $builder = $this->select('applications.*, users.nama_lengkap as owner_name')
                        ->join('users', 'users.id = applications.user_id')
                        ->where('applications.deleted_at', null)
                        ->groupStart()
                            ->like('applications.app_name', $keyword)
                            ->orLike('applications.bidang', $keyword)
                            ->orLike('applications.description', $keyword)
                        ->groupEnd();
        
        if ($userId) {
            $builder->where('applications.user_id', $userId);
        }
        
        return $builder->findAll();
    }

    /**
     * Get total aplikasi count
     */
    public function getTotalCount($userId = null)
    {
        $builder = $this->where('deleted_at', null);
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }
        
        return $builder->countAllResults();
    }

    /**
     * Get aplikasi created in date range
     */
    public function getByDateRange($startDate, $endDate, $userId = null)
    {
        $builder = $this->where('DATE(created_at) >=', $startDate)
                        ->where('DATE(created_at) <=', $endDate)
                        ->where('deleted_at', null);
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }
        
        return $builder->findAll();
    }
}