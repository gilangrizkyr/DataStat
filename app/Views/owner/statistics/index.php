<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kelola Statistik</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('owner/statistics/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Buat Statistik Baru
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

                    <?php if (empty($statistics)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Belum ada statistik</h4>
                            <p class="text-muted">Mulai buat statistik pertama Anda untuk menganalisis data.</p>
                            <a href="<?= base_url('owner/statistics/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Buat Statistik Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nama Statistik</th>
                                        <th>Dataset</th>
                                        <th>Tipe Metrik</th>
                                        <th>Visualisasi</th>
                                        <th>Status</th>
                                        <th>Dibuat Oleh</th>
                                        <th>Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($statistics as $stat): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($stat['stat_name']) ?></strong>
                                                <?php if ($stat['description']): ?>
                                                    <br><small class="text-muted"><?= esc($stat['description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($stat['dataset_name']) ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= ucfirst(str_replace('_', ' ', $stat['metric_type'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?= ucfirst(str_replace('_', ' ', $stat['visualization_type'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($stat['is_active']): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($stat['creator_name']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($stat['created_at'])) ?></td>
                                            <td>
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="<?= base_url('owner/statistics/detail/' . $stat['id']) ?>" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= base_url('owner/statistics/edit/' . $stat['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= base_url('owner/statistics/builder/' . $stat['id']) ?>" class="btn btn-sm btn-outline-primary" title="Builder">
                                                        <i class="fas fa-cogs"></i>
                                                    </a>
                                                    <form method="post" action="<?= base_url('owner/statistics/toggle-active/' . $stat['id']) ?>" style="display: inline;">
                                                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Toggle Status" onclick="return confirm('Ubah status statistik?')">
                                                            <i class="fas fa-toggle-<?= $stat['is_active'] ? 'on text-success' : 'off text-muted' ?>"></i>
                                                        </button>
                                                    </form>
                                                    <a href="<?= base_url('owner/statistics/duplicate/' . $stat['id']) ?>" class="btn btn-sm btn-outline-success" title="Duplikat" onclick="return confirm('Duplikat statistik ini?')">
                                                        <i class="fas fa-copy"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-statistic" data-id="<?= $stat['id'] ?>" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Konfirmasi Hapus</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus statistik ini?</p>
                <p class="text-danger">Data yang sudah dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Ensure jQuery is loaded
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }

        // Delete statistic
        $('.delete-statistic').on('click', function() {
            const id = $(this).data('id');
            $('#confirmDelete').data('id', id);
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').on('click', function() {
            const id = $(this).data('id');

            $.ajax({
                url: `<?= base_url('owner/statistics/delete/') ?>${id}`,
                method: 'POST',
                dataType: 'json',
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    '_method': 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Gagal menghapus statistik: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    try {
                        const response = JSON.parse(xhr.responseText);
                        alert('Terjadi kesalahan: ' + (response.message || 'Unknown error'));
                    } catch (e) {
                        alert('Terjadi kesalahan saat menghapus statistik');
                    }
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>