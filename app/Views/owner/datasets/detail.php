<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-database me-2"></i><?= esc($dataset['dataset_name']) ?></h2>
            <p class="text-muted"><?= esc($dataset['description'] ?? 'Tidak ada deskripsi') ?></p>
        </div>
        <div>
            <a href="<?= base_url('owner/datasets') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="<?= base_url('owner/datasets/edit/' . $dataset['id']) ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="<?= base_url('owner/datasets/export/' . $dataset['id']) ?>" class="btn btn-success">
                <i class="fas fa-download me-2"></i>Export
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Records</h6>
                    <h3><?= number_format($dataset['total_rows']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Columns</h6>
                    <h3><?= number_format($dataset['total_columns']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">File Size</h6>
                    <h3><?= format_file_size($dataset['file_size']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Created At</h6>
                    <h3><?= date('d M Y', strtotime($dataset['created_at'])) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Schema -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-columns me-2"></i>Schema Kolom</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Column Name</th>
                            <th>Data Type</th>
                            <th>Sample Values</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $schema = json_decode($dataset['schema_config'] ?? '[]', true);
                        if (is_array($schema) && !empty($schema)):
                            foreach ($schema as $column):
                                if (is_array($column)):
                        ?>
                                    <tr>
                                        <td><strong><?= esc($column['field_name'] ?? $column['original_name'] ?? $column['name'] ?? 'Unknown') ?></strong></td>
                                        <td><span class="badge bg-info"><?= esc($column['field_type'] ?? $column['type'] ?? 'text') ?></span></td>
                                        <td><code><?= esc($column['sample'] ?? $column['display_label'] ?? '-') ?></code></td>
                                    </tr>
                            <?php
                                endif;
                            endforeach;
                        else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">Schema tidak tersedia</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Preview Data -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-eye me-2"></i>Preview Data (10 rows pertama)</h5>
            <a href="<?= base_url('owner/datasets/records/' . $dataset['id']) ?>" class="btn btn-sm btn-primary">
                Lihat Semua Data
            </a>
        </div>
        <div class="card-body">
            <?php if (!empty($preview)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <?php foreach (array_keys($preview[0] ?? []) as $column): ?>
                                    <th><?= esc($column) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($preview as $row): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td><?= esc($value) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">Tidak ada data untuk ditampilkan</p>
            <?php endif; ?>
        </div>
    </div>

</div>
<?= $this->endSection() ?>