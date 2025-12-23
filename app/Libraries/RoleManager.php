<?php

/**
 * ============================================================================
 * ROLE MANAGER LIBRARY
 * ============================================================================
 * 
 * Path: app/Libraries/RoleManager.php
 * 
 * Deskripsi:
 * Library untuk mengelola roles, permissions, dan authorization.
 * Wrapper untuk RoleModel dan UserRoleModel dengan helper methods.
 * 
 * Features:
 * - Check user roles
 * - Check permissions
 * - Role assignment
 * - Permission management
 * - Multi-tenant support
 * 
 * Used by: Filters, Controllers (authorization)
 * ============================================================================
 */

namespace App\Libraries;

use App\Models\Superadmin\RoleModel;
use App\Models\Superadmin\UserRoleModel;

class RoleManager
{
    protected $roleModel;
    protected $userRoleModel;
    protected $session;
    
    /**
     * Default roles
     */
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_OWNER = 'owner';
    const ROLE_VIEWER = 'viewer';

    /**
     * Permissions
     */
    const PERM_VIEW_DASHBOARD = 'view_dashboard';
    const PERM_MANAGE_USERS = 'manage_users';
    const PERM_MANAGE_DATASETS = 'manage_datasets';
    const PERM_MANAGE_STATISTICS = 'manage_statistics';
    const PERM_MANAGE_DASHBOARDS = 'manage_dashboards';
    const PERM_EXPORT_DATA = 'export_data';
    const PERM_IMPORT_DATA = 'import_data';
    const PERM_MANAGE_SETTINGS = 'manage_settings';
    const PERM_VIEW_LOGS = 'view_logs';
    const PERM_MANAGE_ROLES = 'manage_roles';

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->session = session();
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($userId, $roleName, $applicationId = null)
    {
        return $this->userRoleModel->hasRole($userId, $roleName, $applicationId);
    }

