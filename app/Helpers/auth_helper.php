<?php

/**
 * ============================================================================
 * AUTH HELPER
 * ============================================================================
 * 
 * Path: app/Helpers/auth_helper.php
 * 
 * Helper functions untuk authentication dan authorization
 * ============================================================================
 */

if (!function_exists('is_logged_in')) {
    /**
     * Check if user is logged in
     */
    function is_logged_in(): bool
    {
        return session()->get('logged_in') === true;
    }
}

if (!function_exists('current_user_id')) {
    /**
     * Get current user ID
     */
    function current_user_id(): ?int
    {
        return session()->get('user_id');
    }
}

if (!function_exists('current_user_email')) {
    /**
     * Get current user email
     */
    function current_user_email(): ?string
    {
        return session()->get('email');
    }
}

if (!function_exists('current_user_name')) {
    /**
     * Get current user name
     */
    function current_user_name(): ?string
    {
        return session()->get('nama_lengkap');
    }
}

if (!function_exists('current_user_role')) {
    /**
     * Get current user role name
     */
    function current_user_role(): ?string
    {
        return session()->get('role_name');
    }
}

if (!function_exists('current_user_role_id')) {
    /**
     * Get current user role ID
     */
    function current_user_role_id(): ?int
    {
        return session()->get('role_id');
    }
}

if (!function_exists('current_application_id')) {
    /**
     * Get current application/workspace ID
     */
    function current_application_id(): ?int
    {
        return session()->get('application_id');
    }
}

if (!function_exists('current_application_name')) {
    /**
     * Get current application/workspace name
     */
    function current_application_name(): ?string
    {
        return session()->get('app_name');
    }
}

if (!function_exists('is_superadmin')) {
    /**
     * Check if current user is superadmin
     */
    function is_superadmin(): bool
    {
        return session()->get('role_name') === 'superadmin';
    }
}

if (!function_exists('is_owner')) {
    /**
     * Check if current user is owner
     */
    function is_owner(): bool
    {
        return session()->get('role_name') === 'owner';
    }
}

if (!function_exists('is_viewer')) {
    /**
     * Check if current user is viewer
     */
    function is_viewer(): bool
    {
        return session()->get('role_name') === 'viewer';
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if user has specific role
     */
    function has_role(string $role): bool
    {
        return session()->get('role_name') === $role;
    }
}

if (!function_exists('has_any_role')) {
    /**
     * Check if user has any of the specified roles
     */
    function has_any_role(array $roles): bool
    {
        $currentRole = session()->get('role_name');
        return in_array($currentRole, $roles);
    }
}

if (!function_exists('can_access_application')) {
    /**
     * Check if user can access specific application
     */
    function can_access_application(int $applicationId): bool
    {
        // Superadmin can access all applications
        if (is_superadmin()) {
            return true;
        }

        // Owner and Viewer can only access their assigned application
        return current_application_id() === $applicationId;
    }
}

if (!function_exists('user_avatar_url')) {
    /**
     * Get user avatar URL with fallback to default
     */
    function user_avatar_url(?string $avatar = null): string
    {
        $avatar = $avatar ?? session()->get('avatar');
        
        if ($avatar && file_exists(FCPATH . 'uploads/avatars/' . $avatar)) {
            return base_url('uploads/avatars/' . $avatar);
        }

        // Default avatar
        return base_url('assets/img/default-avatar.png');
    }
}

if (!function_exists('user_initials')) {
    /**
     * Get user initials from name
     */
    function user_initials(?string $name = null): string
    {
        $name = $name ?? current_user_name();
        
        if (!$name) {
            return 'U';
        }

        $words = explode(' ', $name);
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        return substr($initials, 0, 2);
    }
}

if (!function_exists('redirect_to_role_dashboard')) {
    /**
     * Redirect to dashboard based on user role
     */
    function redirect_to_role_dashboard()
    {
        $role = current_user_role();

        switch ($role) {
            case 'superadmin':
                return redirect()->to('/superadmin/dashboard');
            
            case 'owner':
                return redirect()->to('/owner/dashboard');
            
            case 'viewer':
                return redirect()->to('/viewer/dashboard');
            
            default:
                return redirect()->to('/login');
        }
    }
}

if (!function_exists('check_session_timeout')) {
    /**
     * Check if session has timed out
     */
    function check_session_timeout(): bool
    {
        $lastActivity = session()->get('last_activity');
        $timeout = 7200; // 2 hours in seconds

        if ($lastActivity && (time() - $lastActivity > $timeout)) {
            session()->destroy();
            return true;
        }

        session()->set('last_activity', time());
        return false;
    }
}

if (!function_exists('get_user_permissions')) {
    /**
     * Get user permissions based on role
     */
    function get_user_permissions(): array
    {
        $role = current_user_role();

        $permissions = [
            'superadmin' => [
                'manage_users', 'manage_applications', 'manage_roles',
                'view_logs', 'manage_settings', 'view_all_data',
                'manage_datasets', 'manage_statistics', 'manage_dashboards'
            ],
            'owner' => [
                'manage_datasets', 'manage_statistics', 'manage_dashboards',
                'manage_team', 'workspace_settings', 'view_data', 'export_data'
            ],
            'viewer' => [
                'view_dashboards', 'view_statistics', 'export_data'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if user has specific permission
     */
    function has_permission(string $permission): bool
    {
        $permissions = get_user_permissions();
        return in_array($permission, $permissions);
    }
}