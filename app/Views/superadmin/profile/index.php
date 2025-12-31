<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        Profile Superadmin
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('/profile/edit') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                        <a href="<?= base_url('/profile/change-password') ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-key"></i> Ubah Password
                        </a>
                        <a href="<?= base_url('/profile/settings') ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-cog"></i> Pengaturan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?= view('components/flash_messages') ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <?php if ($user['avatar']): ?>
                                    <img src="<?= base_url($user['avatar']) ?>" alt="Avatar" class="img-thumbnail mb-3" style="width: 200px; height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="width: 200px; height: 200px; margin: 0 auto;">
                                        <i class="fas fa-user fa-5x text-muted"></i>
                                    </div>
                                <?php endif; ?>

                                <h4 class="mb-1"><?= esc($user['nama_lengkap']) ?></h4>
                                <p class="text-muted mb-2">Superadmin</p>

                                <?php if ($user['bidang']): ?>
                                    <p class="badge badge-primary mb-3"><?= esc($user['bidang']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Email</span>
                                            <span class="info-box-number"><?= esc($user['email']) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Role</span>
                                            <span class="info-box-number">Superadmin</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Bergabung Sejak</span>
                                            <span class="info-box-number">
                                                <?= date('d M Y', strtotime($user['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Terakhir Update</span>
                                            <span class="info-box-number">
                                                <?= date('d M Y H:i', strtotime($user['updated_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-shield-alt mr-2"></i>
                                                Hak Akses Superadmin
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled">
                                                        <li><i class="fas fa-check text-success mr-2"></i> Kelola User</li>
                                                        <li><i class="fas fa-check text-success mr-2"></i> Kelola Aplikasi</li>
                                                        <li><i class="fas fa-check text-success mr-2"></i> Kelola Role & Permission</li>
                                                        <li><i class="fas fa-check text-success mr-2"></i> Sistem Settings</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled">
                                                        <li><i class="fas fa-check text-success mr-2"></i> Log Activity Monitoring</li>
                                                        <li><i class="fas fa-check text-success mr-2"></i> Reports & Analytics</li>
                                                        <li><i class="fas fa-check text-success mr-2"></i> Backup & Maintenance</li>
                                                        <li><i class="fas fa-check text-success mr-2"></i> Full System Access</li>
                                                    </ul>
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
        </div>
    </div>
</div>

<?= $this->endSection() ?>