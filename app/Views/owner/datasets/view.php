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
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schema as $index => $field): ?>
                                        <tr>
                                            <td><code><?= esc($field['field_name']) ?></code></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= esc($field['field_type'] ?? $field['type'] ?? 'string') ?></span>
                                            </td>
                                            <td><?= esc($field['field_label'] ?? $field['display_label'] ?? $field['field_name']) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-column"
                                                    data-column-name="<?= esc($field['field_name']) ?>"
                                                    data-dataset-id="<?= $dataset['id'] ?>"
                                                    title="Hapus Kolom">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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

<!-- Modal for delete confirmation -->
<div class="modal fade" id="deleteColumnModal" tabindex="-1" aria-labelledby="deleteColumnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteColumnModalLabel">Konfirmasi Hapus Kolom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus kolom <strong id="columnNameToDelete"></strong>?</p>
                <div class="alert alert-warning">
                    <small>
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Tindakan ini akan menghapus kolom dari schema dataset dan data terkait tidak akan dapat dikembalikan.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteColumn">Hapus Kolom</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Wait for jQuery to be loaded
    function initDeleteColumn() {
        if (typeof $ === 'undefined') {
            setTimeout(initDeleteColumn, 100);
            return;
        }

        $(document).ready(function() {
            let columnToDelete = '';
            let datasetId = '';

            // Handle delete column button click
            $('.delete-column').on('click', function() {
                columnToDelete = $(this).data('column-name');
                datasetId = $(this).data('dataset-id');

                $('#columnNameToDelete').text(columnToDelete);
                $('#deleteColumnModal').modal('show');
            });

            // Handle confirm delete
            $('#confirmDeleteColumn').on('click', function() {
                if (!columnToDelete || !datasetId) {
                    return;
                }

                // Show loading state
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');

                // Prepare data with CSRF token
                var data = {
                    dataset_id: datasetId,
                    column_name: columnToDelete
                };
                data['<?= csrf_token() ?>'] = $('meta[name="csrf-token"]').attr('content');

                // Send AJAX request
                $.ajax({
                    url: '<?= base_url("owner/datasets/delete-column") ?>',
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            toastr.success(response.message || 'Kolom berhasil dihapus');

                            // Reload page after short delay
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error(response.message || 'Gagal menghapus kolom');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.error('Response:', xhr.responseText);
                        toastr.error('Terjadi kesalahan saat menghapus kolom');
                    },
                    complete: function() {
                        // Reset button state
                        $('#confirmDeleteColumn').prop('disabled', false).html('Hapus Kolom');
                        $('#deleteColumnModal').modal('hide');
                    }
                });
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDeleteColumn);
    } else {
        initDeleteColumn();
    }
</script>

<?= $this->endSection() ?>