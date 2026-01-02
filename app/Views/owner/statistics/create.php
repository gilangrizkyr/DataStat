<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Buat Statistik Baru</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('owner/statistics') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="icon fas fa-ban"></i> <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('owner/statistics/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stat_name">Nama Statistik <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= ($validation->hasError('stat_name')) ? 'is-invalid' : '' ?>" id="stat_name" name="stat_name" value="<?= old('stat_name') ?>" placeholder="Masukkan nama statistik" required>
                                    <?php if ($validation->hasError('stat_name')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('stat_name') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dataset_id">Dataset <span class="text-danger">*</span></label>
                                    <select class="form-control <?= ($validation->hasError('dataset_id')) ? 'is-invalid' : '' ?>" id="dataset_id" name="dataset_id" required>
                                        <option value="">Pilih Dataset</option>
                                        <?php foreach ($datasets as $dataset): ?>
                                            <option value="<?= $dataset['id'] ?>" <?= (old('dataset_id') == $dataset['id']) ? 'selected' : '' ?>>
                                                <?= esc($dataset['dataset_name']) ?> (<?= format_file_size($dataset['file_size']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($validation->hasError('dataset_id')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('dataset_id') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi statistik (opsional)"><?= old('description') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="metric_type">Tipe Metrik <span class="text-danger">*</span></label>
                                    <select class="form-control <?= ($validation->hasError('metric_type')) ? 'is-invalid' : '' ?>" id="metric_type" name="metric_type" required>
                                        <option value="">Pilih Tipe Metrik</option>
                                        <option value="count" <?= (old('metric_type') == 'count') ? 'selected' : '' ?>>Count (Jumlah)</option>
                                        <option value="sum" <?= (old('metric_type') == 'sum') ? 'selected' : '' ?>>Sum (Jumlah Total)</option>
                                        <option value="average" <?= (old('metric_type') == 'average') ? 'selected' : '' ?>>Average (Rata-rata)</option>
                                        <option value="min" <?= (old('metric_type') == 'min') ? 'selected' : '' ?>>Minimum</option>
                                        <option value="max" <?= (old('metric_type') == 'max') ? 'selected' : '' ?>>Maximum</option>
                                        <option value="percentage" <?= (old('metric_type') == 'percentage') ? 'selected' : '' ?>>Percentage (Persentase)</option>
                                        <option value="ratio" <?= (old('metric_type') == 'ratio') ? 'selected' : '' ?>>Ratio (Rasio)</option>
                                        <option value="growth" <?= (old('metric_type') == 'growth') ? 'selected' : '' ?>>Growth (Pertumbuhan)</option>
                                        <option value="ranking" <?= (old('metric_type') == 'ranking') ? 'selected' : '' ?>>Ranking (Peringkat)</option>
                                        <option value="custom_formula" <?= (old('metric_type') == 'custom_formula') ? 'selected' : '' ?>>Custom Formula</option>
                                    </select>
                                    <?php if ($validation->hasError('metric_type')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('metric_type') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="visualization_type">Tipe Visualisasi <span class="text-danger">*</span></label>
                                    <select class="form-control <?= ($validation->hasError('visualization_type')) ? 'is-invalid' : '' ?>" id="visualization_type" name="visualization_type" required>
                                        <option value="">Pilih Tipe Visualisasi</option>
                                        <option value="table" <?= (old('visualization_type') == 'table') ? 'selected' : '' ?>>Table (Tabel)</option>
                                        <option value="bar_chart" <?= (old('visualization_type') == 'bar_chart') ? 'selected' : '' ?>>Bar Chart</option>
                                        <option value="pie_chart" <?= (old('visualization_type') == 'pie_chart') ? 'selected' : '' ?>>Pie Chart</option>
                                        <option value="line_chart" <?= (old('visualization_type') == 'line_chart') ? 'selected' : '' ?>>Line Chart</option>
                                        <option value="area_chart" <?= (old('visualization_type') == 'area_chart') ? 'selected' : '' ?>>Area Chart</option>
                                        <option value="kpi_card" <?= (old('visualization_type') == 'kpi_card') ? 'selected' : '' ?>>KPI Card</option>
                                        <option value="progress_bar" <?= (old('visualization_type') == 'progress_bar') ? 'selected' : '' ?>>Progress Bar</option>
                                        <option value="donut_chart" <?= (old('visualization_type') == 'donut_chart') ? 'selected' : '' ?>>Donut Chart</option>
                                        <option value="scatter_chart" <?= (old('visualization_type') == 'scatter_chart') ? 'selected' : '' ?>>Scatter Chart</option>
                                    </select>
                                    <?php if ($validation->hasError('visualization_type')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('visualization_type') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="target_field_group" style="display: none;">
                            <label for="target_field">Field Target</label>
                            <select class="form-control" id="target_field" name="target_field">
                                <option value="">Pilih Field Target</option>
                            </select>
                            <small class="form-text text-muted">Field yang akan digunakan untuk perhitungan statistik</small>
                        </div>

                        <div class="card border-info">
                            <div class="card-header bg-info">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle"></i> Informasi
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Langkah Selanjutnya:</strong></p>
                                <ul class="mb-0">
                                    <li>Setelah membuat statistik dasar, Anda akan diarahkan ke Statistic Builder</li>
                                    <li>Di Statistic Builder, Anda dapat mengkonfigurasi detail perhitungan, filter, dan tampilan</li>
                                    <li>Statistik akan aktif secara otomatis setelah dikonfigurasi lengkap</li>
                                </ul>
                            </div>
                        </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Buat Statistik
                    </button>
                    <a href="<?= base_url('owner/statistics') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Load target fields when dataset is selected
        $('#dataset_id').on('change', function() {
            const datasetId = $(this).val();
            if (datasetId) {
                loadTargetFields(datasetId);
            } else {
                $('#target_field').html('<option value="">Pilih Field Target</option>');
                $('#target_field_group').hide();
            }
        });

        // Show/hide target field based on metric type
        $('#metric_type').on('change', function() {
            const metricType = $(this).val();
            const targetFieldGroup = $('#target_field_group');

            if (['count', 'sum', 'average', 'min', 'max', 'percentage', 'ratio', 'growth', 'ranking'].includes(metricType)) {
                targetFieldGroup.show();
            } else {
                targetFieldGroup.hide();
            }
        });

        function loadTargetFields(datasetId) {
            $.ajax({
                url: `<?= base_url('api/dataset/fields/') ?>${datasetId}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">Pilih Field Target</option>';
                        response.fields.forEach(function(field) {
                            options += `<option value="${field.name}">${field.name} (${field.type})</option>`;
                        });
                        $('#target_field').html(options);
                        $('#target_field_group').show();
                    } else {
                        $('#target_field').html('<option value="">Error loading fields</option>');
                    }
                },
                error: function() {
                    $('#target_field').html('<option value="">Error loading fields</option>');
                }
            });
        }

        // Trigger change events on page load if values are set
        if ($('#dataset_id').val()) {
            $('#dataset_id').trigger('change');
        }
        if ($('#metric_type').val()) {
            $('#metric_type').trigger('change');
        }
    });
</script>

<?= $this->endSection() ?>