<?php

/**
 * ============================================================================
 * ROLE MODEL
 * ============================================================================
 * 
 * Path: app/Models/Superadmin/RoleModel.php
 * 
 * Deskripsi:
 * Model untuk mengelola roles dan permissions di sistem.
 * Roles: superadmin, owner, viewer (dan custom roles)
 * 
 * Table: roles
 * 
 * Fields:
 * - id (PK)
 * - role_name - Nama role (unique, lowercase, underscore)
 * - role_label - Label tampilan (Title Case)
 * - description - Deskripsi role
 * - permissions (JSON) - Array permissions
 * - created_at, updated_at
 * 
 * Relations:
 * - hasMany: user_roles
 * 
 * Used by: Superadmin, Owner
 * ============================================================================
 */

namespace App\Models\Superadmin;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'role_name',
        'role_label',
        'description',
        'permissions',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'role_name'  => 'required|alpha_dash|is_unique[roles.role_name,id,{id}]|max_length[50]',
        'role_label' => 'required|min_length[3]|max_length[100]'
    ];
    
    protected $validationMessages = [
        'role_name' => [
            'required' => 'Nama role harus diisi',
            'alpha_dash' => 'Nama role hanya boleh alfanumerik, dash, dan underscore',
            'is_unique' => 'Nama role sudah digunakan'
        ],
        'role_label' => [
            'required' => 'Label role harus diisi',
            'min_length' => 'Label role minimal 3 karakter'
        ]
    ];
    
    protected $skipValidation = false;

    /**
     * Get role by name
     */
    public function getRoleByName($roleName)
    {
        return $this->where('role_name', $roleName)->first();
    }

    /**
     * Get roles dengan user count
     */
    public function getWithUserCount()
    {
        return $this->select('roles.*, 
                             (SELECT COUNT(*) FROM user_roles WHERE role_id = roles.id) as user_count')
                    ->findAll();
    }

    /**
     * Get default roles (superadmin, owner, viewer)
     */
    public function getDefaultRoles()
    {
        return $this->whereIn('role_name', ['superadmin', 'owner', 'viewer'])->findAll();
    }

    /**
     * Get custom roles (non-default)
     */
    public function getCustomRoles()
    {
        return $this->whereNotIn('role_name', ['superadmin', 'owner', 'viewer'])->findAll();
    }

    /**
     * Check if role is default (cannot be deleted)
     */
    public function isDefaultRole($roleId)
    {
        $role = $this->find($roleId);
        if (!$role) return false;

        return in_array($role['role_name'], ['superadmin', 'owner', 'viewer']);
    }

    /**
     * Get permissions for role
     */
    public function getPermissions($roleId)
    {
        $role = $this->find($roleId);
        if (!$role) return [];

        return json_decode($role['permissions'] ?? '[]', true);
    }

    /**
     * Update permissions
     */
    public function updatePermissions($roleId, $permissions)
    {
        return $this->update($roleId, [
            'permissions' => json_encode($permissions)
        ]);
    }

    /**
     * Check if role has permission
     */
    public function hasPermission($roleId, $permission)
    {
        $permissions = $this->getPermissions($roleId);
        return in_array($permission, $permissions);
    }

    /**
     * Get roles untuk dropdown (id => label)
     */
    public function getRolesForDropdown()
    {
        $roles = $this->findAll();
        $dropdown = [];
        
        foreach ($roles as $role) {
            $dropdown[$role['id']] = $role['role_label'];
        }
        
        return $dropdown;
    }

    /**
     * Get assignable roles (owner & viewer, not superadmin)
     */
    public function getAssignableRoles()
    {
        return $this->whereIn('role_name', ['owner', 'viewer'])->findAll();
    }
}