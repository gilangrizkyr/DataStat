<?= $this->extend('layouts/superadmin') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1> <i class="bi bi-person-gear me-2"></i><?= esc($title) ?></h1>
    <p class="mb-0">Create Role</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-lg me-2"></i>Form Tambah Role</h5>
            </div>
            <div class="card-body">

                <form action="<?= base_url('superadmin/roles/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="role_name" class="form-label">
                            <i class="bi bi-person-badge me-1"></i>Role Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control <?= session('errors.role_name') ? 'is-invalid' : '' ?>"
                            id="role_name"
                            name="role_name"
                            value="<?= old('role_name') ?>"
                            placeholder="Enter role name"
                            required>
                        <?php if (session('errors.role_name')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.role_name') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="bi bi-card-text me-1"></i>Description
                        </label>
                        <textarea
                            class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>"
                            id="description"
                            name="description"
                            placeholder="Enter role description"
                            rows="3"><?= old('description') ?></textarea>
                        <?php if (session('errors.description')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.description') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('superadmin/roles') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-plus me-1"></i>Simpan Role
                                </button>
                            </div>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-lightbulb me-1"></i>Tips:</h6>
                    <ul class="mb-0 small">
                        <li>Pastikan nama role yang dimasukkan belum terdaftar</li>
                        <li>Deskripsi role bersifat opsional untuk klasifikasi role</li>
                    </ul>
                </div>


<?= $this->endSection() ?>