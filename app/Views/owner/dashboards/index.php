<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1><i class="bi bi-layout-text-window-reverse me-2"></i>Kelola Dashboard</h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Dashboard</h5>
                <a href="<?= base_url('owner/dashboards/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Buat Dashboard Baru
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($dashboards)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-layout-text-window-reverse" style="font-size: 4rem; color: #6c757d;"></i>
                        <h4 class="mt-3 text-muted">Belum ada dashboard</h4>
                        <p class="text-muted">Mulai buat dashboard pertama Anda untuk menampilkan statistik dan data.</p>
                        <a href="<?= base_url('owner/dashboards/create') ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Buat Dashboard Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Nama Dashboard</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th>Widget</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashboards as $dashboard): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="bi bi-layout-text-window-reverse text-primary" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">
                                                        <a href="<?= base_url('owner/dashboards/manage/' . $dashboard['id']) ?>" class="text-decoration-none">
                                                            <?= esc($dashboard['dashboard_name']) ?>
                                                        </a>
                                                    </h6>
                                                    <?php if ($dashboard['is_default']): ?>
                                                        <small class="badge bg-success">Default</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= esc($dashboard['description'] ?? 'Tidak ada deskripsi') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($dashboard['is_public']): ?>
                                                <span class="badge bg-info">Publik</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Private</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?= $dashboard['widget_count'] ?? 0 ?> widget
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d M Y', strtotime($dashboard['created_at'])) ?><br>
                                                oleh <?= esc($dashboard['creator_name']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= base_url('owner/dashboards/manage/' . $dashboard['id']) ?>" class="btn btn-sm btn-outline-primary" title="Kelola">
                                                    <i class="bi bi-gear"></i>
                                                </a>
                                                <a href="<?= base_url('owner/dashboards/edit/' . $dashboard['id']) ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button onclick="confirmDelete('<?= base_url('owner/dashboards/delete/' . $dashboard['id']) ?>')" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>