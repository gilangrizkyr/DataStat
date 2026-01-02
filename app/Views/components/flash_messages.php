<!-- Flash Messages Component -->

<?php if (session()->getFlashdata('success')): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= session()->getFlashdata('success') ?>',
                confirmButtonColor: '#198754',
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= session()->getFlashdata('error') ?>',
                confirmButtonColor: '#dc3545'
            });
        });
    </script>
<?php endif; ?>

<?php if (session()->getFlashdata('warning')): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: '<?= session()->getFlashdata('warning') ?>',
                confirmButtonColor: '#ffc107'
            });
        });
    </script>
<?php endif; ?>

<?php if (session()->getFlashdata('info')): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'info',
                title: 'Informasi!',
                text: '<?= session()->getFlashdata('info') ?>',
                confirmButtonColor: '#0dcaf0'
            });
        });
    </script>
<?php endif; ?>

<?php if (session()->getFlashdata('validation')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Validation Errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach (session()->getFlashdata('validation') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('email_debug')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-envelope me-2"></i>
        <strong>Email Debug:</strong> <?= esc(session()->getFlashdata('email_debug')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('email_error_details')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Email Error Details:</strong>
        <pre class="mt-2 mb-0" style="font-size: 0.875rem; white-space: pre-wrap;"><?= esc(session()->getFlashdata('email_error_details')) ?></pre>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>