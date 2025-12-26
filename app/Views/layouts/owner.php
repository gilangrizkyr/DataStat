<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?? 'Owner Dashboard' ?> - DataStat App</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Font Awesome for statistics icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">

    <!-- Custom CSS for Owner -->
    <style>
        :root {
            --owner-primary: #0d6efd;
            --owner-secondary: #6c757d;
            --owner-accent: #198754;
            --sidebar-width: 260px;
            --topbar-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        /* Owner Sidebar - Green Theme */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #198754 0%, #157347 100%);
            color: white;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-brand {
            padding: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
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
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: rgba(255, 255, 255, 0.15);
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

        .sidebar.collapsed~.main-content {
            margin-left: 70px;
        }

        /* Topbar - Owner Theme */
        .topbar {
            height: var(--topbar-height);
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 3px solid #198754;
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
            color: #198754;
        }

        /* Workspace Badge */
        .workspace-badge {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
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

        /* Cards - Owner Style */
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            border-left: 4px solid #198754;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }

        /* Stats Card - Owner */
        .stats-card {
            border-left: 4px solid #198754;
        }

        .stats-card.success {
            border-left-color: #0dcaf0;
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
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
        }

        /* Buttons - Owner Theme */
        .btn-primary {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #157347 0%, #146c43 100%);
        }

        /* Page Title */
        .page-title {
            margin-bottom: 1.5rem;
            border-left: 5px solid #198754;
            padding-left: 1rem;
        }

        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
            color: #198754;
        }

        /* Loading Spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(25, 135, 84, 0.5);
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
            background: #198754;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #157347;
        }
    </style>

    <!-- Additional CSS -->
    <?= $this->renderSection('css') ?>
</head>

<body>

    <!-- Owner Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-briefcase-fill"></i>
            <span>Owner Panel</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="<?= base_url('owner/dashboard') ?>" class="<?= uri_string() == 'owner/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="mt-3">
                <div class="px-3 py-2 text-white-50 small text-uppercase">
                    <span>Data Management</span>
                </div>
            </li>

            <li>
                <a href="<?= base_url('owner/datasets') ?>" class="<?= strpos(uri_string(), 'owner/datasets') !== false ? 'active' : '' ?>">
                    <i class="bi bi-database-fill"></i>
                    <span>Datasets</span>
                </a>
            </li>

            <li>
                <a href="<?= base_url('owner/statistics') ?>" class="<?= strpos(uri_string(), 'owner/statistics') !== false ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Statistics</span>
                </a>
            </li>

            <li>
                <a href="<?= base_url('owner/dashboards') ?>" class="<?= strpos(uri_string(), 'owner/dashboards') !== false ? 'active' : '' ?>">
                    <i class="bi bi-layout-text-window-reverse"></i>
                    <span>Dashboards</span>
                </a>
            </li>

            <li class="mt-3">
                <div class="px-3 py-2 text-white-50 small text-uppercase">
                    <span>Team</span>
                </div>
            </li>

            <li>
                <a href="<?= base_url('owner/users') ?>" class="<?= strpos(uri_string(), 'owner/users') !== false ? 'active' : '' ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Team Members</span>
                </a>
            </li>

            <li>
                <a href="<?= base_url('owner/settings') ?>" class="<?= strpos(uri_string(), 'owner/settings') !== false ? 'active' : '' ?>">
                    <i class="bi bi-sliders"></i>
                    <span>Settings</span>
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
                <a href="<?= base_url('logout') ?>" onclick="return confirm('Logout?')">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="btn-toggle-sidebar" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>

                <div class="workspace-badge">
                    <i class="bi bi-building me-2"></i>
                    <?= esc(session()->get('app_name') ?? 'Workspace') ?>
                </div>
            </div>

            <div class="topbar-right">
                <?= $this->include('components/topbar') ?>
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
                &copy; <?= date('Y') ?> DataStat App - Owner Panel
            </small>
        </footer>

    </div>

    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="loadingSpinner">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- jQuery (moved to head for compatibility) -->
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
                title: 'Konfirmasi',
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
    </script>

    <!-- Additional JS -->
    <?= $this->renderSection('js') ?>

</body>

</html>