<?= $this->extend('layouts/superadmin') ?>  
<?= $this->section('content') ?>

<div class="page-title">
    <h1><i class="bi bi-person-plus-fill me-2"></i> <?= esc($title) ?></h1>
    <p class="mb-0">Tambahkan user baru ke dalam sistem DataStat</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-fill-add me-2"></i>Form Tambah User</h5>
            </div>
            <div class="card-body">

                <form action="<?= base_url('superadmin/users/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_lengkap" class="form-label">
                                <i class="bi bi-person me-1"></i>Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control <?= session('errors.nama_lengkap') ? 'is-invalid' : '' ?>"
                                id="nama_lengkap"
                                name="nama_lengkap"
                                value="<?= old('nama_lengkap') ?>"
                                placeholder="Masukkan nama lengkap"
                                required>
                            <?php if (session('errors.nama_lengkap')): ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.nama_lengkap') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                                id="email"
                                name="email"
                                value="<?= old('email') ?>"
                                placeholder="Masukkan alamat email"
                                required>
                            <?php if (session('errors.email')): ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.email') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-key me-1"></i>Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                    class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>"
                                    id="password"
                                    name="password"
                                    placeholder="Minimal 6 karakter"
                                    required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                                <?php if (session('errors.password')): ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.password') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Password minimal 6 karakter</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bidang" class="form-label">
                                <i class="bi bi-building me-1"></i>Bidang
                            </label>
                            <input type="text"
                                class="form-control"
                                id="bidang"
                                name="bidang"
                                value="<?= old('bidang') ?>"
                                placeholder="Contoh: IT, Keuangan, HR">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                value="1"
                                <?= old('is_active') ? 'checked' : 'checked' ?>>
                            <label class="form-check-label" for="is_active">
                                <i class="bi bi-check-circle me-1"></i>Aktifkan user setelah dibuat
                            </label>
                        </div>
                        <div class="form-text">User yang tidak aktif tidak dapat login ke sistem</div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('superadmin/users') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-plus me-1"></i>Simpan User
                                </button>
                            </div>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-lightbulb me-1"></i>Tips:</h6>
                    <ul class="mb-0 small">
                        <li>Pastikan email yang dimasukkan belum terdaftar</li>
                        <li>Gunakan password yang kuat dan mudah diingat</li>
                        <li>Bidang bersifat opsional untuk klasifikasi user</li>
                        <li>User baru akan aktif secara default</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle me-1"></i>Peringatan:</h6>
                    <ul class="mb-0 small">
                        <li>Setelah user dibuat, Anda perlu mengatur role dan permission</li>
                        <li>Password akan di-hash dan tidak dapat dilihat kembali</li>
                        <li>Email harus unik di seluruh sistem</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + '-icon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Auto-generate password suggestion
    document.getElementById('nama_lengkap').addEventListener('input', function() {
        const nama = this.value;
        if (nama.length >= 3) {
            const suggestion = nama.toLowerCase().replace(/\s+/g, '') + '123';
            document.getElementById('password').placeholder = 'Sarankan: ' + suggestion;
        }
    });
</script>
<?= $this->endSection() ?>