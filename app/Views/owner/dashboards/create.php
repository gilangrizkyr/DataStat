<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1><i class="bi bi-plus-circle me-2"></i>Buat Dashboard Baru</h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Dashboard</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('owner/dashboards/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="dashboard_name" class="form-label">
                            Nama Dashboard <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control <?= session('errors.dashboard_name') ? 'is-invalid' : '' ?>"
                            id="dashboard_name" name="dashboard_name"
                            value="<?= old('dashboard_name') ?>"
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
                            placeholder="Jelaskan tujuan dan isi dashboard ini"><?= old('description') ?></textarea>
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
                                <?= old('is_default') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_default">
                                Jadikan sebagai dashboard default
                            </label>
                        </div>
                        <div class="form-text">
                            Dashboard default akan ditampilkan pertama kali saat membuka aplikasi
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('owner/dashboards') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Buat Dashboard
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tips Card -->
        <div class="card mt-4 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips Membuat Dashboard</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Gunakan nama yang jelas dan deskriptif</li>
                    <li>Tentukan tujuan dashboard (misalnya: "Dashboard Penjualan Bulanan")</li>
                    <li>Dashboard dapat diatur sebagai default untuk kemudahan akses</li>
                    <li>Setelah dibuat, Anda dapat menambahkan widget statistik ke dashboard</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>