<?= $this->extend('layouts/owner') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Konfirmasi Hapus Statistik</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian!</strong> Anda akan menghapus statistik berikut. Data yang sudah dihapus tidak dapat dikembalikan.
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card border-warning">
                                <div class="card-header bg-warning">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-bar"></i> <?= esc($statistic['stat_name']) ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Nama Statistik:</dt>
                                        <dd class="col-sm-8"><?= esc($statistic['stat_name']) ?></dd>

                                        <dt class="col-sm-4">Tipe Metrik:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge badge-info">
                                                <?= ucfirst(str_replace('_', ' ', $statistic['metric_type'])) ?>
                                            </span>
                                        </dd>

                                        <dt class="col-sm-4">Visualisasi:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge badge-secondary">
                                                <?= ucfirst(str_replace('_', ' ', $statistic['visualization_type'])) ?>
                                            </span>
                                        </dd>

                                        <dt class="col-sm-4">Status:</dt>
                                        <dd class="col-sm-8">
                                            <?php if ($statistic['is_active']): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </dd>

                                        <?php if ($statistic['description']): ?>
                                            <dt class="col-sm-4">Deskripsi:</dt>
                                            <dd class="col-sm-8"><?= esc($statistic['description']) ?></dd>
                                        <?php endif; ?>

                                        <dt class="col-sm-4">Dibuat:</dt>
                                        <dd class="col-sm-8"><?= date('d/m/Y H:i', strtotime($statistic['created_at'])) ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h5>Apakah Anda yakin?</h5>
                                    <p class="text-muted">Tindakan ini tidak dapat dibatalkan.</p>

                                    <div class="mt-4">
                                        <form action="<?= base_url('owner/statistics/delete/' . $statistic['id']) ?>" method="post" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-lg">
                                                <i class="fas fa-trash"></i> Ya, Hapus Statistik
                                            </button>
                                        </form>
                                        <a href="<?= base_url('owner/statistics') ?>" class="btn btn-secondary btn-lg ml-2">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>