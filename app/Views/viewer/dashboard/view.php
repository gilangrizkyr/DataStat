<?= $this->extend('layouts/viewer') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-title mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-layout-text-window-reverse me-2"></i><?= esc($dashboard['dashboard_name']) ?></h1>
            <?php if (!empty($dashboard['description'])): ?>
                <p class="text-muted mb-0"><?= esc($dashboard['description']) ?></p>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('viewer/dashboard/view/' . $dashboard['id']) ?>?fullscreen=1"
               class="btn btn-outline-secondary" target="_blank">
                <i class="bi bi-fullscreen me-1"></i>Fullscreen
            </a>
            <a href="<?= base_url('viewer/dashboards') ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>
</div>

<!-- Viewer Notice -->
<div class="viewer-notice mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Read-Only View:</strong> You can view this dashboard but cannot modify it.
</div>

<!-- Dashboard Content -->
<?php if (!empty($widgets)): ?>
    <div class="dashboard-grid" id="dashboard-grid">
        <?php foreach ($widgets as $widget): ?>
        <div class="dashboard-widget"
             data-widget-id="<?= $widget['id'] ?>"
             data-widget-type="<?= $widget['widget_type'] ?>"
             data-position-x="<?= $widget['position_x'] ?>"
             data-position-y="<?= $widget['position_y'] ?>"
             data-width="<?= $widget['width'] ?>"
             data-height="<?= $widget['height'] ?>">

            <div class="card h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-<?php
                            $icons = [
                                'chart' => 'bar-chart-line',
                                'table' => 'table',
                                'metric' => 'speedometer2',
                                'text' => 'card-text',
                                'image' => 'image',
                                'map' => 'geo-alt'
                            ];
                            echo $icons[$widget['widget_type']] ?? 'square';
                        ?> me-2"></i>
                        <?= esc($widget['widget_title']) ?>
                    </h6>
                    <div class="widget-controls">
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshWidget(<?= $widget['id'] ?>)">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="widget-content" id="widget-content-<?= $widget['id'] ?>">
                        <!-- Widget content will be loaded here -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading widget...</p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($widget['last_updated'])): ?>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Last updated: <?= date('M d, Y H:i', strtotime($widget['last_updated'])) ?>
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Empty Dashboard -->
    <div class="text-center py-5">
        <i class="bi bi-layout-text-window-reverse text-muted mb-3" style="font-size: 5rem; opacity: 0.3;"></i>
        <h3>No Widgets Available</h3>
        <p class="text-muted mb-4">This dashboard doesn't have any widgets configured yet.</p>
        <a href="<?= base_url('viewer/dashboards') ?>" class="btn btn-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard List
        </a>
    </div>
<?php endif; ?>

<!-- Dashboard Info -->
<?php if (!empty($dashboard)): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Dashboard Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Created by:</strong> <?= esc($dashboard['creator_name']) ?></p>
                        <p><strong>Created:</strong> <?= date('M d, Y H:i', strtotime($dashboard['created_at'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Last modified:</strong> <?= date('M d, Y H:i', strtotime($dashboard['updated_at'])) ?></p>
                        <p><strong>Status:</strong>
                            <span class="badge bg-<?= $dashboard['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $dashboard['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.dashboard-widget {
    min-height: 300px;
}

.widget-content {
    min-height: 200px;
    position: relative;
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
let widgetsData = <?= json_encode($widgets ?? []) ?>;
let dashboardId = <?= $dashboard['id'] ?? 'null' ?>;

// Initialize dashboard when page loads
$(document).ready(function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Load widget content
    widgetsData.forEach(function(widget) {
        loadWidgetContent(widget.id);
    });

    // Auto-refresh widgets every 5 minutes
    setInterval(function() {
        widgetsData.forEach(function(widget) {
            if (widget.auto_refresh) {
                loadWidgetContent(widget.id);
            }
        });
    }, 300000); // 5 minutes
}

function loadWidgetContent(widgetId) {
    const widgetElement = $(`#widget-content-${widgetId}`);

    // Show loading state
    widgetElement.html(`
        <div class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted small">Loading...</p>
        </div>
    `);

    // Load widget content via AJAX
    $.ajax({
        url: `<?= base_url('viewer/dashboard/widget/') ?>${widgetId}`,
        method: 'GET',
        success: function(response) {
            widgetElement.html(response.html);
            initializeWidgetCharts(widgetId);
        },
        error: function(xhr, status, error) {
            widgetElement.html(`
                <div class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle mb-2" style="font-size: 2rem;"></i>
                    <p>Failed to load widget content</p>
                    <small class="text-muted">${error}</small>
                </div>
            `);
        }
    });
}

function refreshWidget(widgetId) {
    loadWidgetContent(widgetId);
}

function initializeWidgetCharts(widgetId) {
    // Initialize any charts in the widget
    const widgetElement = $(`#widget-content-${widgetId}`);

    // Look for canvas elements and initialize charts
    widgetElement.find('canvas').each(function() {
        const canvas = $(this);
        const chartType = canvas.data('chart-type');
        const chartData = canvas.data('chart-data');

        if (chartType && chartData) {
            new Chart(canvas, {
                type: chartType,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });
}

function getWidgetIcon(widgetType) {
    const icons = {
        'chart': 'bar-chart-line',
        'table': 'table',
        'metric': 'speedometer2',
        'text': 'card-text',
        'image': 'image',
        'map': 'geo-alt'
    };
    return icons[widgetType] || 'square';
}

// Handle fullscreen mode
if (window.location.search.includes('fullscreen=1')) {
    $(document).ready(function() {
        // Hide sidebar and adjust layout for fullscreen
        $('.sidebar').hide();
        $('.main-content').css('margin-left', '0');
        $('.topbar').hide();
        $('.page-title').hide();
        $('.viewer-notice').hide();
    });
}
</script>
<?= $this->endSection() ?>
