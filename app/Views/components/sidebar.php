<!-- Sidebar Component -->
<aside class="sidebar" id="sidebar">
    
    <!-- Brand -->
    <div class="sidebar-brand">
        <i class="bi bi-bar-chart-fill"></i>
        <span>DataStat</span>
    </div>
    
    
    <!-- Menu -->
    <ul class="sidebar-menu">
        
        <?php 
        $currentUri = uri_string();
        $roleName = session()->get('role_name');
        ?>
        
        <!-- Dashboard (All Roles) -->
        <li>
            <a href="<?= base_url('dashboard') ?>" class="<?= $currentUri == 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <!-- Superadmin Menu -->
        <?php if ($roleName === 'superadmin'): ?>
            
            <li class="mt-3">
                <div class="px-3 py-2 text-white-50 small text-uppercase">
                    <span>Superadmin</span>
                </div>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/dashboard') ?>" class="<?= strpos($currentUri, 'superadmin/dashboard') !== false ? 'active' : '' ?>">
                    <i class="bi bi-grid-fill"></i>
                    <span>Overview</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/users') ?>" class="<?= strpos($currentUri, 'superadmin/users') !== false ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Users</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/applications') ?>" class="<?= strpos($currentUri, 'superadmin/applications') !== false ? 'active' : '' ?>">
                    <i class="bi bi-app-indicator"></i>
                    <span>Applications</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/roles') ?>" class="<?= strpos($currentUri, 'superadmin/roles') !== false ? 'active' : '' ?>">
                    <i class="bi bi-shield-fill-check"></i>
                    <span>Roles</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/logs') ?>" class="<?= strpos($currentUri, 'superadmin/logs') !== false ? 'active' : '' ?>">
                    <i class="bi bi-activity"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/reports') ?>" class="<?= strpos($currentUri, 'superadmin/reports') !== false ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Reports</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/settings') ?>" class="<?= strpos($currentUri, 'superadmin/settings') !== false ? 'active' : '' ?>">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
            
        <?php endif; ?>
        
        <!-- Owner Menu -->
        <?php if ($roleName === 'owner' || $roleName === 'superadmin'): ?>
            
            <li class="mt-3">
                <div class="px-3 py-2 text-white-50 small text-uppercase">
                    <span>Workspace</span>
                </div>
            </li>
            
            <li>
                <a href="<?= base_url('owner/dashboard') ?>" class="<?= strpos($currentUri, 'owner/dashboard') !== false ? 'active' : '' ?>">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('owner/datasets') ?>" class="<?= strpos($currentUri, 'owner/datasets') !== false ? 'active' : '' ?>">
                    <i class="bi bi-database-fill"></i>
                    <span>Datasets</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('owner/statistics') ?>" class="<?= strpos($currentUri, 'owner/statistics') !== false ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Statistics</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('owner/dashboards') ?>" class="<?= strpos($currentUri, 'owner/dashboards') !== false ? 'active' : '' ?>">
                    <i class="bi bi-layout-text-window-reverse"></i>
                    <span>Dashboards</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('owner/users') ?>" class="<?= strpos($currentUri, 'owner/users') !== false ? 'active' : '' ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Team Members</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('owner/settings') ?>" class="<?= strpos($currentUri, 'owner/settings') !== false ? 'active' : '' ?>">
                    <i class="bi bi-sliders"></i>
                    <span>Settings</span>
                </a>
            </li>
            
        <?php endif; ?>
        
        <!-- Viewer Menu -->
        <?php if ($roleName === 'viewer' || $roleName === 'owner' || $roleName === 'superadmin'): ?>
            
            <li class="mt-3">
                <div class="px-3 py-2 text-white-50 small text-uppercase">
                    <span>View</span>
                </div>
            </li>
            
            <li>
                <a href="<?= base_url('viewer/dashboards') ?>" class="<?= strpos($currentUri, 'viewer/dashboards') !== false ? 'active' : '' ?>">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <span>Dashboards</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('viewer/statistics') ?>" class="<?= strpos($currentUri, 'viewer/statistics') !== false ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <span>Statistics</span>
                </a>
            </li>
            
        <?php endif; ?>
        
        <!-- Profile & Logout (All Roles) -->
        <li class="mt-3">
            <div class="px-3 py-2 text-white-50 small text-uppercase">
                <span>Account</span>
            </div>
        </li>
        
        <li>
            <a href="<?= base_url('profile') ?>" class="<?= strpos($currentUri, 'profile') !== false ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>
        </li>
        
        <li>
            <a href="<?= base_url('logout') ?>" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </li>
        
    </ul>
    
</aside>