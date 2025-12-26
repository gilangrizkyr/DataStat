<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs mr-2"></i>
                        Statistic Builder: <?= esc($statistic['stat_name']) ?>
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('owner/statistics/detail/' . $statistic['id']) ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Lihat Detail
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

                    <form id="statisticBuilderForm" action="<?= base_url('owner/statistics/builder/save/' . $statistic['id']) ?>" method="post">
                        <?= csrf_field() ?>

                        <!-- Basic Configuration -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle"></i> Informasi Dasar
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Nama Statistik</label>
                                            <input type="text" class="form-control" value="<?= esc($statistic['stat_name']) ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Dataset</label>
                                            <input type="text" class="form-control" value="<?= esc($statistic['dataset_name']) ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipe Metrik</label>
                                            <input type="text" class="form-control" value="<?= ucfirst(str_replace('_', ' ', $statistic['metric_type'])) ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipe Visualisasi</label>
                                            <input type="text" class="form-control" value="<?= ucfirst(str_replace('_', ' ', $statistic['visualization_type'])) ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-filter"></i> Konfigurasi Filter
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="filters">Filter Data</label>
                                            <textarea class="form-control" id="filters" name="filters" rows="4" placeholder="Masukkan filter dalam format JSON atau kosongkan untuk semua data"><?= old('filters', $config['filters'] ?? '') ?></textarea>
                                            <small class="form-text text-muted">
                                                Contoh: {"column_name": "value"} atau {"column_name": {"operator": ">", "value": 100}}
                                            </small>
                                        </div>
                                        <div class="form-group">
                                            <label for="group_by">Group By</label>
                                            <input type="text" class="form-control" id="group_by" name="group_by" value="<?= old('group_by', $config['group_by'] ?? '') ?>" placeholder="Nama kolom untuk grouping">
                                            <small class="form-text text-muted">Kosongkan jika tidak perlu grouping</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Configuration -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-calculator"></i> Konfigurasi Perhitungan
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="target_field">Field Target</label>
                                            <select class="form-control" id="target_field" name="target_field">
                                                <option value="">Pilih Field Target</option>
                                                <?php if (isset($dataset['fields'])): ?>
                                                    <?php foreach ($dataset['fields'] as $field): ?>
                                                        <option value="<?= esc($field['name']) ?>" <?= (old('target_field', $config['target_field'] ?? $statistic['target_field']) == $field['name']) ? 'selected' : '' ?>>
                                                            <?= esc($field['name']) ?> (<?= esc($field['type']) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <small class="form-text text-muted">Field yang akan digunakan untuk perhitungan</small>
                                        </div>

                                        <?php if ($statistic['metric_type'] === 'custom_formula'): ?>
                                            <div class="form-group">
                                                <label for="custom_formula">Custom Formula</label>
                                                <textarea class="form-control" id="custom_formula" name="custom_formula" rows="3" placeholder="Masukkan formula matematika"><?= old('custom_formula', $config['custom_formula'] ?? '') ?></textarea>
                                                <small class="form-text text-muted">
                                                    Gunakan nama kolom sebagai variabel. Contoh: (harga * jumlah) + pajak
                                                </small>
                                            </div>
                                        <?php endif; ?>

                                        <div class="form-group">
                                            <label for="decimal_places">Desimal</label>
                                            <input type="number" class="form-control" id="decimal_places" name="decimal_places" value="<?= old('decimal_places', $config['decimal_places'] ?? 2) ?>" min="0" max="10">
                                            <small class="form-text text-muted">Jumlah digit desimal untuk hasil</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-bar"></i> Konfigurasi Visualisasi
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="chart_title">Judul Chart</label>
                                            <input type="text" class="form-control" id="chart_title" name="chart_title" value="<?= old('chart_title', $config['chart_title'] ?? $statistic['stat_name']) ?>" placeholder="Judul untuk chart">
                                        </div>

                                        <div class="form-group">
                                            <label for="x_axis_label">Label Sumbu X</label>
                                            <input type="text" class="form-control" id="x_axis_label" name="x_axis_label" value="<?= old('x_axis_label', $config['x_axis_label'] ?? '') ?>" placeholder="Label untuk sumbu X">
                                        </div>

                                        <div class="form-group">
                                            <label for="y_axis_label">Label Sumbu Y</label>
                                            <input type="text" class="form-control" id="y_axis_label" name="y_axis_label" value="<?= old('y_axis_label', $config['y_axis_label'] ?? '') ?>" placeholder="Label untuk sumbu Y">
                                        </div>

                                        <div class="form-group">
                                            <label for="colors">Warna Chart</label>
                                            <input type="text" class="form-control" id="colors" name="colors" value="<?= old('colors', $config['colors'] ?? '') ?>" placeholder="Warna dalam format hex atau nama warna">
                                            <small class="form-text text-muted">
                                                Pisahkan dengan koma. Contoh: #FF6384,#36A2EB,#FFCE56
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-eye"></i> Preview
                                        </h5>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-primary btn-sm" id="previewBtn">
                                                <i class="fas fa-sync-alt"></i> Preview
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="previewContainer">
                                            <div class="text-center py-5">
                                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                                <h4 class="text-muted">Klik "Preview" untuk melihat hasil</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer">
                    <button type="submit" form="statisticBuilderForm" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Konfigurasi
                    </button>
                    <button type="button" class="btn btn-info" id="testCalculationBtn">
                        <i class="fas fa-calculator"></i> Test Perhitungan
                    </button>
                    <a href="<?= base_url('owner/statistics/detail/' . $statistic['id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
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
                <p class="mt-2">Memproses...</p>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Preview functionality
        $('#previewBtn').on('click', function() {
            const formData = new FormData(document.getElementById('statisticBuilderForm'));
            const config = {};

            // Convert FormData to JSON object
            for (let [key, value] of formData.entries()) {
                if (value !== '' && value !== null) {
                    // Try to parse JSON strings
                    try {
                        config[key] = JSON.parse(value);
                    } catch {
                        config[key] = value;
                    }
                }
            }

            // Add required fields for preview
            config.dataset_id = <?= $statistic['dataset_id'] ?>;
            config.metric_type = '<?= $statistic['metric_type'] ?>';
            config.visualization_type = '<?= $statistic['visualization_type'] ?>';

            $('#loadingModal').modal('show');

            $.ajax({
                url: `<?= base_url('owner/statistic-builder/preview') ?>`,
                method: 'POST',
                data: JSON.stringify(config),
                contentType: 'application/json',
                success: function(response) {
                    $('#loadingModal').modal('hide');

                    if (response.success) {
                        renderPreview(response.data);
                    } else {
                        $('#previewContainer').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Error: ${response.message}
                        </div>
                    `);
                    }
                },
                error: function() {
                    $('#loadingModal').modal('hide');
                    $('#previewContainer').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan saat memuat preview
                    </div>
                `);
                }
            });
        });

        // Test calculation
        $('#testCalculationBtn').on('click', function() {
            const formData = new FormData(document.getElementById('statisticBuilderForm'));
            const config = {};

            // Convert FormData to JSON object
            for (let [key, value] of formData.entries()) {
                if (value !== '' && value !== null) {
                    // Try to parse JSON strings
                    try {
                        config[key] = JSON.parse(value);
                    } catch {
                        config[key] = value;
                    }
                }
            }

            // Add statistic_id for existing statistics
            config.statistic_id = <?= $statistic['id'] ?? 'null' ?>;

            $('#loadingModal').modal('show');

            $.ajax({
                url: `<?= base_url('owner/statistics/recalculate/' . $statistic['id']) ?>`,
                method: 'POST',
                data: JSON.stringify(config),
                contentType: 'application/json',
                success: function(response) {
                    $('#loadingModal').modal('hide');

                    if (response.success) {
                        alert('Perhitungan berhasil! Lihat detail statistik untuk hasil lengkapnya.');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    $('#loadingModal').modal('hide');
                    alert('Terjadi kesalahan saat melakukan perhitungan');
                }
            });
        });

        // Form validation
        $('#statisticBuilderForm').on('submit', function(e) {
            // Basic validation
            const targetField = $('#target_field').val();
            if (!targetField) {
                e.preventDefault();
                alert('Field target harus dipilih');
                return false;
            }

            $('#loadingModal').modal('show');
        });

        function renderPreview(data) {
            let html = '';

            if (data.chart_type === 'table') {
                // Table preview
                html = '<div class="table-responsive"><table class="table table-bordered table-striped">';
                if (data.headers && data.headers.length > 0) {
                    html += '<thead><tr>';
                    data.headers.forEach(header => {
                        html += `<th>${header}</th>`;
                    });
                    html += '</tr></thead>';
                }
                if (data.rows && data.rows.length > 0) {
                    html += '<tbody>';
                    data.rows.forEach(row => {
                        html += '<tr>';
                        row.forEach(cell => {
                            html += `<td>${cell}</td>`;
                        });
                        html += '</tr>';
                    });
                    html += '</tbody>';
                }
                html += '</table></div>';
            } else if (['bar_chart', 'pie_chart', 'line_chart'].includes(data.chart_type)) {
                // Chart preview
                html = '<div class="chart-container"><canvas id="previewChart" width="400" height="200"></canvas></div>';
                setTimeout(() => {
                    renderChart(data);
                }, 100);
            } else {
                // JSON preview
                html = `<pre class="bg-light p-3 rounded"><code>${JSON.stringify(data, null, 2)}</code></pre>`;
            }

            $('#previewContainer').html(html);
        }

        function renderChart(data) {
            const ctx = document.getElementById('previewChart');
            if (!ctx) return;

            const config = {
                type: data.chart_type.replace('_chart', ''),
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: data.title || 'Data',
                        data: data.values || [],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            };

            new Chart(ctx, config);
        }
    });
</script>

<?= $this->endSection() ?>