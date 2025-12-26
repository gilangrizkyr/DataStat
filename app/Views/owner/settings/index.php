<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Pengaturan Workspace
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Flash Messages -->
                    <?= view('components/flash_messages') ?>

                    <form action="<?= base_url('owner/settings/update') ?>" method="post">
                        <?= csrf_field() ?>

                        <!-- Workspace Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Informasi Workspace
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="app_name" class="form-label">Nama Aplikasi</label>
                                            <input type="text" class="form-control" id="app_name" name="app_name" value="<?= esc($application['app_name'] ?? '') ?>" required>
                                            <div class="form-text">Masukkan nama aplikasi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bidang" class="form-label">Bidang</label>
                                            <input type="text" class="form-control" id="bidang" name="bidang" value="<?= esc($application['bidang'] ?? '') ?>" required>
                                            <div class="form-text">Masukkan bidang aplikasi</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-bell me-2"></i>Notifikasi
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="email_notifications"
                                                name="email_notifications" value="1"
                                                <?= ($settings['notifications']['email_enabled'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="email_notifications">
                                                Email Notifikasi
                                            </label>
                                            <div class="form-text">Kirim notifikasi via email</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="notify_upload"
                                                name="notify_upload" value="1"
                                                <?= ($settings['notifications']['dataset_upload'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="notify_upload">
                                                Upload Dataset
                                            </label>
                                            <div class="form-text">Notifikasi saat dataset diupload</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="notify_calculated"
                                                name="notify_calculated" value="1"
                                                <?= ($settings['notifications']['statistic_calculated'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="notify_calculated">
                                                Statistik Dihitung
                                            </label>
                                            <div class="form-text">Notifikasi saat statistik dihitung</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Retention Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-database me-2"></i>Retensi Data
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="auto_cleanup"
                                                name="auto_cleanup" value="1"
                                                <?= ($settings['data_retention']['auto_cleanup_enabled'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="auto_cleanup">
                                                Auto Cleanup Data Lama
                                            </label>
                                            <div class="form-text">Otomatis hapus data yang sudah lama</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="retention_days" class="form-label">Periode Retensi (hari)</label>
                                            <input type="number" class="form-control" id="retention_days"
                                                name="retention_days" min="30" max="3650"
                                                value="<?= $settings['data_retention']['retention_days'] ?? 365 ?>">
                                            <div class="form-text">Data yang lebih lama dari periode ini akan dihapus</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Feature Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-toggle-on me-2"></i>Fitur
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="public_dashboard"
                                                name="public_dashboard" value="1"
                                                <?= ($settings['features']['allow_public_dashboard'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="public_dashboard">
                                                Dashboard Publik
                                            </label>
                                            <div class="form-text">Izinkan dashboard dibagikan secara publik</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="data_export"
                                                name="data_export" value="1"
                                                <?= ($settings['features']['allow_data_export'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="data_export">
                                                Export Data
                                            </label>
                                            <div class="form-text">Izinkan export data dan statistik</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="api_access"
                                                name="api_access" value="1"
                                                <?= ($settings['features']['enable_api_access'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="api_access">
                                                API Access
                                            </label>
                                            <div class="form-text">Izinkan akses via API</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Display Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-desktop me-2"></i>Tampilan
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="records_per_page" class="form-label">Records per Halaman</label>
                                            <select class="form-select" id="records_per_page" name="records_per_page">
                                                <option value="25" <?= ($settings['display']['records_per_page'] ?? 50) == 25 ? 'selected' : '' ?>>25</option>
                                                <option value="50" <?= ($settings['display']['records_per_page'] ?? 50) == 50 ? 'selected' : '' ?>>50</option>
                                                <option value="100" <?= ($settings['display']['records_per_page'] ?? 50) == 100 ? 'selected' : '' ?>>100</option>
                                                <option value="200" <?= ($settings['display']['records_per_page'] ?? 50) == 200 ? 'selected' : '' ?>>200</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="default_chart" class="form-label">Tipe Chart Default</label>
                                            <select class="form-select" id="default_chart" name="default_chart">
                                                <option value="bar_chart" <?= ($settings['display']['default_chart_type'] ?? 'bar_chart') == 'bar_chart' ? 'selected' : '' ?>>Bar Chart</option>
                                                <option value="line_chart" <?= ($settings['display']['default_chart_type'] ?? 'bar_chart') == 'line_chart' ? 'selected' : '' ?>>Line Chart</option>
                                                <option value="pie_chart" <?= ($settings['display']['default_chart_type'] ?? 'bar_chart') == 'pie_chart' ? 'selected' : '' ?>>Pie Chart</option>
                                                <option value="area_chart" <?= ($settings['display']['default_chart_type'] ?? 'bar_chart') == 'area_chart' ? 'selected' : '' ?>>Area Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="date_format" class="form-label">Format Tanggal</label>
                                            <select class="form-select" id="date_format" name="date_format">
                                                <option value="d-m-Y" <?= ($settings['display']['date_format'] ?? 'd-m-Y') == 'd-m-Y' ? 'selected' : '' ?>>DD-MM-YYYY</option>
                                                <option value="m/d/Y" <?= ($settings['display']['date_format'] ?? 'd-m-Y') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                                <option value="Y-m-d" <?= ($settings['display']['date_format'] ?? 'd-m-Y') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan Pengaturan
                                </button>
                                <button type="button" class="btn btn-warning ms-2" onclick="resetSettings()">
                                    <i class="fas fa-undo me-1"></i>Reset ke Default
                                </button>
                                <div class="float-end">
                                    <button type="button" class="btn btn-outline-primary" onclick="exportSettings()">
                                        <i class="fas fa-download me-1"></i>Export Settings
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary ms-1" onclick="document.getElementById('importFile').click()">
                                        <i class="fas fa-upload me-1"></i>Import Settings
                                    </button>
                                    <input type="file" id="importFile" style="display: none;" accept=".json" onchange="importSettings(this)">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms for export/import -->
<form id="exportForm" action="<?= base_url('owner/settings/export') ?>" method="post" style="display: none;">
    <?= csrf_field() ?>
</form>

<form id="importForm" action="<?= base_url('owner/settings/import') ?>" method="post" enctype="multipart/form-data" style="display: none;">
    <?= csrf_field() ?>
    <input type="file" name="settings_file" id="settingsFileInput">
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

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .btn-outline-primary:hover,
    .btn-outline-secondary:hover {
        color: #fff;
    }

    .text-primary {
        color: #0d6efd !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Form validation
        $('form').on('submit', function(e) {
            const retentionDays = $('#retention_days').val();
            if (retentionDays < 30 || retentionDays > 3650) {
                e.preventDefault();
                alert('Periode retensi harus antara 30-3650 hari');
                return false;
            }
            return true;
        });
    });

    function resetSettings() {
        if (confirm('Apakah Anda yakin ingin mereset semua pengaturan ke default?')) {
            $.ajax({
                url: '<?= base_url('owner/settings/reset') ?>',
                type: 'POST',
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat mereset pengaturan');
                }
            });
        }
    }

    function exportSettings() {
        document.getElementById('exportForm').submit();
    }

    function importSettings(input) {
        const file = input.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('settings_file', file);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            $.ajax({
                url: '<?= base_url('owner/settings/import') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error importing settings: ' + xhr.responseText);
                }
            });
        }
    }
</script>
<?= $this->endSection() ?>