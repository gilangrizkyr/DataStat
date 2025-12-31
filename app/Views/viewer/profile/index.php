<?= $this->extend('layouts/viewer') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Profile Saya</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('profile/edit') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                        <a href="<?= base_url('profile/settings') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-cog"></i> Pengaturan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="icon fas fa-check"></i> <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <i class="icon fas fa-ban"></i> <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4">
                            <!-- Profile Picture -->
                            <div class="card">
                                <div class="card-body text-center">
                                    <?php if ($user['avatar']): ?>
                                        <img src="<?= base_url($user['avatar']) ?>" alt="Avatar" class="img-circle elevation-2" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                            <i class="fas fa-user fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>

                                    <h4 class="mt-3 mb-1"><?= esc(session()->get('nama_lengkap') ?: $user['nama_lengkap']) ?></h4>
                                    <p class="text-muted mb-3">
                                        <i class="fas fa-envelope mr-2"></i> <?= esc(session()->get('email') ?: $user['email']) ?>
                                    </p>

                                    <div class="row">
                                        <div class="col-6">
                                            <a href="<?= base_url('profile/change-password') ?>" class="btn btn-warning btn-sm btn-block">
                                                <i class="fas fa-key"></i> Ubah Password
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn btn-info btn-sm btn-block" onclick="uploadAvatar()">
                                                <i class="fas fa-camera"></i> Upload Foto
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <!-- Profile Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Informasi Profile</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-3">Nama Lengkap</dt>
                                        <dd class="col-sm-9"><?= esc(session()->get('nama_lengkap') ?: $user['nama_lengkap']) ?></dd>

                                        <dt class="col-sm-3">Email</dt>
                                        <dd class="col-sm-9"><?= esc(session()->get('email') ?: $user['email']) ?></dd>

                                        <dt class="col-sm-3">Bidang</dt>
                                        <dd class="col-sm-9"><?= esc(session()->get('bidang') ?: $user['bidang'] ?? '-') ?></dd>

                                        <dt class="col-sm-3">Role</dt>
                                        <dd class="col-sm-9">
                                            <span class="badge badge-primary">
                                                <?= ucfirst(esc($user['role_name'] ?? 'user')) ?>
                                            </span>
                                        </dd>

                                        <dt class="col-sm-3">Bergabung Sejak</dt>
                                        <dd class="col-sm-9">
                                            <?= date('d F Y', strtotime($user['created_at'])) ?>
                                        </dd>

                                        <dt class="col-sm-3">Terakhir Update</dt>
                                        <dd class="col-sm-9">
                                            <?= $user['updated_at'] ? date('d F Y H:i', strtotime($user['updated_at'])) : '-' ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <!-- Account Settings -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Pengaturan Akun</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-palette mr-1"></i> Tema</strong>
                                            <p class="text-muted">
                                                <?= ucfirst($user['theme'] ?? 'light') ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-language mr-1"></i> Bahasa</strong>
                                            <p class="text-muted">
                                                <?= strtoupper($user['language'] ?? 'id') ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-globe mr-1"></i> Timezone</strong>
                                            <p class="text-muted">
                                                <?= $user['timezone'] ?? 'Asia/Jakarta' ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-toggle-on mr-1"></i> Sidebar Collapsed</strong>
                                            <p class="text-muted">
                                                <?= ($user['sidebar_collapsed'] ?? 0) ? 'Ya' : 'Tidak' ?>
                                            </p>
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

<!-- Modal Upload Avatar -->
<div class="modal fade" id="avatarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Avatar</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="<?= base_url('profile/upload-avatar') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="avatar">Pilih File Gambar</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*" required>
                        <small class="form-text text-muted">Format: JPG, PNG, Max: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function uploadAvatar() {
        $('#avatarModal').modal('show');
    }
</script>

<?= $this->endSection() ?>