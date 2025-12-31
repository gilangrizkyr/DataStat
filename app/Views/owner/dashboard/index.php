<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>

<!-- Page Title -->
<div class="page-title mb-4">
    <h1><i class="bi bi-house-door-fill me-2"></i>Owner Dashboard</h1>
    <p class="text-muted mb-0">Workspace: <strong><?= esc(session()->get('app_name')) ?></strong></p>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Datasets</h6>
                        <h2 class="mb-0"><?= $stats['total_datasets'] ?? 0 ?></h2>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i> <?= $stats['new_datasets_this_month'] ?? 0 ?> this month
                        </small>
                    </div>
                    <div class="stats-icon bg-primary text-white">
                        <i class="bi bi-database-fill"></i>
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
                        <h6 class="text-muted mb-1">Statistics Created</h6>
                        <h2 class="mb-0"><?= $stats['total_statistics'] ?? 0 ?></h2>
                        <small class="text-success">
                            <i class="bi bi-check-circle"></i> <?= $stats['active_statistics'] ?? 0 ?> active
                        </small>
                    </div>
                    <div class="stats-icon bg-success text-white">
                        <i class="bi bi-graph-up-arrow"></i>
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
                        <h6 class="text-muted mb-1">Dashboards</h6>
                        <h2 class="mb-0"><?= $stats['total_dashboards'] ?? 0 ?></h2>
                        <small class="text-info">
                            <i class="bi bi-share"></i> <?= $stats['public_dashboards'] ?? 0 ?> public
                        </small>
                    </div>
                    <div class="stats-icon bg-warning text-white">
                        <i class="bi bi-layout-text-window-reverse"></i>
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
                        <h6 class="text-muted mb-1">Team Members</h6>
                        <h2 class="mb-0"><?= $stats['team_members'] ?? 0 ?></h2>
                        <small class="text-muted">
                            <i class="bi bi-people"></i> <?= $stats['viewers'] ?? 0 ?> viewers
                        </small>
                    </div>
                    <div class="stats-icon bg-danger text-white">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
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
                        <a href="<?= base_url('owner/datasets/upload') ?>" class="btn btn-outline-primary btn-lg w-100">
                            <i class="bi bi-cloud-upload-fill d-block mb-2" style="font-size: 2rem;"></i>
                            <strong>Upload Dataset</strong>
                            <small class="d-block text-muted">Import Excel/CSV data</small>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('owner/statistics/create') ?>" class="btn btn-outline-success btn-lg w-100">
                            <i class="bi bi-graph-up-arrow d-block mb-2" style="font-size: 2rem;"></i>
                            <strong>Create Statistic</strong>
                            <small class="d-block text-muted">Build custom statistics</small>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('owner/dashboards/create') ?>" class="btn btn-outline-warning btn-lg w-100">
                            <i class="bi bi-layout-text-window-reverse d-block mb-2" style="font-size: 2rem;"></i>
                            <strong>New Dashboard</strong>
                            <small class="d-block text-muted">Design your dashboard</small>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('owner/users/invite') ?>" class="btn btn-outline-info btn-lg w-100">
                            <i class="bi bi-person-plus-fill d-block mb-2" style="font-size: 2rem;"></i>
                            <strong>Invite User</strong>
                            <small class="d-block text-muted">Add team members</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Two Columns Layout -->
<div class="row">
    
    <!-- Recent Datasets -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-database me-2"></i>Recent Datasets</h5>
                <a href="<?= base_url('owner/datasets') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_datasets)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_datasets as $dataset): ?>
                            <a href="<?= base_url('owner/datasets/view/' . $dataset['id']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= esc($dataset['dataset_name']) ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-file-earmark-spreadsheet"></i> 
                                            <?= number_format($dataset['total_rows']) ?> rows
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $dataset['upload_status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($dataset['upload_status']) ?>
                                        </span>
                                        <small class="d-block text-muted mt-1">
                                            <?= date('M d, Y', strtotime($dataset['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-database text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No datasets yet</p>
                        <a href="<?= base_url('owner/datasets/upload') ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus"></i> Upload First Dataset
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Statistics -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Recent Statistics</h5>
                <a href="<?= base_url('owner/statistics') ?>" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_statistics)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_statistics as $statistic): ?>
                            <a href="<?= base_url('owner/statistics/view/' . $statistic['id']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= esc($statistic['stat_name']) ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-tag"></i> <?= ucfirst($statistic['metric_type']) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $statistic['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $statistic['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                        <small class="d-block text-muted mt-1">
                                            <?= date('M d', strtotime($statistic['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-graph-up-arrow text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No statistics yet</p>
                        <a href="<?= base_url('owner/statistics/create') ?>" class="btn btn-success btn-sm">
                            <i class="bi bi-plus"></i> Create First Statistic
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>

<!-- Recent Activity -->
<?php if (!empty($recent_activity)): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Activity</th>
                                <th>Module</th>
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
                                    <small><?= date('M d, H:i', strtotime($activity['created_at'])) ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    // Auto-refresh stats every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
</script>
<?= $this->endSection() ?>