    /**
     * Check if current logged-in user has role
     */
    public function userHasRole($roleName, $applicationId = null)
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            return false;
        }

        return $this->hasRole($userId, $roleName, $applicationId);
    }

    /**
     * Check if user is superadmin
     */
    public function isSuperadmin($userId = null)
    {
        $userId = $userId ?? $this->session->get('user_id');
        if (!$userId) {
            return false;
        }

        return $this->userRoleModel->isSuperadmin($userId);
    }

    /**
     * Check if user is owner of application
     */
    public function isOwner($userId = null, $applicationId = null)
    {
        $userId = $userId ?? $this->session->get('user_id');
        $applicationId = $applicationId ?? $this->session->get('application_id');
        
        if (!$userId || !$applicationId) {
            return false;
        }

        return $this->hasRole($userId, self::ROLE_OWNER, $applicationId);
    }

    /**
     * Check if user is viewer
     */
    public function isViewer($userId = null, $applicationId = null)
    {
        $userId = $userId ?? $this->session->get('user_id');
        $applicationId = $applicationId ?? $this->session->get('application_id');
        
        if (!$userId || !$applicationId) {
            return false;
        }

        return $this->hasRole($userId, self::ROLE_VIEWER, $applicationId);
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $permission, $applicationId = null)
    {
        // Get user roles
        $userRoles = $this->userRoleModel->getUserRoles($userId);
        
        foreach ($userRoles as $userRole) {
            // Skip if application_id doesn't match
            if ($applicationId !== null && $userRole['application_id'] != $applicationId) {
                continue;
            }

            // Get role permissions
            $permissions = $this->roleModel->getPermissions($userRole['role_id']);
            
            if (in_array($permission, $permissions)) {
                return true;
            }

            // Superadmin has all permissions
            if ($userRole['role_name'] === self::ROLE_SUPERADMIN) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if current user has permission
     */
    public function userHasPermission($permission, $applicationId = null)
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            return false;
        }

        $applicationId = $applicationId ?? $this->session->get('application_id');
        return $this->hasPermission($userId, $permission, $applicationId);
    }

    /**
     * Get user's role in application
     */
    public function getUserRole($userId, $applicationId)
    {
        return $this->userRoleModel->getUserRoleInApp($userId, $applicationId);
    }

    /**
     * Get current user's role
     */
    public function getCurrentUserRole()
    {
        $userId = $this->session->get('user_id');
        $applicationId = $this->session->get('application_id');

        if (!$userId || !$applicationId) {
            return null;
        }

        return $this->getUserRole($userId, $applicationId);
    }

    /**
     * Get user's role name
     */
    public function getUserRoleName($userId = null, $applicationId = null)
    {
        $userId = $userId ?? $this->session->get('user_id');
        $applicationId = $applicationId ?? $this->session->get('application_id');

        if (!$userId) {
            return null;
        }

        // Check superadmin first
        if ($this->isSuperadmin($userId)) {
            return self::ROLE_SUPERADMIN;
        }

        if (!$applicationId) {
            return null;
        }

        $userRole = $this->getUserRole($userId, $applicationId);
        return $userRole['role_name'] ?? null;
    }

    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId, $applicationId = null)
    {
        try {
            return $this->userRoleModel->assignRole($userId, $roleId, $applicationId);
        } catch (\Exception $e) {
            log_message('error', 'RoleManager assignRole error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole($userId, $roleId, $applicationId = null)
    {
        try {
            return $this->userRoleModel->removeRole($userId, $roleId, $applicationId);
        } catch (\Exception $e) {
            log_message('error', 'RoleManager removeRole error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user role
     */
    public function updateUserRole($userId, $applicationId, $newRoleId)
    {
        try {
            return $this->userRoleModel->updateUserRole($userId, $applicationId, $newRoleId);
        } catch (\Exception $e) {
            log_message('error', 'RoleManager updateUserRole error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get role by name
     */
    public function getRoleByName($roleName)
    {
        return $this->roleModel->getRoleByName($roleName);
    }

    /**
     * Get role ID by name
     */
    public function getRoleId($roleName)
    {
        $role = $this->getRoleByName($roleName);
        return $role['id'] ?? null;
    }

    /**
     * Get all roles
     */
    public function getAllRoles()
    {
        return $this->roleModel->findAll();
    }

    /**
     * Get assignable roles (for dropdown)
     */
    public function getAssignableRoles()
    {
        return $this->roleModel->getAssignableRoles();
    }

    /**
     * Get role permissions
     */
    public function getRolePermissions($roleId)
    {
        return $this->roleModel->getPermissions($roleId);
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions($roleId, $permissions)
    {
        try {
            return $this->roleModel->updatePermissions($roleId, $permissions);
        } catch (\Exception $e) {
            log_message('error', 'RoleManager updateRolePermissions error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is member of application
     */
    public function isMember($userId, $applicationId)
    {
        return $this->userRoleModel->isMember($userId, $applicationId);
    }

    /**
     * Check if current user is member
     */
    public function isCurrentUserMember($applicationId = null)
    {
        $userId = $this->session->get('user_id');
        $applicationId = $applicationId ?? $this->session->get('application_id');

        if (!$userId || !$applicationId) {
            return false;
        }

        return $this->isMember($userId, $applicationId);
    }

    /**
     * Get users by role in application
     */
    public function getUsersByRole($roleName, $applicationId)
    {
        $role = $this->getRoleByName($roleName);
        if (!$role) {
            return [];
        }

        return $this->userRoleModel->getUsersByApplication($applicationId);
    }

    /**
     * Can user perform action?
     */
    public function can($action, $resource = null, $userId = null, $applicationId = null)
    {
        $userId = $userId ?? $this->session->get('user_id');
        
        if (!$userId) {
            return false;
        }

        // Superadmin can do anything
        if ($this->isSuperadmin($userId)) {
            return true;
        }

        $applicationId = $applicationId ?? $this->session->get('application_id');

        // Map actions to permissions
        $permissionMap = [
            'view' => self::PERM_VIEW_DASHBOARD,
            'create' => self::PERM_MANAGE_DATASETS,
            'update' => self::PERM_MANAGE_DATASETS,
            'delete' => self::PERM_MANAGE_DATASETS,
            'export' => self::PERM_EXPORT_DATA,
            'import' => self::PERM_IMPORT_DATA,
            'manage_users' => self::PERM_MANAGE_USERS,
            'manage_settings' => self::PERM_MANAGE_SETTINGS,
            'view_logs' => self::PERM_VIEW_LOGS
        ];

        $permission = $permissionMap[$action] ?? $action;

        return $this->hasPermission($userId, $permission, $applicationId);
    }

    /**
     * Get default permissions untuk role
     */
    public function getDefaultPermissions($roleName)
    {
        switch ($roleName) {
            case self::ROLE_SUPERADMIN:
                return [
                    self::PERM_VIEW_DASHBOARD,
                    self::PERM_MANAGE_USERS,
                    self::PERM_MANAGE_DATASETS,
                    self::PERM_MANAGE_STATISTICS,
                    self::PERM_MANAGE_DASHBOARDS,
                    self::PERM_EXPORT_DATA,
                    self::PERM_IMPORT_DATA,
                    self::PERM_MANAGE_SETTINGS,
                    self::PERM_VIEW_LOGS,
                    self::PERM_MANAGE_ROLES
                ];

            case self::ROLE_OWNER:
                return [
                    self::PERM_VIEW_DASHBOARD,
                    self::PERM_MANAGE_USERS,
                    self::PERM_MANAGE_DATASETS,
                    self::PERM_MANAGE_STATISTICS,
                    self::PERM_MANAGE_DASHBOARDS,
                    self::PERM_EXPORT_DATA,
                    self::PERM_IMPORT_DATA,
                    self::PERM_MANAGE_SETTINGS
                ];

            case self::ROLE_VIEWER:
                return [
                    self::PERM_VIEW_DASHBOARD,
                    self::PERM_EXPORT_DATA
                ];

            default:
                return [];
        }
    }

    /**
     * Initialize default roles (untuk seeder)
     */
    public function initializeDefaultRoles()
    {
        $defaultRoles = [
            [
                'role_name' => self::ROLE_SUPERADMIN,
                'role_label' => 'Super Admin',
                'description' => 'Full system access',
                'permissions' => json_encode($this->getDefaultPermissions(self::ROLE_SUPERADMIN))
            ],
            [
                'role_name' => self::ROLE_OWNER,
                'role_label' => 'Owner',
                'description' => 'Workspace owner with full access to their application',
                'permissions' => json_encode($this->getDefaultPermissions(self::ROLE_OWNER))
            ],
            [
                'role_name' => self::ROLE_VIEWER,
                'role_label' => 'Viewer',
                'description' => 'Read-only access to dashboards and statistics',
                'permissions' => json_encode($this->getDefaultPermissions(self::ROLE_VIEWER))
            ]
        ];

        foreach ($defaultRoles as $role) {
            $existing = $this->roleModel->getRoleByName($role['role_name']);
            if (!$existing) {
                $role['created_at'] = date('Y-m-d H:i:s');
                $this->roleModel->insert($role);
            }
        }

        return true;
    }
}