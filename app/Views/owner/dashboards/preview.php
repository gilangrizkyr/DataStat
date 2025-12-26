<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1><i class="bi bi-eye me-2"></i>Preview Dashboard: <?= esc($dashboard['dashboard_name']) ?></h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-layout-text-window-reverse me-2"></i>Dashboard Preview</h5>
                <div>
                    <a href="<?= base_url('owner/dashboards/manage/' . $dashboard['id']) ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-gear me-1"></i>Kelola Dashboard
                    </a>
                    <a href="<?= base_url('owner/dashboards/edit/' . $dashboard['id']) ?>" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit Info
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($dashboard_widgets)): ?>
                    <div class="dashboard-preview">
                        <?php foreach ($dashboard_widgets as $widget): ?>
                            <div class="dashboard-widget-preview">
                                <div class="widget-header-preview">
                                    <h6 class="mb-0"><?= esc($widget['widget_title']) ?></h6>
                                </div>
                                <div class="widget-content-preview">
                                    <?php if ($widget['widget_type'] == 'statistic'): ?>
                                        <!-- Render statistic widget -->
                                        <div class="statistic-widget-preview">
                                            <?php if ($widget['visualization_type'] == 'number'): ?>
                                                <div class="text-center">
                                                    <h2 class="mb-0 text-primary"><?= $widget['value'] ?? '0' ?></h2>
                                                    <small class="text-muted"><?= esc($widget['statistic_name']) ?></small>
                                                </div>
                                            <?php elseif ($widget['visualization_type'] == 'chart'): ?>
                                                <div class="chart-container">
                                                    <canvas class="chart-canvas-preview" data-statistic-id="<?= $widget['statistic_id'] ?>"></canvas>
                                                </div>
                                            <?php elseif ($widget['visualization_type'] == 'table'): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Kolom</th>
                                                                <th>Nilai</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Table data would be populated dynamically -->
                                                            <tr>
                                                                <td>Contoh Data 1</td>
                                                                <td>100</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Contoh Data 2</td>
                                                                <td>200</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Render manual widget -->
                                        <div class="manual-widget-preview">
                                            <?= $widget['widget_content'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-layout-text-window-reverse text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-muted">Dashboard Kosong</h4>
                        <p class="text-muted">Belum ada widget yang ditambahkan ke dashboard ini.</p>
                        <a href="<?= base_url('owner/dashboards/manage/' . $dashboard['id']) ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Widget
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Info Card -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Dashboard</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <p class="mb-1"><strong>Nama:</strong></p>
                        <p class="text-muted"><?= esc($dashboard['dashboard_name']) ?></p>
                    </div>
                    <div class="col-sm-6">
                        <p class="mb-1"><strong>Status:</strong></p>
                        <p class="text-muted">
                            <?php if ($dashboard['is_default']): ?>
                                <span class="badge bg-success">Default</span>
                            <?php endif; ?>
                            <?php if ($dashboard['is_public']): ?>
                                <span class="badge bg-info">Publik</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Private</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <p class="mb-1"><strong>Jumlah Widget:</strong></p>
                        <p class="text-muted"><?= $dashboard['widget_count'] ?? 0 ?> widget</p>
                    </div>
                    <div class="col-sm-6">
                        <p class="mb-1"><strong>Dibuat:</strong></p>
                        <p class="text-muted small">
                            <?= date('d M Y H:i', strtotime($dashboard['created_at'])) ?>
                        </p>
                    </div>
                </div>
                <?php if ($dashboard['description']): ?>
                    <div class="row">
                        <div class="col-12">
                            <p class="mb-1"><strong>Deskripsi:</strong></p>
                            <p class="text-muted small"><?= esc($dashboard['description']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-share me-2"></i>Bagikan Dashboard</h6>
            </div>
            <div class="card-body">
                <?php if ($dashboard['is_public']): ?>
                    <p class="mb-3">Dashboard ini dapat diakses oleh semua pengguna aplikasi.</p>
                    <div class="input-group">
                        <input type="text" class="form-control" id="share-url" value="<?= base_url('viewer/dashboard/view/' . $dashboard['id']) ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyShareUrl()">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">URL di atas dapat dibagikan kepada pengguna lain untuk melihat dashboard ini.</small>
                <?php else: ?>
                    <p class="mb-3">Dashboard ini bersifat private dan hanya dapat diakses oleh Anda dan pengguna yang diundang.</p>
                    <a href="<?= base_url('owner/dashboards/edit/' . $dashboard['id']) ?>" class="btn btn-outline-primary">
                        <i class="bi bi-unlock me-1"></i>Jadikan Publik
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function copyShareUrl() {
        const shareUrl = document.getElementById('share-url');
        shareUrl.select();
        shareUrl.setSelectionRange(0, 99999);
        document.execCommand('copy');

        // Show feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> Disalin!';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');

        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }

    // Initialize charts if any exist
    document.addEventListener('DOMContentLoaded', function() {
        const chartCanvases = document.querySelectorAll('.chart-canvas-preview');
        chartCanvases.forEach(canvas => {
            // Chart initialization would go here
            // This would typically involve fetching data and rendering charts
        });
    });
</script>

<?= $this->endSection() ?>