<?= $this->extend('layouts/viewer') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Ubah Password
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Flash Messages -->
                    <?= view('components/flash_messages') ?>

                    <form action="<?= base_url('profile/change-password') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-8 mx-auto">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">
                                        <i class="fas fa-lock me-1"></i>Password Saat Ini <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control <?= ($validation->hasError('current_password')) ? 'is-invalid' : '' ?>"
                                        id="current_password" name="current_password" required>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('current_password') ?>
                                    </div>
                                    <div class="form-text">Masukkan password yang sedang digunakan</div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">
                                        <i class="fas fa-key me-1"></i>Password Baru <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control <?= ($validation->hasError('new_password')) ? 'is-invalid' : '' ?>"
                                            id="new_password" name="new_password" required placeholder="Masukkan password baru">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle_new_password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('new_password') ?>
                                    </div>
                                    <div class="form-text">
                                        Password minimal 8 karakter, harus mengandung huruf besar, huruf kecil, angka, dan simbol
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-check-circle me-1"></i>Konfirmasi Password Baru <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control <?= ($validation->hasError('confirm_password')) ? 'is-invalid' : '' ?>"
                                            id="confirm_password" name="confirm_password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggle_confirm_password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('confirm_password') ?>
                                    </div>
                                    <div class="form-text">Ulangi password baru untuk konfirmasi</div>
                                </div>

                                <!-- Password Strength Indicator -->
                                <div class="mb-3">
                                    <div class="password-strength">
                                        <div class="progress" style="height: 8px;">
                                            <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small id="password-strength-text" class="form-text text-muted">Kekuatan password</small>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Simpan Password Baru
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

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .password-strength .progress-bar {
        transition: width 0.3s ease;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // Toggle password visibility for new password
        $('#toggle_new_password').on('click', function() {
            const input = $('#new_password');
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        // Toggle password visibility for confirm password
        $('#toggle_confirm_password').on('click', function() {
            const input = $('#confirm_password');
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        // Password strength checker
        $('#new_password').on('input', function() {
            const password = $(this).val();
            const strength = checkPasswordStrength(password);
            updatePasswordStrengthIndicator(strength);
        });

        // Confirm password validation
        $('#confirm_password').on('input', function() {
            const newPassword = $('#new_password').val();
            const confirmPassword = $(this).val();

            if (confirmPassword && newPassword !== confirmPassword) {
                $(this).addClass('is-invalid');
                if ($(this).parent().next('.invalid-feedback').length === 0) {
                    $(this).parent().after('<div class="invalid-feedback">Password konfirmasi tidak cocok</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).parent().next('.invalid-feedback').remove();
            }
        });

        // New password validation (also trigger confirm password check)
        $('#new_password').on('input', function() {
            const confirmPassword = $('#confirm_password').val();
            if (confirmPassword) {
                $('#confirm_password').trigger('input');
            }
        });

        // Form validation
        $('form').on('submit', function(e) {
            const currentPassword = $('#current_password').val().trim();
            const newPassword = $('#new_password').val().trim();
            const confirmPassword = $('#confirm_password').val().trim();

            if (!currentPassword || !newPassword || !confirmPassword) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
                return false;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Password konfirmasi tidak cocok dengan password baru.');
                return false;
            }

            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password baru minimal 8 karakter.');
                return false;
            }

            return true;
        });
    });

    function checkPasswordStrength(password) {
        let strength = 0;

        // Length check
        if (password.length >= 8) strength += 25;

        // Lowercase check
        if (/[a-z]/.test(password)) strength += 25;

        // Uppercase check
        if (/[A-Z]/.test(password)) strength += 25;

        // Number check
        if (/\d/.test(password)) strength += 12.5;

        // Special character check
        if (/[@$!%*?&]/.test(password)) strength += 12.5;

        return Math.min(strength, 100);
    }

    function updatePasswordStrengthIndicator(strength) {
        const bar = $('#password-strength-bar');
        const text = $('#password-strength-text');

        bar.removeClass('bg-danger bg-warning bg-info bg-success');

        if (strength < 20) {
            bar.addClass('bg-danger');
            text.text('Sangat lemah');
        } else if (strength < 40) {
            bar.addClass('bg-warning');
            text.text('Lemah');
        } else if (strength < 60) {
            bar.addClass('bg-warning');
            text.text('Sedang');
        } else if (strength < 80) {
            bar.addClass('bg-info');
            text.text('Kuat');
        } else {
            bar.addClass('bg-success');
            text.text('Sangat kuat');
        }

        bar.css('width', strength + '%');
    }
</script>
<?= $this->endSection() ?>