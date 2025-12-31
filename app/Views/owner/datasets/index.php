<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-database me-2"></i>Dataset Management</h2>
            <p class="text-muted">Kelola dan monitor dataset statistik Anda</p>
        </div>
        <a href="<?= base_url('owner/datasets/upload') ?>" class="btn btn-primary">
            <i class="fas fa-upload me-2"></i>Upload Dataset Baru
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?= number_format($total_datasets ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">Total Dataset</p>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-database fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?= number_format($total_records ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">Total Records</p>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?= $total_size ?? '0 MB' ?></h3>
                        <p class="text-muted mb-0 small">Total Size</p>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-hdd fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?= number_format($active_datasets ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">Dataset Aktif</p>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Datasets Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (empty($datasets)): ?>
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="fas fa-database text-muted mb-3" style="font-size: 5rem; opacity: 0.3;"></i>
                <h3>Belum Ada Dataset</h3>
                <p class="text-muted mb-4">Mulai dengan mengupload dataset pertama Anda untuk membuat statistik dan dashboard.</p>
                <a href="<?= base_url('owner/datasets/upload') ?>" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Upload Dataset Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="datasetsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Dataset</th>
                            <th>File</th>
                            <th>Records</th>
                            <th>Columns</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datasets as $dataset): ?>
                        <tr>
                            <td>
                                <strong><?= esc($dataset['dataset_name']) ?></strong>
                                <?php if (!empty($dataset['description'])): ?>
                                    <br><small class="text-muted"><?= esc($dataset['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fas fa-file-csv text-success me-1"></i>
                                <?= esc($dataset['file_name']) ?>
                            </td>
                            <td><?= number_format($dataset['total_rows'] ?? 0) ?></td>
                            <td><?= number_format($dataset['total_columns'] ?? 0) ?></td>
                            <td><?= format_file_size($dataset['file_size'] ?? 0) ?></td>
                            <td><?= date('d M Y', strtotime($dataset['created_at'])) ?></td>
                            <td class="text-center">
                                <a href="<?= base_url('owner/datasets/view/' . $dataset['id']) ?>" 
                                   class="btn btn-sm btn-primary" 
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= base_url('owner/datasets/records/' . $dataset['id']) ?>" 
                                   class="btn btn-sm btn-info" 
                                   title="Records">
                                    <i class="fas fa-table"></i>
                                </a>
                                <a href="<?= base_url('owner/datasets/edit/' . $dataset['id']) ?>" 
                                   class="btn btn-sm btn-warning" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="deleteDataset(<?= $dataset['id'] ?>)" 
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
// Initialize DataTable
$(document).ready(function() {
    $('#datasetsTable').DataTable({
        order: [[5, 'desc']],
        pageLength: 25,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ dataset",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total dataset)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });
});

// Delete dataset
function deleteDataset(id) {
    Swal.fire({
        title: 'Hapus Dataset?',
        text: "Dataset dan semua records akan dihapus permanent!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url('owner/datasets/delete/') ?>' + id;
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_method';
            input.value = 'DELETE';
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Show messages
<?php if (session()->has('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= session('success') ?>',
        timer: 3000,
        showConfirmButton: false
    });
<?php endif; ?>

<?php if (session()->has('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '<?= session('error') ?>',
        timer: 3000,
        showConfirmButton: false
    });
<?php endif; ?>
</script>
<?= $this->endSection() ?>