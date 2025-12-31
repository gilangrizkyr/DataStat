<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Pengaturan Akun
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Flash Messages -->
                    <?= view('components/flash_messages') ?>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Fitur ini sedang dalam pengembangan.</strong> Semua fungsi pengaturan akun saat ini tidak dapat digunakan.
                    </div>

                    <form>
                        <!-- Theme Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">
                                    <i class="fas fa-palette me-2"></i>Tema Tampilan
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="theme" id="theme_light" value="light" disabled>
                                            <label class="form-check-label text-muted" for="theme_light">
                                                <i class="fas fa-sun me-1"></i>Terang
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="theme" id="theme_dark" value="dark" disabled>
                                            <label class="form-check-label text-muted" for="theme_dark">
                                                <i class="fas fa-moon me-1"></i>Gelap
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="theme" id="theme_auto" value="auto" disabled>
                                            <label class="form-check-label text-muted" for="theme_auto">
                                                <i class="fas fa-adjust me-1"></i>Otomatis
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Language Settings - Commented Out -->
                        <!--
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">
                                    <i class="fas fa-language me-2"></i>Bahasa
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="language" id="lang_id" value="id" disabled>
                                            <label class="form-check-label text-muted" for="lang_id">
                                                <i class="flag-icon flag-icon-id me-1"></i>Bahasa Indonesia
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="language" id="lang_en" value="en" disabled>
                                            <label class="form-check-label text-muted" for="lang_en">
                                                <i class="flag-icon flag-icon-us me-1"></i>English
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        -->

                        <!-- Timezone Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">
                                    <i class="fas fa-clock me-2"></i>Timezone
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-select" disabled>
                                            <option>Asia/Jakarta (WIB)</option>
                                            <option>Asia/Makassar (WITA)</option>
                                            <option>Asia/Jayapura (WIT)</option>
                                        </select>
                                        <div class="form-text text-muted">Pengaturan timezone tidak dapat diubah</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Interface Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">
                                    <i class="fas fa-desktop me-2"></i>Antarmuka
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="sidebar_collapsed" disabled>
                                            <label class="form-check-label text-muted" for="sidebar_collapsed">
                                                Sidebar Collapsed
                                            </label>
                                            <div class="form-text text-muted">Pengaturan sidebar tidak dapat diubah</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">
                                    <i class="fas fa-bell me-2"></i>Notifikasi
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="email_notifications" disabled>
                                            <label class="form-check-label text-muted" for="email_notifications">
                                                Email Notifications
                                            </label>
                                            <div class="form-text text-muted">Pengaturan notifikasi tidak dapat diubah</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button - Disabled -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" disabled>
                                    <i class="fas fa-save me-1"></i>Simpan Pengaturan
                                </button>
                                <a href="<?= base_url('profile') ?>" class="btn btn-secondary ms-2">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Profile
                                </a>
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

    .form-check-input:disabled {
        opacity: 0.5;
    }

    .form-select:disabled {
        background-color: #e9ecef;
        opacity: 0.5;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .alert-info {
        border-color: #0dcaf0;
        background-color: #cff4fc;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // All functionality disabled - show message
        console.log('Account settings functionality is currently disabled');
    });
</script>
<?= $this->endSection() ?>