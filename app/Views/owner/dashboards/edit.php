<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1><i class="bi bi-pencil-square me-2"></i>Edit Dashboard: <?= esc($dashboard['dashboard_name']) ?></h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Dashboard</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('owner/dashboards/update/' . $dashboard['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT">

                    <div class="mb-3">
                        <label for="dashboard_name" class="form-label">
                            Nama Dashboard <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control <?= session('errors.dashboard_name') ? 'is-invalid' : '' ?>"
                            id="dashboard_name" name="dashboard_name"
                            value="<?= old('dashboard_name', $dashboard['dashboard_name']) ?>"
                            placeholder="Masukkan nama dashboard" required>
                        <?php if (session('errors.dashboard_name')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.dashboard_name') ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Berikan nama yang deskriptif untuk dashboard Anda
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>"
                            id="description" name="description" rows="3"
                            placeholder="Jelaskan tujuan dan isi dashboard ini"><?= old('description', $dashboard['description']) ?></textarea>
                        <?php if (session('errors.description')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.description') ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Opsional: Jelaskan apa yang akan ditampilkan di dashboard ini
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1"
                                <?= old('is_default', $dashboard['is_default']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_default">
                                Jadikan sebagai dashboard default
                            </label>
                        </div>
                        <div class="form-text">
                            Dashboard default akan ditampilkan pertama kali saat membuka aplikasi
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                                <?= old('is_public', $dashboard['is_public']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_public">
                                Jadikan dashboard publik
                            </label>
                        </div>
                        <div class="form-text">
                            Dashboard publik dapat diakses oleh semua pengguna aplikasi
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('owner/dashboards') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Dashboard Info Card -->
        <div class="card mt-4 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Dashboard</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Dibuat:</strong></p>
                        <p class="text-muted small mb-3">
                            <?= date('d M Y H:i', strtotime($dashboard['created_at'])) ?><br>
                            oleh <?= esc($dashboard['creator_name'] ?? 'Tidak diketahui') ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Terakhir Diubah:</strong></p>
                        <p class="text-muted small mb-3">
                            <?= $dashboard['updated_at'] ? date('d M Y H:i', strtotime($dashboard['updated_at'])) : 'Belum pernah diubah' ?>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Jumlah Widget:</strong></p>
                        <p class="text-muted small">
                            <?= $dashboard['widget_count'] ?? 0 ?> widget
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Status:</strong></p>
                        <p class="text-muted small">
                            <?php if ($dashboard['is_default']): ?>
                                <span class="badge bg-success">Default</span>
                            <?php endif; ?>
                            <?php if ($dashboard['is_public']): ?>
                                <span class="badge bg-info">Publik</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Private</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>