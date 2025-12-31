<?= $this->extend('layouts/viewer') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-th-large me-2"></i>All Dashboards</h2>
            <p class="text-muted">View all available dashboards in this workspace</p>
        </div>
    </div>
</div>

<!-- Viewer Notice -->
<div class="viewer-notice mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Read-Only Access:</strong> You can view dashboards but cannot create or modify them.
</div>

<?php if (empty($dashboards)): ?>
    <!-- Empty State -->
    <div class="text-center py-5">
        <i class="fas fa-th-large text-muted mb-3" style="font-size: 5rem; opacity: 0.3;"></i>
        <h3>No Dashboards Available</h3>
        <p class="text-muted mb-4">There are no dashboards available in this workspace yet.</p>
        <a href="<?= base_url('viewer/dashboard') ?>" class="btn btn-primary">
            <i class="fas fa-home me-2"></i>Go to Default Dashboard
        </a>
    </div>
<?php else: ?>
    <!-- Dashboards Grid -->
    <div class="row">
        <?php foreach ($dashboards as $dashboard): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            <?= esc($dashboard['dashboard_name']) ?>
                        </h5>
                        <?php if ($dashboard['is_default']): ?>
                            <span class="badge bg-success">Default</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($dashboard['description'])): ?>
                        <p class="card-text text-muted small mb-3">
                            <?= esc($dashboard['description']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                <?= esc($dashboard['creator_name']) ?>
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-chart-bar me-1"></i>
                                <?= $dashboard['widget_count'] ?? 0 ?> widgets
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="<?= base_url('viewer/dashboard/view/' . $dashboard['id']) ?>"
                               class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                            <a href="<?= base_url('viewer/dashboard/view/' . $dashboard['id']) ?>?fullscreen=1"
                               class="btn btn-outline-secondary btn-sm"
                               title="Fullscreen View">
                                <i class="fas fa-expand"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        Created <?= date('M j, Y', strtotime($dashboard['created_at'])) ?>
                    </small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
// Auto-refresh functionality (optional)
$(document).ready(function() {
    // Add any dashboard list specific JavaScript here
});
</script>
<?= $this->endSection() ?>
