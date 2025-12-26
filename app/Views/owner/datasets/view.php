<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-table me-2"></i>Dataset: <?= esc($dataset['dataset_name']) ?></h4>
                <div>
                    <a href="<?= base_url('owner/datasets') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke List
                    </a>
                    <a href="<?= base_url('owner/datasets/detail/' . $dataset['id']) ?>" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>Lihat Detail
                    </a>
                    <a href="<?= base_url('owner/datasets/preview/' . $dataset['id']) ?>" class="btn btn-info">
                        <i class="fas fa-eye me-2"></i>Preview Data
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">

            <!-- Dataset Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Informasi Dataset</h5>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Nama:</strong></td>
                            <td><?= esc($dataset['dataset_name']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi:</strong></td>
                            <td><?= esc($dataset['description'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?= $dataset['upload_status'] === 'completed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($dataset['upload_status']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Ukuran File:</strong></td>
                            <td><?= number_format($dataset['file_size'] / 1024, 2) ?> KB</td>
                        </tr>
                        <tr>
                            <td><strong>Diupload:</strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($dataset['created_at'])) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Statistik Data</h5>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Total Kolom:</strong></td>
                            <td><?= $dataset['total_columns'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Baris:</strong></td>
                            <td><?= number_format($dataset['total_rows']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status Schema:</strong></td>
                            <td>
                                <?php if (!empty($dataset['schema_config'])): ?>
                                    <span class="badge bg-success">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Belum Dikonfigurasi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Schema Preview -->
            <?php if (!empty($schema)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5>Schema Kolom</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Kolom</th>
                                        <th>Tipe Data</th>
                                        <th>Label</th>
                                        <th>Wajib</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schema as $field): ?>
                                        <tr>
                                            <td><code><?= esc($field['field_name']) ?></code></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= esc($field['field_type'] ?? $field['type'] ?? 'string') ?></span>
                                            </td>
                                            <td><?= esc($field['field_label'] ?? $field['display_label'] ?? $field['field_name']) ?></td>
                                            <td>
                                                <?php if (isset($field['is_required']) && $field['is_required']): ?>
                                                    <i class="fas fa-check text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Sample Data -->
            <?php if (!empty($sample_data)): ?>
                <div class="row">
                    <div class="col-12">
                        <h5>Contoh Data (5 baris pertama)</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <?php foreach ($schema as $field): ?>
                                            <th><?= esc($field['field_label'] ?? $field['display_label'] ?? $field['field_name']) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($sample_data as $row):
                                        $data = $row['data'];
                                    ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <?php foreach ($schema as $field): ?>
                                                <td><?= esc($data[$field['field_name']] ?? '-') ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-muted small">
                            Menampilkan 5 baris pertama dari total <?= number_format($dataset['total_rows']) ?> baris data.
                            <a href="<?= base_url('owner/datasets/detail/' . $dataset['id']) ?>" class="text-primary">Lihat semua data</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>
<?= $this->endSection() ?>