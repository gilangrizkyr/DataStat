<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1><i class="bi bi-exclamation-triangle text-warning me-2"></i>Konfirmasi Hapus Dashboard</h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Penghapusan</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle-fill me-2"></i>Perhatian!</h6>
                    <p class="mb-0">Tindakan ini tidak dapat dibatalkan. Dashboard yang dihapus akan hilang secara permanen beserta semua widget yang terkait.</p>
                </div>

                <div class="dashboard-info mb-4">
                    <h6>Detail Dashboard:</h6>
                    <div class="row">
                        <div class="col-sm-4"><strong>Nama:</strong></div>
                        <div class="col-sm-8"><?= esc($dashboard['dashboard_name']) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Deskripsi:</strong></div>
                        <div class="col-sm-8">
                            <?= $dashboard['description'] ? esc($dashboard['description']) : '<em class="text-muted">Tidak ada deskripsi</em>' ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Status:</strong></div>
                        <div class="col-sm-8">
                            <?php if ($dashboard['is_default']): ?>
                                <span class="badge bg-success">Default</span>
                            <?php endif; ?>
                            <?php if ($dashboard['is_public']): ?>
                                <span class="badge bg-info">Publik</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Private</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Dibuat:</strong></div>
                        <div class="col-sm-8">
                            <?= date('d M Y H:i', strtotime($dashboard['created_at'])) ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('owner/dashboards') ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Batal
                    </a>
                    <form action="<?= base_url('owner/dashboards/delete/' . $dashboard['id']) ?>" method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus dashboard ini?')">
                            <i class="bi bi-trash me-1"></i>Ya, Hapus Dashboard
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alternative Actions -->
        <div class="card mt-3 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Alternatif Lain</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">Jika Anda tidak ingin menghapus dashboard secara permanen, Anda dapat:</p>
                <div class="d-grid gap-2">
                    <a href="<?= base_url('owner/dashboards/edit/' . $dashboard['id']) ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit Dashboard
                    </a>
                    <a href="<?= base_url('owner/dashboards/manage/' . $dashboard['id']) ?>" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-gear me-1"></i>Kelola Widget
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>