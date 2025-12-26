<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>

<div class="page-title">
    <h1><i class="bi bi-person-gear me-2"></i> <?= esc($title) ?></h1>
    <p class="mb-0">Edit informasi user: <strong><?= esc($user['nama_lengkap']) ?></strong></p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Form Edit User</h5>
            </div>
            <div class="card-body">

                <form action="<?= base_url('superadmin/users/update/' . $user['id']) ?>" method="post">
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
                                value="<?= old('nama_lengkap', $user['nama_lengkap']) ?>"
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
                                value="<?= old('email', $user['email']) ?>"
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
                                <i class="bi bi-key me-1"></i>Password Baru
                            </label>
                            <div class="input-group">
                                <input type="password"
                                    class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>"
                                    id="password"
                                    name="password"
                                    placeholder="Kosongkan jika tidak ingin mengubah">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                                <?php if (session('errors.password')): ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.password') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bidang" class="form-label">
                                <i class="bi bi-building me-1"></i>Bidang
                            </label>
                            <input type="text"
                                class="form-control"
                                id="bidang"
                                name="bidang"
                                value="<?= old('bidang', $user['bidang']) ?>"
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
                                <?= old('is_active', $user['is_active']) == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                <i class="bi bi-check-circle me-1"></i>Aktifkan user
                            </label>
                        </div>
                        <div class="form-text">User yang tidak aktif tidak dapat login ke sistem</div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('superadmin/users/detail/' . $user['id']) ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Kembali
                                </a>
                                <div>
                                    <button type="button" class="btn btn-outline-warning me-2" onclick="resetPassword(<?= $user['id'] ?>)">
                                        <i class="bi bi-key me-1"></i>Reset Password
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Update User
                                    </button>
                                </div>
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
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi User</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-circle bg-primary text-white mx-auto mb-2" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                        <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                    </div>
                    <h6 class="mb-1"><?= esc($user['nama_lengkap']) ?></h6>
                    <p class="text-muted small mb-0">ID: <?= $user['id'] ?></p>
                </div>

                <hr>

                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-0"><?= $user['role_count'] ?></h4>
                            <small class="text-muted">Role</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-0"><?= $user['app_count'] ?></h4>
                        <small class="text-muted">Aplikasi</small>
                    </div>
                </div>

                <hr>

                <div class="small">
                    <p><strong>Dibuat:</strong><br><?= date('d M Y H:i', strtotime($user['created_at'])) ?></p>
                    <p><strong>Terakhir Update:</strong><br><?= date('d M Y H:i', strtotime($user['updated_at'])) ?></p>
                    <p><strong>Status:</strong>
                        <span class="badge <?= $user['is_active'] == 1 ? 'bg-success' : 'bg-danger' ?>">
                            <?= $user['is_active'] == 1 ? 'Aktif' : 'Tidak Aktif' ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Peringatan</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <ul class="mb-0 small">
                        <li>Mengubah email memerlukan verifikasi ulang</li>
                        <li>Reset password akan mengirim email ke user</li>
                        <li>Perubahan akan langsung berlaku</li>
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

    function resetPassword(userId) {
        Swal.fire({
            title: 'Reset Password',
            text: 'Apakah Anda yakin ingin mereset password user ini? Password baru akan dikirim ke email user.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                window.location.href = '<?= base_url('superadmin/users/reset-password/') ?>' + userId;
            }
        });
    }
</script>
<?= $this->endSection() ?>