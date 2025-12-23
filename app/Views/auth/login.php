<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<form action="<?= base_url('login') ?>" method="post" id="loginForm">
    <?= csrf_field() ?>
    
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-envelope"></i>
            </span>
            <input type="email" class="form-control" id="email" name="email" 
                   placeholder="your@email.com" required autofocus 
                   value="<?= old('email') ?>">
        </div>
        <?php if (isset($validation) && $validation->hasError('email')): ?>
            <div class="text-danger small mt-1"><?= $validation->getError('email') ?></div>
        <?php endif; ?>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bi bi-lock"></i>
            </span>
            <input type="password" class="form-control" id="password" name="password" 
                   placeholder="Enter your password" required>
            <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword('password')">
                <i class="bi bi-eye" id="toggleIcon"></i>
            </span>
        </div>
        <?php if (isset($validation) && $validation->hasError('password')): ?>
            <div class="text-danger small mt-1"><?= $validation->getError('password') ?></div>
        <?php endif; ?>
    </div>
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
        <label class="form-check-label" for="remember">
            Remember me
        </label>
    </div>
    
    <button type="submit" class="btn btn-primary btn-block">
        <i class="bi bi-box-arrow-in-right me-2"></i> Login
    </button>
</form>
<?= $this->endSection() ?>

<?= $this->section('footer') ?>
<div class="auth-footer">
    <p class="mb-0">
        Don't have an account? 
        <a href="<?= base_url('register') ?>">Register here</a>
    </p>
    <p class="mb-0 mt-2">
        <a href="<?= base_url('forgot-password') ?>">Forgot your password?</a>
    </p>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $('#loginForm').on('submit', function() {
        showLoading();
    });
</script>
<?= $this->endSection() ?>