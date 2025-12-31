<?= $this->extend('layouts/viewer') ?>

<?= $this->section('content') ?>

<!-- Page Title -->
<div class="page-title mb-4">
    <h1><i class="bi bi-bar-chart-line-fill me-2"></i>Statistics</h1>
    <p class="text-muted mb-0">Workspace: <strong><?= esc(session()->get('app_name')) ?></strong></p>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Statistics</h6>
                        <h2 class="mb-0"><?= $stats['total_statistics'] ?? 0 ?></h2>
                        <small class="text-info">
                            <i class="bi bi-eye"></i> <?= $stats['active_statistics'] ?? 0 ?> active
                        </small>
                    </div>
                    <div class="stats-icon bg-primary text-white">
                        <i class="bi bi-bar-chart-line-fill"></i>
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
                        <h6 class="text-muted mb-1">Chart Types</h6>
                        <h2 class="mb-0"><?= $stats['chart_types'] ?? 0 ?></h2>
                        <small class="text-success">
                            <i class="bi bi-graph-up"></i> Different visualizations
                        </small>
                    </div>
                    <div class="stats-icon bg-success text-white">
                        <i class="bi bi-pie-chart-fill"></i>
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
                        <h6 class="text-muted mb-1">Most Popular</h6>
                        <h2 class="mb-0" style="font-size: 1.5rem;">
                            <?= $stats['most_viewed'] ?? 'N/A' ?>
                        </h2>
                        <small class="text-muted">
                            <i class="bi bi-star"></i> Most viewed statistic
                        </small>
                    </div>
                    <div class="stats-icon bg-warning text-white">
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search" class="form-label">Search Statistics</label>
                            <input type="text" class="form-control" id="search" placeholder="Search by name...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="metric_type" class="form-label">Metric Type</label>
                            <select class="form-select" id="metric_type">
                                <option value="">All Types</option>
                                <option value="count">Count</option>
                                <option value="sum">Sum</option>
                                <option value="average">Average</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="visualization_type" class="form-label">Visualization</label>
                            <select class="form-select" id="visualization_type">
                                <option value="">All Types</option>
                                <option value="bar">Bar Chart</option>
                                <option value="line">Line Chart</option>
                                <option value="pie">Pie Chart</option>
                                <option value="table">Table</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Grid -->
<div class="row" id="statistics-container">
    <?php if (!empty($statistics)): ?>
        <?php foreach ($statistics as $statistic): ?>
            <div class="col-lg-4 col-md-6 mb-4 statistic-item"
                data-metric-type="<?= esc($statistic['metric_type']) ?>"
                data-visualization-type="<?= esc($statistic['visualization_type']) ?>"
                data-name="<?= esc(strtolower($statistic['stat_name'])) ?>">

                <div class="card h-100 statistic-card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-graph-up-arrow text-success me-2"></i>
                            <?= esc($statistic['stat_name']) ?>
                        </h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="viewStatistic(<?= $statistic['id'] ?>)">
                                        <i class="bi bi-eye me-2"></i>View Details</a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportStatistic(<?= $statistic['id'] ?>)">
                                        <i class="bi bi-download me-2"></i>Export Data</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Chart Preview -->
                        <div class="chart-preview mb-3" style="height: 150px;">
                            <canvas id="chart-preview-<?= $statistic['id'] ?>"></canvas>
                        </div>

                        <!-- Statistic Info -->
                        <div class="statistic-info">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted">Type</small>
                                    <br>
                                    <span class="badge bg-info">
                                        <?= ucfirst($statistic['metric_type']) ?>
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Visualization</small>
                                    <br>
                                    <span class="badge bg-secondary">
                                        <?= ucfirst($statistic['visualization_type']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-eye me-1"></i>
                                <?= $statistic['view_count'] ?? 0 ?> views
                            </small>
                            <a href="<?= base_url('viewer/statistics/view/' . $statistic['id']) ?>" class="btn btn-sm btn-success">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Empty State -->
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-bar-chart-line text-muted mb-3" style="font-size: 5rem; opacity: 0.3;"></i>
                <h3>No Statistics Available</h3>
                <p class="text-muted mb-4">No statistics have been created for this workspace yet.</p>
                <p class="text-muted">Contact your workspace owner to create statistics.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if (!empty($statistics) && $pager): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-center">
                <?= $pager->links() ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Help Section -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="alert alert-info">
            <h5 class="alert-heading">
                <i class="bi bi-info-circle-fill me-2"></i>Statistics Overview
            </h5>
            <p class="mb-2">Browse and view statistical reports created for this workspace.</p>
            <hr>
            <p class="mb-0">
                <strong>Available Actions:</strong>
            <ul class="mb-0">
                <li>View detailed statistics with interactive charts</li>
                <li>Export statistical data (if enabled)</li>
                <li>Filter statistics by type and visualization</li>
            </ul>
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let statisticsData = <?= json_encode($statistics ?? []) ?>;
    let chartInstances = {};

    // Initialize page
    $(document).ready(function() {
        initializeStatistics();
    });

    function initializeStatistics() {
        // Initialize chart previews
        statisticsData.forEach(function(statistic) {
            initializeChartPreview(statistic);
        });

        // Setup search functionality
        $('#search').on('keyup', function() {
            filterStatistics();
        });

        $('#metric_type, #visualization_type').on('change', function() {
            filterStatistics();
        });
    }

    function initializeChartPreview(statistic) {
        const canvas = document.getElementById(`chart-preview-${statistic.id}`);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Sample data for preview (you would load real data via AJAX)
        const sampleData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            datasets: [{
                label: statistic.stat_name,
                data: [12, 19, 3, 5, 2],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 205, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        };

        const config = {
            type: statistic.visualization_type === 'pie' ? 'pie' : statistic.visualization_type === 'line' ? 'line' : 'bar',
            data: sampleData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: statistic.visualization_type !== 'pie' ? {
                    y: {
                        beginAtZero: true,
                        display: false
                    },
                    x: {
                        display: false
                    }
                } : {}
            }
        };

        chartInstances[statistic.id] = new Chart(ctx, config);
    }

    function filterStatistics() {
        const searchTerm = $('#search').val().toLowerCase();
        const metricType = $('#metric_type').val();
        const visualizationType = $('#visualization_type').val();

        $('.statistic-item').each(function() {
            const $item = $(this);
            const name = $item.data('name');
            const itemMetricType = $item.data('metric-type');
            const itemVisualizationType = $item.data('visualization-type');

            const matchesSearch = !searchTerm || name.includes(searchTerm);
            const matchesMetric = !metricType || itemMetricType === metricType;
            const matchesVisualization = !visualizationType || itemVisualizationType === visualizationType;

            if (matchesSearch && matchesMetric && matchesVisualization) {
                $item.show();
            } else {
                $item.hide();
            }
        });
    }

    function applyFilters() {
        filterStatistics();
    }

    function viewStatistic(statisticId) {
        window.location.href = `<?= base_url('viewer/statistics/view/') ?>${statisticId}`;
    }

    function exportStatistic(statisticId) {
        // Implement export functionality
        window.open(`<?= base_url('viewer/statistics/export/') ?>${statisticId}`, '_blank');
    }

    // Clean up charts on page unload
    $(window).on('beforeunload', function() {
        Object.values(chartInstances).forEach(chart => {
            if (chart) {
                chart.destroy();
            }
        });
    });
</script>
<?= $this->endSection() ?>