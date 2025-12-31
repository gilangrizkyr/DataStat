<?= $this->extend('layouts/superadmin') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-edit mr-2"></i>
                        Edit Profile Superadmin
                    </h3>
                </div>
                <div class="card-body">
                    <?= view('components/flash_messages') ?>

                    <form action="<?= base_url('/profile/update') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= ($validation->hasError('nama_lengkap')) ? 'is-invalid' : '' ?>"
                                        id="nama_lengkap" name="nama_lengkap"
                                        value="<?= old('nama_lengkap', $user['nama_lengkap']) ?>" required>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('nama_lengkap') ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control <?= ($validation->hasError('email')) ? 'is-invalid' : '' ?>"
                                        id="email" name="email"
                                        value="<?= old('email', $user['email']) ?>" required>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('email') ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="bidang">Bidang</label>
                                    <input type="text" class="form-control"
                                        id="bidang" name="bidang"
                                        value="<?= old('bidang', $user['bidang']) ?>"
                                        placeholder="Contoh: IT, Keuangan, dll">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Avatar</label>
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <?php if ($user['avatar']): ?>
                                                <img src="<?= base_url($user['avatar']) ?>" alt="Avatar" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                                    <i class="fas fa-user fa-3x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="avatar" name="avatar" accept="image/*">
                                            <label class="custom-file-label" for="avatar">Pilih gambar...</label>
                                        </div>
                                        <small class="form-text text-muted">Format: JPG, PNG, Max: 2MB</small>

                                        <?php if ($user['avatar']): ?>
                                            <div class="mt-2">
                                                <button type="submit" formaction="<?= base_url('/profile/delete-avatar') ?>" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus avatar?')">
                                                    <i class="fas fa-trash"></i> Hapus Avatar
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
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
    // Update file input label
    $('#avatar').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Pilih gambar...');
    });
</script>

<?= $this->endSection() ?>