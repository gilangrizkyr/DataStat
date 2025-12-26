<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>
                        Preview Statistik: <?= esc($statistic['stat_name']) ?>
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('owner/statistics/detail/' . $statistic['id']) ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                        <a href="<?= base_url('owner/statistics/edit/' . $statistic['id']) ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?= base_url('owner/statistics/builder/' . $statistic['id']) ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-cogs"></i> Builder
                        </a>
                        <a href="<?= base_url('owner/statistics') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="icon fas fa-check"></i> <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="icon fas fa-ban"></i> <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Statistic Info -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle"></i> Informasi Statistik
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nama:</strong> <?= esc($statistic['stat_name']) ?></p>
                                            <p><strong>Dataset:</strong> <?= esc($statistic['dataset_name']) ?></p>
                                            <p><strong>Tipe Metrik:</strong>
                                                <span class="badge badge-info">
                                                    <?= ucfirst(str_replace('_', ' ', $statistic['metric_type'])) ?>
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Visualisasi:</strong>
                                                <span class="badge badge-secondary">
                                                    <?= ucfirst(str_replace('_', ' ', $statistic['visualization_type'])) ?>
                                                </span>
                                            </p>
                                            <p><strong>Status:</strong>
                                                <?php if ($statistic['is_active']): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </p>
                                            <?php if ($statistic['target_field']): ?>
                                                <p><strong>Field Target:</strong> <?= esc($statistic['target_field']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($statistic['description']): ?>
                                        <div class="mt-3">
                                            <strong>Deskripsi:</strong>
                                            <p class="mb-0"><?= esc($statistic['description']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock"></i> Sistem
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="small mb-1"><strong>Dibuat:</strong></p>
                                    <p class="mb-2"><?= date('d/m/Y H:i:s', strtotime($statistic['created_at'])) ?></p>

                                    <p class="small mb-1"><strong>Terakhir Update:</strong></p>
                                    <p class="mb-2"><?= date('d/m/Y H:i:s', strtotime($statistic['updated_at'])) ?></p>

                                    <?php if ($statistic['last_calculated']): ?>
                                        <p class="small mb-1"><strong>Terakhir Dihitung:</strong></p>
                                        <p class="mb-0"><?= date('d/m/Y H:i:s', strtotime($statistic['last_calculated'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Result -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-line"></i> Preview Hasil Perhitungan
                                    </h5>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-success btn-sm" id="recalculateBtn">
                                            <i class="fas fa-sync-alt"></i> Hitung Ulang
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" id="fullscreenBtn">
                                            <i class="fas fa-expand"></i> Fullscreen
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if ($result && isset($result['success']) && $result['success']): ?>
                                        <div id="visualization-container">
                                            <?php if ($statistic['visualization_type'] === 'table'): ?>
                                                <!-- Table Visualization -->
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <?php if (isset($result['data'][0])): ?>
                                                                    <?php foreach (array_keys($result['data'][0]) as $header): ?>
                                                                        <th><?= esc($header) ?></th>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($result['data'] as $row): ?>
                                                                <tr>
                                                                    <?php foreach ($row as $value): ?>
                                                                        <td><?= esc($value) ?></td>
                                                                    <?php endforeach; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>

                                            <?php elseif ($statistic['visualization_type'] === 'kpi_card'): ?>
                                                <!-- KPI Card Visualization -->
                                                <div class="row">
                                                    <div class="col-md-4 offset-md-4">
                                                        <div class="card text-center bg-primary text-white">
                                                            <div class="card-body">
                                                                <h2 class="card-title mb-0">
                                                                    <?php
                                                                    if (isset($result['data']['value'])) {
                                                                        echo esc($result['data']['value']);
                                                                    } elseif (isset($result['data'][0]['value'])) {
                                                                        echo esc($result['data'][0]['value']);
                                                                    } else {
                                                                        echo 'N/A';
                                                                    }
                                                                    ?>
                                                                </h2>
                                                                <p class="card-text">
                                                                    <?= ucfirst(str_replace('_', ' ', $statistic['metric_type'])) ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php elseif (in_array($statistic['visualization_type'], ['bar_chart', 'pie_chart', 'line_chart', 'area_chart', 'donut_chart', 'scatter_chart'])): ?>
                                                <!-- Chart Visualization -->
                                                <div class="chart-container">
                                                    <canvas id="statisticChart" width="400" height="300"></canvas>
                                                </div>

                                            <?php elseif ($statistic['visualization_type'] === 'progress_bar'): ?>
                                                <!-- Progress Bar Visualization -->
                                                <div class="progress" style="height: 30px;">
                                                    <?php
                                                    $progress = 0;
                                                    if (isset($result['data']['percentage'])) {
                                                        $progress = min(100, max(0, $result['data']['percentage']));
                                                    }
                                                    ?>
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: <?= $progress ?>%"
                                                        aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= number_format($progress, 1) ?>%
                                                    </div>
                                                </div>

                                            <?php else: ?>
                                                <!-- Default: JSON Display -->
                                                <pre class="bg-light p-3 rounded"><code><?= json_encode($result['data'], JSON_PRETTY_PRINT) ?></code></pre>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Raw Data Toggle -->
                                        <div class="mt-3">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-toggle="collapse" data-target="#rawData" aria-expanded="false">
                                                <i class="fas fa-code"></i> Lihat Data Mentah
                                            </button>
                                            <div class="collapse mt-2" id="rawData">
                                                <pre class="bg-light p-3 rounded small"><code><?= json_encode($result, JSON_PRETTY_PRINT) ?></code></pre>
                                            </div>
                                        </div>

                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                            <h4 class="text-muted">Data Tidak Tersedia</h4>
                                            <p class="text-muted">
                                                <?php if (isset($result['message'])): ?>
                                                    <?= esc($result['message']) ?>
                                                <?php else: ?>
                                                    Terjadi kesalahan saat menghitung statistik.
                                                <?php endif; ?>
                                            </p>
                                            <button type="button" class="btn btn-primary" id="recalculateBtnError">
                                                <i class="fas fa-sync-alt"></i> Coba Hitung Ulang
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuration Summary -->
                    <?php if (isset($statistic['config']) && $statistic['config']): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-cogs"></i> Konfigurasi Saat Ini
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php $config = json_decode($statistic['config'], true); ?>
                                            <?php if ($config): ?>
                                                <div class="col-md-6">
                                                    <h6>Filter & Grouping</h6>
                                                    <ul class="list-unstyled">
                                                        <li><strong>Filter:</strong> <?= $config['filters'] ?? 'Tidak ada' ?></li>
                                                        <li><strong>Group By:</strong> <?= $config['group_by'] ?? 'Tidak ada' ?></li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Visualisasi</h6>
                                                    <ul class="list-unstyled">
                                                        <li><strong>Judul Chart:</strong> <?= $config['chart_title'] ?? 'Default' ?></li>
                                                        <li><strong>Label X:</strong> <?= $config['x_axis_label'] ?? 'Tidak ada' ?></li>
                                                        <li><strong>Label Y:</strong> <?= $config['y_axis_label'] ?? 'Tidak ada' ?></li>
                                                        <li><strong>Desimal:</strong> <?= $config['decimal_places'] ?? 2 ?> digit</li>
                                                    </ul>
                                                </div>
                                            <?php else: ?>
                                                <div class="col-12">
                                                    <p class="text-muted">Konfigurasi default digunakan.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Loading -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Menghitung ulang statistik...</p>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Modal -->
<div class="modal fade" id="fullscreenModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Preview Fullscreen: <?= esc($statistic['stat_name']) ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="fullscreenContainer">
                    <!-- Content will be cloned here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize chart if needed
        <?php if (in_array($statistic['visualization_type'], ['bar_chart', 'pie_chart', 'line_chart', 'area_chart', 'donut_chart', 'scatter_chart'])): ?>
            initializeChart();
        <?php endif; ?>

        // Recalculate button
        $('#recalculateBtn, #recalculateBtnError').on('click', function() {
            $('#loadingModal').modal('show');

            $.ajax({
                url: `<?= base_url('owner/statistics/recalculate/' . $statistic['id']) ?>`,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    $('#loadingModal').modal('hide');

                    if (response.success) {
                        // Reload page to show updated data
                        location.reload();
                    } else {
                        alert('Gagal menghitung ulang: ' + response.message);
                    }
                },
                error: function() {
                    $('#loadingModal').modal('hide');
                    alert('Terjadi kesalahan saat menghitung ulang statistik');
                }
            });
        });

        // Fullscreen button
        $('#fullscreenBtn').on('click', function() {
            const container = $('#visualization-container').clone();
            $('#fullscreenContainer').html(container);
            $('#fullscreenModal').modal('show');

            // Re-initialize chart in fullscreen if needed
            <?php if (in_array($statistic['visualization_type'], ['bar_chart', 'pie_chart', 'line_chart', 'area_chart', 'donut_chart', 'scatter_chart'])): ?>
                setTimeout(() => {
                    initializeFullscreenChart();
                }, 500);
            <?php endif; ?>
        });
    });

    <?php if (in_array($statistic['visualization_type'], ['bar_chart', 'pie_chart', 'line_chart', 'area_chart', 'donut_chart', 'scatter_chart'])): ?>

        function initializeChart() {
            const ctx = document.getElementById('statisticChart').getContext('2d');

            <?php if (isset($result['data']) && is_array($result['data'])): ?>
                const chartData = <?= json_encode($result['data']) ?>;

                let labels = [];
                let values = [];

                // Extract labels and values based on data structure
                if (Array.isArray(chartData)) {
                    chartData.forEach(item => {
                        if (typeof item === 'object') {
                            const keys = Object.keys(item);
                            if (keys.length >= 2) {
                                labels.push(item[keys[0]]);
                                values.push(parseFloat(item[keys[1]]) || 0);
                            }
                        }
                    });
                }

                const config = {
                    type: '<?= str_replace('_', '', $statistic['visualization_type']) ?>',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '<?= esc($statistic['stat_name']) ?>',
                            data: values,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                };

                new Chart(ctx, config);
            <?php endif; ?>
        }

        function initializeFullscreenChart() {
            const ctx = document.querySelector('#fullscreenContainer canvas')?.getContext('2d');
            if (!ctx) return;

            <?php if (isset($result['data']) && is_array($result['data'])): ?>
                const chartData = <?= json_encode($result['data']) ?>;

                let labels = [];
                let values = [];

                // Extract labels and values based on data structure
                if (Array.isArray(chartData)) {
                    chartData.forEach(item => {
                        if (typeof item === 'object') {
                            const keys = Object.keys(item);
                            if (keys.length >= 2) {
                                labels.push(item[keys[0]]);
                                values.push(parseFloat(item[keys[1]]) || 0);
                            }
                        }
                    });
                }

                const config = {
                    type: '<?= str_replace('_', '', $statistic['visualization_type']) ?>',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '<?= esc($statistic['stat_name']) ?>',
                            data: values,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                };

                new Chart(ctx, config);
            <?php endif; ?>
        }
    <?php endif; ?>
</script>

<?= $this->endSection() ?>