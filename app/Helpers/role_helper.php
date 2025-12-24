<?php

/**
 * ============================================================================
 * ROLE HELPER
 * ============================================================================
 * 
 * Path: app/Helpers/role_helper.php
 * 
 * Helper functions untuk role management
 * ============================================================================
 */

if (!function_exists('get_role_badge')) {
    /**
     * Get HTML badge for role
     */
    function get_role_badge(string $roleName): string
    {
        $badges = [
            'superadmin' => '<span class="badge bg-purple">Superadmin</span>',
            'owner' => '<span class="badge bg-success">Owner</span>',
            'viewer' => '<span class="badge bg-info">Viewer</span>'
        ];
        
        return $badges[$roleName] ?? '<span class="badge bg-secondary">' . ucfirst($roleName) . '</span>';
    }
}

if (!function_exists('get_role_color')) {
    /**
     * Get color for role
     */
    function get_role_color(string $roleName): string
    {
        $colors = [
            'superadmin' => '#6f42c1',
            'owner' => '#198754',
            'viewer' => '#0dcaf0'
        ];
        
        return $colors[$roleName] ?? '#6c757d';
    }
}

if (!function_exists('get_role_icon')) {
    /**
     * Get icon for role
     */
    function get_role_icon(string $roleName): string
    {
        $icons = [
            'superadmin' => 'fa-crown',
            'owner' => 'fa-user-shield',
            'viewer' => 'fa-eye'
        ];
        
        return $icons[$roleName] ?? 'fa-user';
    }
}

if (!function_exists('get_role_label')) {
    /**
     * Get display label for role
     */
    function get_role_label(string $roleName): string
    {
        $labels = [
            'superadmin' => 'Super Administrator',
            'owner' => 'Owner',
            'viewer' => 'Viewer'
        ];
        
        return $labels[$roleName] ?? ucfirst($roleName);
    }
}

if (!function_exists('get_role_description')) {
    /**
     * Get description for role
     */
    function get_role_description(string $roleName): string
    {
        $descriptions = [
            'superadmin' => 'Full system access with all permissions',
            'owner' => 'Workspace owner with full CRUD access',
            'viewer' => 'Read-only access to dashboards and statistics'
        ];
        
        return $descriptions[$roleName] ?? '';
    }
}

if (!function_exists('get_all_roles')) {
    /**
     * Get all available roles
     */
    function get_all_roles(): array
    {
        return [
            [
                'name' => 'superadmin',
                'label' => 'Super Administrator',
                'description' => 'Full system access',
                'color' => '#6f42c1',
                'icon' => 'fa-crown'
            ],
            [
                'name' => 'owner',
                'label' => 'Owner',
                'description' => 'Workspace owner',
                'color' => '#198754',
                'icon' => 'fa-user-shield'
            ],
            [
                'name' => 'viewer',
                'label' => 'Viewer',
                'description' => 'Read-only access',
                'color' => '#0dcaf0',
                'icon' => 'fa-eye'
            ]
        ];
    }
}

if (!function_exists('can_manage_users')) {
    /**
     * Check if current role can manage users
     */
    function can_manage_users(): bool
    {
        return has_any_role(['superadmin', 'owner']);
    }
}

if (!function_exists('can_manage_datasets')) {
    /**
     * Check if current role can manage datasets
     */
    function can_manage_datasets(): bool
    {
        return has_any_role(['superadmin', 'owner']);
    }
}

if (!function_exists('can_manage_statistics')) {
    /**
     * Check if current role can manage statistics
     */
    function can_manage_statistics(): bool
    {
        return has_any_role(['superadmin', 'owner']);
    }
}

if (!function_exists('can_manage_dashboards')) {
    /**
     * Check if current role can manage dashboards
     */
    function can_manage_dashboards(): bool
    {
        return has_any_role(['superadmin', 'owner']);
    }
}

if (!function_exists('can_view_only')) {
    /**
     * Check if current role is view-only
     */
    function can_view_only(): bool
    {
        return is_viewer();
    }
}

if (!function_exists('can_export_data')) {
    /**
     * Check if current role can export data
     */
    function can_export_data(): bool
    {
        return is_logged_in(); // All logged in users can export
    }
}

if (!function_exists('can_access_superadmin')) {
    /**
     * Check if can access superadmin area
     */
    function can_access_superadmin(): bool
    {
        return is_superadmin();
    }
}

if (!function_exists('can_access_owner')) {
    /**
     * Check if can access owner area
     */
    function can_access_owner(): bool
    {
        return has_any_role(['superadmin', 'owner']);
    }
}

if (!function_exists('can_access_viewer')) {
    /**
     * Check if can access viewer area
     */
    function can_access_viewer(): bool
    {
        return is_logged_in(); // All users can view
    }
}

if (!function_exists('get_accessible_routes')) {
    /**
     * Get accessible routes based on role
     */
    function get_accessible_routes(): array
    {
        $role = current_user_role();
        
        $routes = [
            'superadmin' => [
                '/superadmin/*',
                '/owner/*',
                '/viewer/*'
            ],
            'owner' => [
                '/owner/*',
                '/viewer/*'
            ],
            'viewer' => [
                '/viewer/*'
            ]
        ];
        
        return $routes[$role] ?? [];
    }
}

if (!function_exists('can_access_route')) {
    /**
     * Check if user can access specific route
     */
    function can_access_route(string $route): bool
    {
        $accessibleRoutes = get_accessible_routes();
        
        foreach ($accessibleRoutes as $pattern) {
            $pattern = str_replace('*', '.*', $pattern);
            if (preg_match('#^' . $pattern . '$#', $route)) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('get_role_permissions_list')) {
    /**
     * Get formatted list of permissions for a role
     */
    function get_role_permissions_list(string $roleName): array
    {
        $permissions = [
            'superadmin' => [
                'Manage Users' => 'Create, edit, delete users',
                'Manage Applications' => 'Create, edit, delete workspaces',
                'Manage Roles' => 'Assign and modify user roles',
                'View Logs' => 'Access system activity logs',
                'System Settings' => 'Modify system configuration',
                'Full Data Access' => 'Access all workspaces and data'
            ],
            'owner' => [
                'Manage Datasets' => 'Upload, edit, delete datasets',
                'Manage Statistics' => 'Create and configure statistics',
                'Manage Dashboards' => 'Create and edit dashboards',
                'Manage Team' => 'Invite and manage workspace members',
                'Workspace Settings' => 'Configure workspace settings',
                'Export Data' => 'Export datasets and reports'
            ],
            'viewer' => [
                'View Dashboards' => 'Access and view dashboards',
                'View Statistics' => 'View statistical data',
                'Export Data' => 'Download data and reports'
            ]
        ];
        
        return $permissions[$roleName] ?? [];
    }
}

if (!function_exists('format_role_card')) {
    /**
     * Format role information as HTML card
     */
    function format_role_card(string $roleName): string
    {
        $role = null;
        foreach (get_all_roles() as $r) {
            if ($r['name'] === $roleName) {
                $role = $r;
                break;
            }
        }
        
        if (!$role) {
            return '';
        }
        
        $html = '<div class="role-card" style="border-left: 4px solid ' . $role['color'] . '; padding: 15px;">';
        $html .= '<div class="d-flex align-items-center mb-2">';
        $html .= '<i class="fas ' . $role['icon'] . ' me-2" style="color: ' . $role['color'] . ';"></i>';
        $html .= '<h5 class="mb-0">' . $role['label'] . '</h5>';
        $html .= '</div>';
        $html .= '<p class="text-muted mb-0">' . $role['description'] . '</p>';
        $html .= '</div>';
        
        return $html;
    }
}