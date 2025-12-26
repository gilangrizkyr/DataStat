<?= $this->extend('layouts/superadmin') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1> <i class="bi bi-person-gear me-2"></i><?= esc($title) ?></h1>
    <p class="mb-0">Edit Role Information</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil-fill me-2"></i>Edit Role</h5>
            </div>
            <div class="card-body">

                <form action="<?= base_url('superadmin/roles/update/' . $role['id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="role_name" class="form-label">
                            <i class="bi bi-person-badge me-1"></i>Role Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control <?= session('errors.role_name') ? 'is-invalid' : '' ?>"
                            id="role_name"
                            name="role_name"
                            value="<?= old('role_name', $role['role_name']) ?>"
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
                            rows="3"><?= old('description', $role['description']) ?></textarea>
                        <?php if (session('errors.description')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.description') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Update Role
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>