<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<form action="<?= base_url('register') ?>" method="post" id="registerForm">
    <?= csrf_field() ?>
    
    <div class="mb-3">
        <label for="nama_lengkap" class="form-label">Full Name</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-person"></i>
            </span>
            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                   placeholder="Your full name" required autofocus 
                   value="<?= old('nama_lengkap') ?>">
        </div>
        <?php if (isset($validation) && $validation->hasError('nama_lengkap')): ?>
            <div class="text-danger small mt-1"><?= $validation->getError('nama_lengkap') ?></div>
        <?php endif; ?>
    </div>
    
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-envelope"></i>
            </span>
            <input type="email" class="form-control" id="email" name="email" 
                   placeholder="your@email.com" required 
                   value="<?= old('email') ?>">
        </div>
        <?php if (isset($validation) && $validation->hasError('email')): ?>
            <div class="text-danger small mt-1"><?= $validation->getError('email') ?></div>
        <?php endif; ?>
    </div>
    
    <div class="mb-3">
        <label for="bidang" class="form-label">Department/Field</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-briefcase"></i>
            </span>
            <input type="text" class="form-control" id="bidang" name="bidang" 
                   placeholder="e.g., IT, Finance, Marketing" 
                   value="<?= old('bidang') ?>">
        </div>
        <?php if (isset($validation) && $validation->hasError('bidang')): ?>
            <div class="text-danger small mt-1"><?= $validation->getError('bidang') ?></div>
        <?php endif; ?>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-lock"></i>
            </span>
            <input type="password" class="form-control" id="password" name="password" 
                   placeholder="Min. 6 characters" required>
            <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword('password')">
                <i class="bi bi-eye"></i>
            </span>
        </div>
        <?php if (isset($validation) && $validation->hasError('password')): ?>
            <div class="text-danger small mt-1"><?= $validation->getError('password') ?></div>
        <?php endif; ?>
    </div>
    
    <div class="mb-3">
        <label for="password_confirm" class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-lock-fill"></i>
            </span>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                   placeholder="Re-enter password" required>
            <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword('password_confirm')">
                <i class="bi bi-eye"></i>
            </span>
        </div>
        <?php if (isset($validation) && $validation->hasError('password_confirm')): ?>
            <div class="text-danger small mt-1"><?= $validation->getError('password_confirm') ?></div>
        <?php endif; ?>
    </div>
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
        <label class="form-check-label" for="terms">
            I agree to the <a href="#" target="_blank">Terms and Conditions</a>
        </label>
    </div>
    
    <button type="submit" class="btn btn-primary btn-block">
        <i class="bi bi-person-plus me-2"></i> Register
    </button>
</form>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<div class="auth-footer">
    <p class="mb-0">
        Already have an account? 
        <a href="<?= base_url('login') ?>">Login here</a>
    </p>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $('#registerForm').on('submit', function() {
        showLoading();
    });
</script>
<?= $this->endSection() ?>