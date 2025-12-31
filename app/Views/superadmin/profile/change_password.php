<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-key mr-2"></i>
                        Ubah Password Superadmin
                    </h3>
                </div>
                <div class="card-body">
                    <?= view('components/flash_messages') ?>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Informasi:</strong> Password baru harus memiliki minimal 8 karakter dan mengandung kombinasi huruf besar, huruf kecil, angka, dan simbol.
                    </div>

                    <form action="<?= base_url('/profile/change-password') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_password">Password Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control <?= ($validation->hasError('current_password')) ? 'is-invalid' : '' ?>"
                                        id="current_password" name="current_password" required>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('current_password') ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="new_password">Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control <?= ($validation->hasError('new_password')) ? 'is-invalid' : '' ?>"
                                        id="new_password" name="new_password" required
                                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                        title="Password harus minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, angka, dan simbol">
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('new_password') ?>
                                    </div>
                                    <small class="form-text text-muted">
                                        Minimal 8 karakter, kombinasi huruf besar, kecil, angka, dan simbol
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control <?= ($validation->hasError('confirm_password')) ? 'is-invalid' : '' ?>"
                                        id="confirm_password" name="confirm_password" required>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('confirm_password') ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="show_password" onchange="togglePassword()">
                                        <label class="custom-control-label" for="show_password">Tampilkan password</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-shield-alt mr-2"></i>
                                            Kebijakan Password
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li id="length-check">
                                                <i class="fas fa-times text-danger mr-2"></i>
                                                Minimal 8 karakter
                                            </li>
                                            <li id="uppercase-check">
                                                <i class="fas fa-times text-danger mr-2"></i>
                                                Setidaknya 1 huruf besar (A-Z)
                                            </li>
                                            <li id="lowercase-check">
                                                <i class="fas fa-times text-danger mr-2"></i>
                                                Setidaknya 1 huruf kecil (a-z)
                                            </li>
                                            <li id="number-check">
                                                <i class="fas fa-times text-danger mr-2"></i>
                                                Setidaknya 1 angka (0-9)
                                            </li>
                                            <li id="symbol-check">
                                                <i class="fas fa-times text-danger mr-2"></i>
                                                Setidaknya 1 simbol (@$!%*?&)
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Ubah Password
                            </button>
                            <a href="<?= base_url('/profile') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const inputs = ['current_password', 'new_password', 'confirm_password'];
        const showPassword = document.getElementById('show_password').checked;

        inputs.forEach(id => {
            document.getElementById(id).type = showPassword ? 'text' : 'password';
        });
    }

    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value;

        // Length check
        const lengthCheck = password.length >= 8;
        document.getElementById('length-check').innerHTML =
            `<i class="fas fa-${lengthCheck ? 'check text-success' : 'times text-danger'} mr-2"></i> Minimal 8 karakter`;

        // Uppercase check
        const uppercaseCheck = /[A-Z]/.test(password);
        document.getElementById('uppercase-check').innerHTML =
            `<i class="fas fa-${uppercaseCheck ? 'check text-success' : 'times text-danger'} mr-2"></i> Setidaknya 1 huruf besar (A-Z)`;

        // Lowercase check
        const lowercaseCheck = /[a-z]/.test(password);
        document.getElementById('lowercase-check').innerHTML =
            `<i class="fas fa-${lowercaseCheck ? 'check text-success' : 'times text-danger'} mr-2"></i> Setidaknya 1 huruf kecil (a-z)`;

        // Number check
        const numberCheck = /\d/.test(password);
        document.getElementById('number-check').innerHTML =
            `<i class="fas fa-${numberCheck ? 'check text-success' : 'times text-danger'} mr-2"></i> Setidaknya 1 angka (0-9)`;

        // Symbol check
        const symbolCheck = /[@$!%*?&]/.test(password);
        document.getElementById('symbol-check').innerHTML =
            `<i class="fas fa-${symbolCheck ? 'check text-success' : 'times text-danger'} mr-2"></i> Setidaknya 1 simbol (@$!%*?&)`;
    });
</script>

<?= $this->endSection() ?>