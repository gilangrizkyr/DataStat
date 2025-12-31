<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog mr-2"></i>
                        Pengaturan Akun Superadmin
                    </h3>
                </div>
                <div class="card-body">
                    <?= view('components/flash_messages') ?>

                    <form action="<?= base_url('/profile/settings') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="theme">Tema <span class="text-danger">*</span></label>
                                    <select class="form-control <?= ($validation->hasError('theme')) ? 'is-invalid' : '' ?>"
                                            id="theme" name="theme" required>
                                        <option value="">Pilih Tema</option>
                                        <option value="light" <?= old('theme', $user['theme'] ?? 'light') == 'light' ? 'selected' : '' ?>>Terang</option>
                                        <option value="dark" <?= old('theme', $user['theme'] ?? 'light') == 'dark' ? 'selected' : '' ?>>Gelap</option>
                                        <option value="auto" <?= old('theme', $user['theme'] ?? 'light') == 'auto' ? 'selected' : '' ?>>Otomatis</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('theme') ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="language">Bahasa <span class="text-danger">*</span></label>
                                    <select class="form-control <?= ($validation->hasError('language')) ? 'is-invalid' : '' ?>"
                                            id="language" name="language" required>
                                        <option value="">Pilih Bahasa</option>
                                        <option value="id" <?= old('language', $user['language'] ?? 'id') == 'id' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                                        <option value="en" <?= old('language', $user['language'] ?? 'id') == 'en' ? 'selected' : '' ?>>English</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('language') ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="timezone">Timezone <span class="text-danger">*</span></label>
                                    <select class="form-control <?= ($validation->hasError('timezone')) ? 'is-invalid' : '' ?>"
                                            id="timezone" name="timezone" required>
                                        <option value="">Pilih Timezone</option>
                                        <option value="Asia/Jakarta" <?= old('timezone', $user['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : '' ?>>WIB (Jakarta)</option>
                                        <option value="Asia/Makassar" <?= old('timezone', $user['timezone'] ?? 'Asia/Jakarta') == 'Asia/Makassar' ? 'selected' : '' ?>>WITA (Makassar)</option>
                                        <option value="Asia/Jayapura" <?= old('timezone', $user['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jayapura' ? 'selected' : '' ?>>WIT (Jayapura)</option>
                                        <option value="UTC" <?= old('timezone', $user['timezone'] ?? 'Asia/Jakarta') == 'UTC' ? 'selected' : '' ?>>UTC</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('timezone') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Preferensi Tampilan</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="sidebar_collapsed"
                                               name="sidebar_collapsed" value="1"
                                               <?= old('sidebar_collapsed', $user['sidebar_collapsed'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="sidebar_collapsed">
                                            Sembunyikan sidebar secara default saat login
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Aktifkan untuk memperluas area kerja secara otomatis
                                    </small>
                                </div>

                                <div class="card bg-light mt-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Informasi Pengaturan
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-palette text-primary mr-2"></i> Tema akan diterapkan di seluruh aplikasi</li>
                                            <li><i class="fas fa-language text-primary mr-2"></i> Bahasa mempengaruhi tampilan menu dan pesan</li>
                                            <li><i class="fas fa-clock text-primary mr-2"></i> Timezone mempengaruhi format tanggal dan waktu</li>
                                            <li><i class="fas fa-columns text-primary mr-2"></i> Preferensi sidebar disimpan per user</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Pengaturan
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
// Preview theme changes
$('#theme').on('change', function() {
    const theme = $(this).val();
    if (theme === 'dark') {
        $('body').addClass('dark-mode');
    } else if (theme === 'light') {
        $('body').removeClass('dark-mode');
    } else {
        // Auto theme - detect system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            $('body').addClass('dark-mode');
        } else {
            $('body').removeClass('dark-mode');
        }
    }
});
</script>

<?= $this->endSection() ?>
