<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Flash Messages -->
                    <?= view('components/flash_messages') ?>

                    <form action="<?= base_url('profile/update') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <!-- Avatar Section -->
                            <div class="col-md-4 mb-4">
                                <div class="text-center">
                                    <?php if ($user['avatar']): ?>
                                        <img src="<?= base_url($user['avatar']) ?>" alt="Avatar" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px;">
                                            <i class="fas fa-user fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('avatar').click()">
                                            <i class="fas fa-camera me-1"></i>Pilih Avatar
                                        </button>
                                        <?php if ($user['avatar']): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm ms-1" onclick="deleteAvatar()">
                                                <i class="fas fa-trash me-1"></i>Hapus
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-text">
                                        Format: JPG, PNG. Maksimal 2MB
                                    </div>
                                </div>
                            </div>

                            <!-- Form Fields -->
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_lengkap" class="form-label">
                                            <i class="fas fa-user me-1"></i>Nama Lengkap <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control <?= ($validation->hasError('nama_lengkap')) ? 'is-invalid' : '' ?>"
                                            id="nama_lengkap" name="nama_lengkap" value="<?= old('nama_lengkap', $user['nama_lengkap']) ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('nama_lengkap') ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-1"></i>Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control <?= ($validation->hasError('email')) ? 'is-invalid' : '' ?>"
                                            id="email" name="email" value="<?= old('email', $user['email']) ?>" required>
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('email') ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="bidang" class="form-label">
                                            <i class="fas fa-briefcase me-1"></i>Bidang
                                        </label>
                                        <input type="text" class="form-control <?= ($validation->hasError('bidang')) ? 'is-invalid' : '' ?>"
                                            id="bidang" name="bidang" value="<?= old('bidang', $user['bidang']) ?>"
                                            placeholder="Contoh: IT, Keuangan, dll">
                                        <div class="invalid-feedback">
                                            <?= $validation->getError('bidang') ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-user-tag me-1"></i>Role
                                        </label>
                                        <input type="text" class="form-control" value="<?= ucfirst(session()->get('role_name')) ?>" readonly>
                                        <div class="form-text">Role tidak dapat diubah</div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Simpan Perubahan
                                        </button>
                                        <a href="<?= base_url('profile') ?>" class="btn btn-secondary ms-2">
                                            <i class="fas fa-arrow-left me-1"></i>Kembali
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Avatar Form -->
<form id="deleteAvatarForm" action="<?= base_url('profile/delete-avatar') ?>" method="post" style="display: none;">
    <?= csrf_field() ?>
</form>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .rounded-circle {
        border: 3px solid #e9ecef;
    }

    .btn-outline-primary:hover,
    .btn-outline-danger:hover {
        color: #fff;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Avatar preview
        $('#avatar').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update preview (you might want to add a preview image element)
                    console.log('Avatar selected:', file.name);
                };
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        $('form').on('submit', function(e) {
            const namaLengkap = $('#nama_lengkap').val().trim();
            const email = $('#email').val().trim();

            if (!namaLengkap || !email) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
                return false;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Format email tidak valid.');
                return false;
            }

            return true;
        });
    });

    function deleteAvatar() {
        if (confirm('Apakah Anda yakin ingin menghapus avatar?')) {
            document.getElementById('deleteAvatarForm').submit();
        }
    }
</script>
<?= $this->endSection() ?>