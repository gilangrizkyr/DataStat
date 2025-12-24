<?php

/**
 * ============================================================================
 * USER ROLE MODEL
 * ============================================================================
 * 
 * Path: app/Models/Superadmin/UserRoleModel.php
 * 
 * Deskripsi:
 * Model untuk mapping user ke role di aplikasi/workspace tertentu.
 * Menghubungkan users, roles, dan applications.
 * 
 * Table: user_roles
 * 
 * Fields:
 * - id (PK)
 * - user_id (FK → users)
 * - role_id (FK → roles)
 * - application_id (FK → applications, NULL untuk superadmin)
 * - is_active - Status aktif
 * - created_at, updated_at
 * 
 * Relations:
 * - belongsTo: users, roles, applications
 * 
 * Notes:
 * - Superadmin: application_id = NULL (akses global)
 * - Owner/Viewer: application_id = workspace_id (akses terbatas)
 * 
 * Used by: Superadmin, Owner
 * ============================================================================
 */

namespace App\Models\Superadmin;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table            = 'user_roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'role_id',
        'application_id',
        'is_active',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'user_id'   => 'required|integer',
        'role_id'   => 'required|integer',
        'is_active' => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID harus berupa angka'
        ],
        'role_id' => [
            'required' => 'Role ID harus diisi',
            'integer' => 'Role ID harus berupa angka'
        ]
    ];

    /**
     * Get roles untuk user
     */
    public function getUserRoles($userId)
    {
        return $this->select('user_roles.*, roles.role_name, roles.role_label, applications.app_name')
                    ->join('roles', 'roles.id = user_roles.role_id')
                    ->join('applications', 'applications.id = user_roles.application_id', 'left')
                    ->where('user_roles.user_id', $userId)
                    ->findAll();
    }

    /**
     * Get user role di aplikasi tertentu
     */
    public function getUserRoleInApp($userId, $applicationId)
    {
        return $this->select('user_roles.*, roles.role_name, roles.role_label')
                    ->join('roles', 'roles.id = user_roles.role_id')
                    ->where('user_roles.user_id', $userId)
                    ->where('user_roles.application_id', $applicationId)
                    ->first();
    }

    /**
     * Get users di aplikasi tertentu
     */
    public function getUsersByApplication($applicationId)
    {
        return $this->select('user_roles.*, users.nama_lengkap, users.email, users.avatar, 
                             roles.role_name, roles.role_label')
                    ->join('users', 'users.id = user_roles.user_id')
                    ->join('roles', 'roles.id = user_roles.role_id')
                    ->where('user_roles.application_id', $applicationId)
                    ->where('users.deleted_at', null)
                    ->findAll();
    }

    /**
     * Check if user is member of application
     */
    public function isMember($userId, $applicationId)
    {
        return $this->where('user_id', $userId)
                    ->where('application_id', $applicationId)
                    ->countAllResults() > 0;
    }

    /**
     * Check if user has role in application
     */
    public function hasRole($userId, $roleName, $applicationId = null)
    {
        $builder = $this->select('user_roles.*')
                        ->join('roles', 'roles.id = user_roles.role_id')
                        ->where('user_roles.user_id', $userId)
                        ->where('roles.role_name', $roleName);

        if ($applicationId !== null) {
            $builder->where('user_roles.application_id', $applicationId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId, $applicationId = null)
    {
        // Check if already exists
        $existing = $this->where('user_id', $userId)
                         ->where('role_id', $roleId)
                         ->where('application_id', $applicationId)
                         ->first();

        if ($existing) {
            return $existing['id'];
        }

        // Insert new
        return $this->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'application_id' => $applicationId,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Remove role from user
     */
    public function removeRole($userId, $roleId, $applicationId = null)
    {
        $builder = $this->where('user_id', $userId)
                        ->where('role_id', $roleId);

        if ($applicationId !== null) {
            $builder->where('application_id', $applicationId);
        }

        return $builder->delete();
    }

    /**
     * Remove user dari application (semua roles)
     */
    public function removeUserFromApp($userId, $applicationId)
    {
        return $this->where('user_id', $userId)
                    ->where('application_id', $applicationId)
                    ->delete();
    }

    /**
     * Update user role di application
     */
    public function updateUserRole($userId, $applicationId, $newRoleId)
    {
        return $this->where('user_id', $userId)
                    ->where('application_id', $applicationId)
                    ->set(['role_id' => $newRoleId, 'updated_at' => date('Y-m-d H:i:s')])
                    ->update();
    }

    /**
     * Get superadmin users
     */
    public function getSuperadmins()
    {
        return $this->select('user_roles.*, users.nama_lengkap, users.email, roles.role_name')
                    ->join('users', 'users.id = user_roles.user_id')
                    ->join('roles', 'roles.id = user_roles.role_id')
                    ->where('roles.role_name', 'superadmin')
                    ->where('user_roles.application_id', null)
                    ->where('users.deleted_at', null)
                    ->findAll();
    }

    /**
     * Check if user is superadmin
     */
    public function isSuperadmin($userId)
    {
        return $this->select('user_roles.*')
                    ->join('roles', 'roles.id = user_roles.role_id')
                    ->where('user_roles.user_id', $userId)
                    ->where('roles.role_name', 'superadmin')
                    ->where('user_roles.application_id', null)
                    ->countAllResults() > 0;
    }

    /**
     * Get applications where user is owner
     */
    public function getOwnedApplications($userId)
    {
        return $this->select('applications.*')
                    ->join('roles', 'roles.id = user_roles.role_id')
                    ->join('applications', 'applications.id = user_roles.application_id')
                    ->where('user_roles.user_id', $userId)
                    ->where('roles.role_name', 'owner')
                    ->where('applications.deleted_at', null)
                    ->findAll();
    }

    /**
     * Get role statistics
     */
    public function getRoleStatistics()
    {
        return $this->select('roles.role_label, COUNT(user_roles.id) as user_count')
                    ->join('roles', 'roles.id = user_roles.role_id')
                    ->groupBy('roles.id, roles.role_label')
                    ->findAll();
    }

    /**
     * Toggle active status
     */
    public function toggleActive($userRoleId)
    {
        $userRole = $this->find($userRoleId);
        if (!$userRole) return false;

        $newStatus = $userRole['is_active'] == 1 ? 0 : 1;
        return $this->update($userRoleId, ['is_active' => $newStatus]);
    }
}