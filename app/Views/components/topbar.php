<!-- Topbar Component -->
<div class="topbar">
    
    <div class="topbar-left">
        <!-- Toggle Sidebar Button -->
        <button class="btn-toggle-sidebar" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        
        <!-- Breadcrumb (optional, bisa diisi dari controller) -->
        <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                        <?php if ($index === count($breadcrumbs) - 1): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= esc($breadcrumb['label']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <a href="<?= esc($breadcrumb['url']) ?>"><?= esc($breadcrumb['label']) ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
    </div>
    
    <div class="topbar-right">
        
        <!-- Workspace Selector (for Owner/Viewer) -->
        <?php if (session()->get('application_id')): ?>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-building"></i>
                    <span class="ms-1"><?= esc(session()->get('app_name') ?? 'Workspace') ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Switch Workspace</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item active" href="#">
                            <i class="bi bi-check2 me-2"></i>
                            <?= esc(session()->get('app_name') ?? 'Current Workspace') ?>
                        </a>
                    </li>
                    <!-- Add more workspaces here if user has multiple -->
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Notifications (placeholder) -->
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <!-- Notification Badge -->
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                    0
                    <span class="visually-hidden">unread notifications</span>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <div class="px-3 py-2 text-muted text-center">
                        <small>No new notifications</small>
                    </div>
                </li>
            </ul>
        </div>
        
        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                <?php if (session()->get('avatar')): ?>
                    <img src="<?= base_url(session()->get('avatar')) ?>" alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                <?php else: ?>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                        <?= strtoupper(substr(session()->get('nama_lengkap') ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="text-start d-none d-md-block">
                    <div class="fw-semibold" style="font-size: 0.875rem;">
                        <?= esc(session()->get('nama_lengkap') ?? 'User') ?>
                    </div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        <?php
                        $roleName = session()->get('role_name');
                        $roleLabels = [
                            'superadmin' => 'Super Admin',
                            'owner' => 'Owner',
                            'viewer' => 'Viewer'
                        ];
                        echo $roleLabels[$roleName] ?? ucfirst($roleName);
                        ?>
                    </div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <div class="dropdown-header">
                        <div class="fw-semibold"><?= esc(session()->get('nama_lengkap') ?? 'User') ?></div>
                        <small class="text-muted"><?= esc(session()->get('email') ?? '') ?></small>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="<?= base_url('profile') ?>">
                        <i class="bi bi-person me-2"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= base_url('profile/settings') ?>">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= base_url('logout') ?>" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
    </div>
    
</div>