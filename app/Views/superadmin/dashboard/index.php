<?= $this->extend('layouts/superadmin') ?>
<?= $this->section('content') ?>

<!-- Page Title -->
<div class="page-title mb-4">
    <h1><i class="bi bi-shield-fill-check me-2"></i>Superadmin Dashboard</h1>
    <p class="text-muted mb-0">System Overview & Management</p>
</div>

<!-- Main Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Users</h6>
                        <h2 class="mb-0"><?= $stats['total_users'] ?? 0 ?></h2>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i> <?= $stats['new_users_this_month'] ?? 0 ?> this month
                        </small>
                    </div>
                    <div class="stats-icon bg-primary text-white">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Applications</h6>
                        <h2 class="mb-0"><?= $stats['total_applications'] ?? 0 ?></h2>
                        <small class="text-info">
                            <i class="bi bi-check-circle"></i> <?= $stats['active_applications'] ?? 0 ?> active
                        </small>
                    </div>
                    <div class="stats-icon bg-success text-white">
                        <i class="bi bi-app-indicator"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Active Sessions</h6>
                        <h2 class="mb-0"><?= $stats['active_sessions'] ?? 0 ?></h2>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> Online now
                        </small>
                    </div>
                    <div class="stats-icon bg-warning text-white">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Today's Activity</h6>
                        <h2 class="mb-0"><?= $stats['today_activity'] ?? 0 ?></h2>
                        <small class="text-muted">
                            <i class="bi bi-activity"></i> Events logged
                        </small>
                    </div>
                    <div class="stats-icon bg-danger text-white">
                        <i class="bi bi-activity"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Total Datasets</small>
                        <h4 class="mb-0"><?= $stats['total_datasets'] ?? 0 ?></h4>
                    </div>
                    <i class="bi bi-database text-primary" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Total Statistics</small>
                        <h4 class="mb-0"><?= $stats['total_statistics'] ?? 0 ?></h4>
                    </div>
                    <i class="bi bi-graph-up text-success" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Total Dashboards</small>
                        <h4 class="mb-0"><?= $stats['total_dashboards'] ?? 0 ?></h4>
                    </div>
                    <i class="bi bi-layout-text-window text-warning" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Storage Used</small>
                        <h4 class="mb-0"><?= $stats['storage_used'] ?? '0 MB' ?></h4>
                    </div>
                    <i class="bi bi-hdd text-info" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning-fill me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('superadmin/users/create') ?>" class="btn btn-outline-primary btn-lg w-100">
                            <i class="bi bi-person-plus-fill d-block mb-2" style="font-size: 2rem;"></i>
                            <strong>Add User</strong>
                            <small class="d-block text-muted">Create new user account</small>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('superadmin/applications') ?>" class="btn btn-outline-success btn-lg w-100">
                            <i class="bi bi-app-indicator d-block mb-2" style="font-size: 2rem;"></i>
                            <strong>View Applications</strong>
                            <small class="d-block text-muted">Manage workspaces</small>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('superadmin/logs') ?>" class="btn btn-outline-warning btn-lg w-100">
                            <i class="bi bi-activity d-block mb-2" style="font-size: 2rem;"></i>
                            <strong>Activity Logs</strong>
                            <small class="d-block text-muted">View system logs</small>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('superadmin/reports') ?>" class="btn btn-outline-info btn-lg w-100">
                            <i class="bi bi-file-earmark-bar-graph d-block mb-2" style="font-size: 2rem;"></i>
                            <strong>Generate Report</strong>
                            <small class="d-block text-muted">System analytics</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- User Growth Chart -->
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>User Growth (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="userGrowthChart" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Activity Distribution -->
    <div class="col-lg-4 mb-3">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Activity by Type</h5>
            </div>
            <div class="card-body">
                <canvas id="activityPieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Two Columns -->
<div class="row">
    
    <!-- Recent Users -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Recent Users</h5>
                <a href="<?= base_url('superadmin/users') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_users)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_users as $user): ?>
                            <a href="<?= base_url('superadmin/users/view/' . $user['id']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= esc($user['nama_lengkap']) ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i> <?= esc($user['email']) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                        <small class="d-block text-muted mt-1">
                                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No recent users</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Applications -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-app me-2"></i>Recent Applications</h5>
                <a href="<?= base_url('superadmin/applications') ?>" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_applications)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_applications as $app): ?>
                            <a href="<?= base_url('superadmin/applications/view/' . $app['id']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= esc($app['app_name']) ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-person"></i> Owner: <?= esc($app['owner_name']) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $app['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $app['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                        <small class="d-block text-muted mt-1">
                                            <?= date('M d', strtotime($app['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No applications yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>

<!-- System Activity Log -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent System Activity</h5>
                <a href="<?= base_url('superadmin/logs') ?>" class="btn btn-sm btn-outline-warning">View All Logs</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_activity)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>Module</th>
                                    <th>IP Address</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_activity as $activity): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($activity['user_name'] ?? 'System') ?></strong>
                                    </td>
                                    <td><?= esc($activity['description']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= esc(ucfirst($activity['module'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        <small><?= esc($activity['ip_address']) ?></small>
                                    </td>
                                    <td class="text-muted">
                                        <small><?= date('M d, H:i', strtotime($activity['created_at'])) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No recent activity</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
// User Growth Chart
<?php if (!empty($chart_data['user_growth'])): ?>
const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
new Chart(userGrowthCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_data['user_growth']['labels']) ?>,
        datasets: [{
            label: 'New Users',
            data: <?= json_encode($chart_data['user_growth']['data']) ?>,
            borderColor: 'rgba(13, 110, 253, 1)',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
<?php endif; ?>

// Activity Pie Chart
<?php if (!empty($chart_data['activity_distribution'])): ?>
const activityPieCtx = document.getElementById('activityPieChart').getContext('2d');
new Chart(activityPieCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($chart_data['activity_distribution']['labels']) ?>,
        datasets: [{
            data: <?= json_encode($chart_data['activity_distribution']['data']) ?>,
            backgroundColor: [
                'rgba(13, 110, 253, 0.8)',
                'rgba(25, 135, 84, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(13, 202, 240, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
<?= $this->endSection() ?>