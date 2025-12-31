<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Title -->
<div class="page-title mb-4">
    <h1><i class="bi bi-gear-fill me-2"></i>Setup Your Workspace</h1>
    <p class="text-muted mb-0">Welcome! Let's get you started by creating your first application.</p>
</div>

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-body text-center py-5">
                <i class="bi bi-rocket-takeoff-fill text-primary" style="font-size: 4rem;"></i>
                <h3 class="mt-3">Welcome to DataStat!</h3>
                <p class="text-muted mb-4">Create your first workspace to start building amazing data visualizations and statistics.</p>
                <a href="<?= base_url('owner/application/create') ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Create Your First Application
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Features Overview -->
<div class="row mb-4">
    <div class="col-md-12">
        <h4 class="mb-3">What you can do with DataStat:</h4>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-spreadsheet-fill text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Upload Datasets</h5>
                <p class="text-muted">Import Excel, CSV, and other data formats to create rich datasets for analysis.</p>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-graph-up-arrow text-info" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Create Statistics</h5>
                <p class="text-muted">Build custom statistics with our visual statistic builder - no coding required.</p>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-layout-text-window-reverse text-warning" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Design Dashboards</h5>
                <p class="text-muted">Create beautiful, interactive dashboards to showcase your data insights.</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Start Guide -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Quick Start Guide</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <div class="step-circle bg-primary text-white mx-auto mb-2">1</div>
                        <h6>Create Application</h6>
                        <small class="text-muted">Set up your workspace</small>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div class="step-circle bg-secondary text-white mx-auto mb-2">2</div>
                        <h6>Upload Data</h6>
                        <small class="text-muted">Import your datasets</small>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div class="step-circle bg-secondary text-white mx-auto mb-2">3</div>
                        <h6>Build Statistics</h6>
                        <small class="text-muted">Create visualizations</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Applications (if any) -->
<?php if ($has_pending): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Pending Applications</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">You have application(s) waiting for approval:</p>
                    <div class="list-group">
                        <?php foreach ($pending_applications as $app): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= esc($app['app_name']) ?></h6>
                                        <small class="text-muted">Created: <?= date('M d, Y', strtotime($app['created_at'])) ?></small>
                                    </div>
                                    <span class="badge bg-warning">Pending Approval</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Your applications will be reviewed by a superadmin before activation.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Help Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6>Need Help?</h6>
                <p class="text-muted mb-2">Check out our documentation or contact support.</p>
                <div class="btn-group">
                    <a href="#" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-book me-1"></i>Documentation
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-chat-dots me-1"></i>Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style>
<?= $this->endSection() ?>