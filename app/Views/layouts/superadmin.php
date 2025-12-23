<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'Superadmin Dashboard' ?> - DataStat App</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
    
    <!-- Custom CSS for Superadmin -->
    <style>
        :root {
            --admin-primary: #6f42c1;
            --admin-secondary: #6c757d;
            --admin-accent: #d63384;
            --sidebar-width: 260px;
            --topbar-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        /* Superadmin Sidebar - Purple Theme */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #6f42c1 0%, #5a32a3 100%);
            color: white;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-brand {
            padding: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .admin-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: rgba(255,255,255,0.15);
            color: white;
            border-left: 4px solid #ffc107;
        }

        .sidebar-menu li a i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .sidebar.collapsed .sidebar-menu li a span {
            display: none;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 70px;
        }

        /* Topbar - Superadmin Theme */
        .topbar {
            height: var(--topbar-height);
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar * {
            color: white !important;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: white !important;
        }

        .admin-mode-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }

        /* Content Area */
        .content-wrapper {
            padding: 1.5rem;
            min-height: calc(100vh - var(--topbar-height));
        }

        /* Cards - Superadmin Style */
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            border-left: 4px solid #6f42c1;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }

        /* Stats Card - Superadmin */
        .stats-card {
            border-left: 4px solid #6f42c1;
        }

        .stats-card.success {
            border-left-color: #198754;
        }

        .stats-card.warning {
            border-left-color: #ffc107;
        }

        .stats-card.danger {
            border-left-color: #dc3545;
        }

        .stats-card .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
        }

        /* Buttons - Superadmin Theme */
        .btn-primary {
            background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a32a3 0%, #4a2685 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
            border: none;
        }

        /* Page Title */
        .page-title {
            margin-bottom: 1.5rem;
            border-left: 5px solid #6f42c1;
            padding-left: 1rem;
        }

        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
            color: #6f42c1;
        }

        /* Admin Alert */
        .admin-alert {
            background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
            border-left: 4px solid #6f42c1;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
        }

        /* Loading Spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(111, 66, 193, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .spinner-overlay.show {
            display: flex;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-wrapper {
                padding: 1rem;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #6f42c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #5a32a3;
        }
    </style>
    
    <!-- Additional CSS -->
    <?= $this->renderSection('css') ?>
</head>
<body>
    
    <!-- Superadmin Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-shield-fill-check"></i>
            <span>SuperAdmin</span>
            <span class="admin-badge">FULL ACCESS</span>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="<?= base_url('superadmin/dashboard') ?>" class="<?= uri_string() == 'superadmin/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="mt-3">
                <div class="px-3 py-2 text-white-50 small text-uppercase">
                    <span>User Management</span>
                </div>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/users') ?>" class="<?= strpos(uri_string(), 'superadmin/users') !== false ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Users</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/roles') ?>" class="<?= strpos(uri_string(), 'superadmin/roles') !== false ? 'active' : '' ?>">
                    <i class="bi bi-shield-fill-check"></i>
                    <span>Roles & Permissions</span>
                </a>
            </li>
            
            <li class="mt-3">
                <div class="px-3 py-2 text-white-50 small text-uppercase">
                    <span>System</span>
                </div>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/applications') ?>" class="<?= strpos(uri_string(), 'superadmin/applications') !== false ? 'active' : '' ?>">
                    <i class="bi bi-app-indicator"></i>
                    <span>Applications</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/logs') ?>" class="<?= strpos(uri_string(), 'superadmin/logs') !== false ? 'active' : '' ?>">
                    <i class="bi bi-activity"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/reports') ?>" class="<?= strpos(uri_string(), 'superadmin/reports') !== false ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Reports</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('superadmin/settings') ?>" class="<?= strpos(uri_string(), 'superadmin/settings') !== false ? 'active' : '' ?>">
                    <i class="bi bi-gear-fill"></i>
                    <span>System Settings</span>
                </a>
            </li>
            
            <li class="mt-3">
                <div class="px-3 py-2 text-white-50 small text-uppercase">
                    <span>Account</span>
                </div>
            </li>
            
            <li>
                <a href="<?= base_url('profile') ?>">
                    <i class="bi bi-person-circle"></i>
                    <span>Profile</span>
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('logout') ?>" onclick="return confirm('Logout from admin panel?')">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        
        <!-- System Info in Sidebar -->
        <div class="p-3 mt-4" style="border-top: 1px solid rgba(255,255,255,0.2);">
            <small class="text-white-50">
                <i class="bi bi-info-circle"></i> System Admin Mode<br>
                <i class="bi bi-shield-check"></i> Full Access Granted
            </small>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="btn-toggle-sidebar" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                
                <div class="admin-mode-badge">
                    <i class="bi bi-shield-fill-check me-2"></i>
                    SUPERADMIN MODE
                </div>
            </div>
            
            <div class="topbar-right">
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell text-dark"></i>
                        <span class="badge bg-danger">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-muted" href="#">No new notifications</a></li>
                    </ul>
                </div>
                
                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle text-dark me-1"></i>
                        <span class="text-dark"><?= esc(session()->get('nama_lengkap')) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('superadmin/settings') ?>"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            
            <!-- Flash Messages -->
            <?= $this->include('components/flash_messages') ?>
            
            <!-- Page Content -->
            <?= $this->renderSection('content') ?>
            
        </div>
        
        <!-- Footer -->
        <footer class="text-center py-3 border-top">
            <small class="text-muted">
                &copy; <?= date('Y') ?> DataStat App - Superadmin Panel
            </small>
        </footer>
        
    </div>
    
    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="loadingSpinner">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Common JS -->
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        }
        
        function showLoading() {
            document.getElementById('loadingSpinner').classList.add('show');
        }
        
        function hideLoading() {
            document.getElementById('loadingSpinner').classList.remove('show');
        }
        
        $(document).ready(function() {
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });
            
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
            
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
        
        function confirmDelete(url, message = 'Apakah Anda yakin ingin menghapus data ini?') {
            Swal.fire({
                title: 'Konfirmasi Penghapusan',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    window.location.href = url;
                }
            });
        }
        
        // Admin action confirmation
        function confirmAdminAction(url, title, message) {
            Swal.fire({
                title: title,
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6f42c1',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    window.location.href = url;
                }
            });
        }
    </script>
    
    <!-- Additional JS -->
    <?= $this->renderSection('js') ?>
    
</body>
</html>