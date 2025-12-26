<?= $this->extend('layouts/viewer') ?>

<?= $this->section('content') ?>

<!-- Page Title -->
<div class="page-title mb-4">
    <h1><i class="bi bi-grid-3x3-gap-fill me-2"></i>Viewer Dashboard</h1>
    <p class="text-muted mb-0">Workspace: <strong><?= esc(session()->get('app_name')) ?></strong></p>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Available Dashboards</h6>
                        <h2 class="mb-0"><?= $stats['total_dashboards'] ?? 0 ?></h2>
                        <small class="text-info">
                            <i class="bi bi-eye"></i> <?= $stats['public_dashboards'] ?? 0 ?> public
                        </small>
                    </div>
                    <div class="stats-icon bg-primary text-white">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Statistics Available</h6>
                        <h2 class="mb-0"><?= $stats['total_statistics'] ?? 0 ?></h2>
                        <small class="text-success">
                            <i class="bi bi-check-circle"></i> <?= $stats['active_statistics'] ?? 0 ?> active
                        </small>
                    </div>
                    <div class="stats-icon bg-success text-white">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Last Access</h6>
                        <h2 class="mb-0" style="font-size: 1.5rem;">
                            <?= date('H:i', strtotime($stats['last_access'] ?? 'now')) ?>
                        </h2>
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> <?= date('M d, Y') ?>
                        </small>
                    </div>
                    <div class="stats-icon bg-warning text-white">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning-fill me-2"></i>Quick Access</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <a href="<?= base_url('viewer/dashboards') ?>" class="btn btn-outline-primary btn-lg w-100 py-4">
                            <i class="bi bi-grid-3x3-gap-fill d-block mb-2" style="font-size: 3rem;"></i>
                            <strong class="d-block mb-1">View Dashboards</strong>
                            <small class="text-muted">Browse all available dashboards</small>
                        </a>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <a href="<?= base_url('viewer/statistics') ?>" class="btn btn-outline-success btn-lg w-100 py-4">
                            <i class="bi bi-bar-chart-line-fill d-block mb-2" style="font-size: 3rem;"></i>
                            <strong class="d-block mb-1">View Statistics</strong>
                            <small class="text-muted">Explore statistical reports</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured/Pinned Dashboards -->
<?php if (!empty($featured_dashboards)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-star-fill me-2"></i>Featured Dashboards</h5>
                <a href="<?= base_url('viewer/dashboards') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($featured_dashboards as $dashboard): ?>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card h-100 border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-layout-text-window-reverse text-primary me-2"></i>
                                        <?= esc($dashboard['dashboard_name']) ?>
                                    </h5>
                                    <?php if ($dashboard['is_default']): ?>
                                        <span class="badge bg-warning">Default</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($dashboard['description']): ?>
                                    <p class="card-text text-muted small">
                                        <?= esc(substr($dashboard['description'], 0, 100)) ?>
                                        <?= strlen($dashboard['description']) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-grid-3x2"></i> <?= $dashboard['widget_count'] ?? 0 ?> widgets
                                    </small>
                                    <a href="<?= base_url('viewer/dashboards/view/' . $dashboard['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recently Viewed -->
<?php if (!empty($recently_viewed)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recently Viewed</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($recently_viewed as $item): ?>
                    <a href="<?= base_url($item['url']) ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <i class="bi bi-<?= $item['type'] === 'dashboard' ? 'grid-3x3-gap' : 'bar-chart-line' ?>"></i>
                                    <?= esc($item['name']) ?>
                                </h6>
                                <small class="text-muted">
                                    <?= ucfirst($item['type']) ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">
                                    <?= date('M d, H:i', strtotime($item['viewed_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Popular Statistics -->
<?php if (!empty($popular_statistics)): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Popular Statistics</h5>
                <a href="<?= base_url('viewer/statistics') ?>" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($popular_statistics as $statistic): ?>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card h-100 border">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-graph-up-arrow text-success me-1"></i>
                                    <?= esc($statistic['stat_name']) ?>
                                </h6>
                                
                                <div class="mb-2">
                                    <span class="badge bg-info">
                                        <?= ucfirst($statistic['metric_type']) ?>
                                    </span>
                                    <span class="badge bg-secondary">
                                        <?= ucfirst($statistic['visualization_type']) ?>
                                    </span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-eye"></i> <?= $statistic['view_count'] ?? 0 ?> views
                                    </small>
                                    <a href="<?= base_url('viewer/statistics/view/' . $statistic['id']) ?>" class="btn btn-sm btn-success">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Help Section -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="alert alert-info">
            <h5 class="alert-heading">
                <i class="bi bi-info-circle-fill me-2"></i>Need Help?
            </h5>
            <p class="mb-2">As a Viewer, you have read-only access to dashboards and statistics in this workspace.</p>
            <hr>
            <p class="mb-0">
                <strong>Available Actions:</strong>
                <ul class="mb-0">
                    <li>View all dashboards and statistics</li>
                    <li>Export data (if enabled)</li>
                    <li>Share public dashboard links</li>
                </ul>
            </p>
            <p class="mb-0 mt-2">
                Contact your workspace owner if you need additional permissions or have questions.
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    // Track viewed items
    function trackView(type, id) {
        // Could send AJAX to track views
        console.log('Tracked view:', type, id);
    }
</script>
<?= $this->endSection() ?>