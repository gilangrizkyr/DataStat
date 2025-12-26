<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1><i class="bi bi-gear me-2"></i>Kelola Dashboard: <?= esc($dashboard['dashboard_name']) ?></h1>
</div>

<div class="row">
    <!-- Statistics Panel -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistik Tersedia</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="search-statistic" placeholder="Cari statistik...">
                </div>
                <div id="statistics-list" class="statistics-list">
                    <?php if (!empty($available_statistics)): ?>
                        <?php foreach ($available_statistics as $statistic): ?>
                            <div class="statistic-item draggable" data-statistic-id="<?= $statistic['id'] ?>" data-statistic-name="<?= esc($statistic['statistic_name']) ?>" data-visualization-type="<?= $statistic['visualization_type'] ?>">
                                <div class="statistic-icon">
                                    <?php if ($statistic['visualization_type'] == 'chart'): ?>
                                        <i class="bi bi-bar-chart"></i>
                                    <?php elseif ($statistic['visualization_type'] == 'table'): ?>
                                        <i class="bi bi-table"></i>
                                    <?php elseif ($statistic['visualization_type'] == 'number'): ?>
                                        <i class="bi bi-hash"></i>
                                    <?php else: ?>
                                        <i class="bi bi-text-left"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="statistic-info">
                                    <h6 class="mb-0"><?= esc($statistic['statistic_name']) ?></h6>
                                    <small class="text-muted">
                                        <?php if ($statistic['metric_type'] == 'count'): ?>
                                            Jumlah
                                        <?php elseif ($statistic['metric_type'] == 'sum'): ?>
                                            Total
                                        <?php elseif ($statistic['metric_type'] == 'avg'): ?>
                                            Rata-rata
                                        <?php else: ?>
                                            <?= ucfirst($statistic['metric_type']) ?>
                                        <?php endif; ?>
                                        - <?= ucfirst($statistic['visualization_type']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">Belum ada statistik yang tersedia</p>
                            <a href="<?= base_url('owner/statistics/create') ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Buat Statistik
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Manual Widget Form -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-plus-square me-2"></i>Tambah Widget Manual</h6>
            </div>
            <div class="card-body">
                <form id="add-widget-form">
                    <div class="mb-3">
                        <label for="widget_type" class="form-label">Tipe Widget</label>
                        <select class="form-select" id="widget_type" name="widget_type" required>
                            <option value="">Pilih tipe widget</option>
                            <option value="text">Teks</option>
                            <option value="number">Angka</option>
                            <option value="chart">Grafik</option>
                            <option value="table">Tabel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="widget_title" class="form-label">Judul Widget</label>
                        <input type="text" class="form-control" id="widget_title" name="widget_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="widget_content" class="form-label">Konten Widget</label>
                        <textarea class="form-control" id="widget_content" name="widget_content" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Widget
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Dashboard Canvas -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-layout-text-window-reverse me-2"></i>Dashboard Canvas</h5>
                <div>
                    <button id="preview-btn" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-eye me-1"></i>Preview
                    </button>
                    <button id="save-layout-btn" class="btn btn-success btn-sm">
                        <i class="bi bi-check-circle me-1"></i>Simpan Layout
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="dashboard-canvas" class="dashboard-canvas">
                    <?php if (!empty($dashboard_widgets)): ?>
                        <?php foreach ($dashboard_widgets as $widget): ?>
                            <div class="dashboard-widget" data-widget-id="<?= $widget['id'] ?>">
                                <div class="widget-header">
                                    <h6 class="mb-0"><?= esc($widget['widget_title']) ?></h6>
                                    <div class="widget-actions">
                                        <button class="btn btn-sm btn-outline-warning edit-widget" data-widget-id="<?= $widget['id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger remove-widget" data-widget-id="<?= $widget['id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="widget-content">
                                    <?php if ($widget['widget_type'] == 'statistic'): ?>
                                        <!-- Render statistic widget -->
                                        <div class="statistic-widget">
                                            <?php if ($widget['visualization_type'] == 'number'): ?>
                                                <div class="text-center">
                                                    <h2 class="mb-0"><?= $widget['value'] ?? '0' ?></h2>
                                                    <small class="text-muted"><?= esc($widget['statistic_name']) ?></small>
                                                </div>
                                            <?php elseif ($widget['visualization_type'] == 'chart'): ?>
                                                <canvas class="chart-canvas" data-statistic-id="<?= $widget['statistic_id'] ?>"></canvas>
                                            <?php elseif ($widget['visualization_type'] == 'table'): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Kolom</th>
                                                                <th>Nilai</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Table data would be populated dynamically -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Render manual widget -->
                                        <div class="manual-widget">
                                            <?= $widget['widget_content'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-layout-text-window-reverse text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">Dashboard Kosong</h5>
                            <p class="text-muted">Seret statistik dari panel kiri atau tambahkan widget manual untuk memulai</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Widget Modal -->
<div class="modal fade" id="editWidgetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Widget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-widget-form">
                    <input type="hidden" id="edit-widget-id" name="widget_id">
                    <div class="mb-3">
                        <label for="edit-widget-title" class="form-label">Judul Widget</label>
                        <input type="text" class="form-control" id="edit-widget-title" name="widget_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-widget-content" class="form-label">Konten Widget</label>
                        <textarea class="form-control" id="edit-widget-content" name="widget_content" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="save-widget-changes">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Dashboard builder JavaScript would go here
    // Including drag and drop functionality, widget management, etc.
</script>

<?= $this->endSection() ?>