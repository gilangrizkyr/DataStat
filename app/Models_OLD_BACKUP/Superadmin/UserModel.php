<?php

/**
 * ============================================================================
 * USER MODEL
 * ============================================================================
 * 
 * Path: app/Models/Superadmin/UserModel.php
 * 
 * Deskripsi:
 * Model untuk mengelola data user di sistem.
 * Digunakan oleh Superadmin untuk manage semua user, dan Owner untuk invite user.
 * 
 * Table: users
 * 
 * Fields:
 * - id (PK)
 * - nama_lengkap - Nama lengkap user
 * - email - Email (unique)
 * - password - Hashed password (bcrypt)
 * - bidang - Bidang/departemen user
 * - avatar - Path ke avatar image
 * - is_active - Status aktif (1/0)
 * - last_login - Timestamp login terakhir
 * - created_at, updated_at, deleted_at (soft delete)
 * 
 * Relations:
 * - hasMany: user_roles, applications (as owner), datasets (as uploader)
 * 
 * Used by: Superadmin, Owner (untuk user management)
 * ============================================================================
 */

namespace App\Models\Superadmin;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'nama_lengkap',
        'email',
        'password',
        'bidang',
        'avatar',
        'is_active',
        'last_login',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'nama_lengkap' => 'required|min_length[3]|max_length[255]',
        'email'        => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password'     => 'required|min_length[6]',
        'bidang'       => 'permit_empty|max_length[100]',
        'is_active'    => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'nama_lengkap' => [
            'required' => 'Nama lengkap harus diisi',
            'min_length' => 'Nama lengkap minimal 3 karakter',
            'max_length' => 'Nama lengkap maksimal 255 karakter'
        ],
        'email' => [
            'required' => 'Email harus diisi',
            'valid_email' => 'Format email tidak valid',
            'is_unique' => 'Email sudah terdaftar'
        ],
        'password' => [
            'required' => 'Password harus diisi',
            'min_length' => 'Password minimal 6 karakter'
        ]
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    /**
     * Hash password sebelum insert/update
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            // Jangan hash jika sudah dalam format bcrypt
            if (strpos($data['data']['password'], '$2y$') !== 0) {
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            }
        }
        return $data;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)
                    ->where('deleted_at', null)
                    ->first();
    }

    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        return $this->where('is_active', 1)
                    ->where('deleted_at', null)
                    ->findAll();
    }

    /**
     * Get users dengan role count
     */
    public function getWithRoleCount()
    {
        return $this->select('users.*, 
                             (SELECT COUNT(*) FROM user_roles WHERE user_id = users.id) as role_count')
                    ->where('users.deleted_at', null)
                    ->findAll();
    }

    /**
     * Get users dengan application count
     */
    public function getWithApplicationCount()
    {
        return $this->select('users.*, 
                             (SELECT COUNT(*) FROM applications WHERE user_id = users.id AND deleted_at IS NULL) as app_count')
                    ->where('users.deleted_at', null)
                    ->findAll();
    }

    /**
     * Search users
     */
    public function searchUsers($keyword)
    {
        return $this->groupStart()
                    ->like('nama_lengkap', $keyword)
                    ->orLike('email', $keyword)
                    ->orLike('bidang', $keyword)
                    ->groupEnd()
                    ->where('deleted_at', null)
                    ->findAll();
    }

    /**
     * Update last login
     */
    public function updateLastLogin($userId)
    {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive($userId)
    {
        $user = $this->find($userId);
        if (!$user) return false;

        $newStatus = $user['is_active'] == 1 ? 0 : 1;
        return $this->update($userId, ['is_active' => $newStatus]);
    }

    /**
     * Reset password
     */
    public function resetPassword($userId, $newPassword)
    {
        return $this->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Update avatar
     */
    public function updateAvatar($userId, $avatarPath)
    {
        return $this->update($userId, ['avatar' => $avatarPath]);
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar($userId)
    {
        $user = $this->find($userId);
        if ($user && $user['avatar']) {
            // Delete file
            $filePath = FCPATH . $user['avatar'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            // Update database
            return $this->update($userId, ['avatar' => null]);
        }
        return false;
    }

    /**
     * Verify password
     */
    public function verifyPassword($userId, $password)
    {
        $user = $this->find($userId);
        if (!$user) return false;

        return password_verify($password, $user['password']);
    }

    /**
     * Get new users (last N days)
     */
    public function getNewUsers($days = 7)
    {
        return $this->where('DATE(created_at) >=', date('Y-m-d', strtotime("-{$days} days")))
                    ->where('deleted_at', null)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics()
    {
        $stats = [
            'total' => $this->where('deleted_at', null)->countAllResults(),
            'active' => $this->where('is_active', 1)->where('deleted_at', null)->countAllResults(),
            'inactive' => $this->where('is_active', 0)->where('deleted_at', null)->countAllResults(),
            'new_this_week' => $this->where('DATE(created_at) >=', date('Y-m-d', strtotime('-7 days')))
                                    ->where('deleted_at', null)
                                    ->countAllResults(),
            'new_this_month' => $this->where('DATE(created_at) >=', date('Y-m-d', strtotime('-30 days')))
                                     ->where('deleted_at', null)
                                     ->countAllResults()
        ];

        return $stats;
    }
}