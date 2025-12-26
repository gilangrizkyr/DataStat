i<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-table me-2"></i>Records: <?= esc($dataset['dataset_name']) ?></h2>
            <p class="text-muted">Menampilkan semua data dalam dataset</p>
        </div>
        <div>
            <a href="<?= base_url('owner/datasets/detail/' . $dataset['id']) ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Detail
            </a>
            <a href="<?= base_url('owner/datasets/export/' . $dataset['id']) ?>" class="btn btn-success">
                <i class="fas fa-download me-2"></i>Export Excel
            </a>
        </div>
    </div>

    <!-- Dataset Info -->
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
                    <h6 class="text-muted">Current Page</h6>
                    <h3><?= $pager->getCurrentPage() ?> / <?= $pager->getPageCount() ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Records per Page</h6>
                    <h3>100</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Records Table -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list me-2"></i>Data Records</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($records)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <?php if (!empty($schema)): ?>
                                    <?php foreach ($schema as $column): ?>
                                        <th>
                                            <?= esc($column['field_name'] ?? $column['name'] ?? 'Unknown') ?>
                                            <br><small class="text-muted">
                                                <?= esc($column['field_type'] ?? $column['type'] ?? 'text') ?>
                                            </small>
                                        </th>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback: use first record keys -->
                                    <?php $firstRecord = reset($records); ?>
                                    <?php if (is_array($firstRecord['data'])): ?>
                                        <?php foreach (array_keys($firstRecord['data']) as $column): ?>
                                            <th><?= esc($column) ?></th>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $rowNumber = (($pager->getCurrentPage() - 1) * 100) + 1;
                            foreach ($records as $record):
                                if (is_array($record['data'])):
                            ?>
                                    <tr>
                                        <td class="text-center">
                                            <strong><?= $rowNumber++ ?></strong>
                                        </td>
                                        <?php if (!empty($schema)): ?>
                                            <?php foreach ($schema as $column): ?>
                                                <?php
                                                $fieldName = $column['field_name'] ?? $column['name'] ?? '';
                                                $value = $record['data'][$fieldName] ?? '';
                                                ?>
                                                <td>
                                                    <?php if (is_array($value) || is_object($value)): ?>
                                                        <code><?= esc(json_encode($value)) ?></code>
                                                    <?php else: ?>
                                                        <?= esc($value) ?>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <?php foreach ($record['data'] as $value): ?>
                                                <td>
                                                    <?php if (is_array($value) || is_object($value)): ?>
                                                        <code><?= esc(json_encode($value)) ?></code>
                                                    <?php else: ?>
                                                        <?= esc($value) ?>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tr>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Menampilkan <?= count($records) ?> dari <?= $dataset['total_rows'] ?> records
                    </div>
                    <?= $pager->links() ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data untuk ditampilkan</h5>
                    <p class="text-muted">Dataset ini belum memiliki records atau terjadi kesalahan dalam memuat data.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    // Add some interactivity to the table
    document.addEventListener('DOMContentLoaded', function() {
        // Make table rows clickable for details (optional)
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
</script>

<?= $this->endSection() ?>