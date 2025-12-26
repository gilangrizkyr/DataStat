<?= $this->extend('layouts/superadmin') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1> <i class="bi bi-person-gear me-2"></i><?= esc($title) ?></h1>
    <p class="mb-0">Manajemen Roles dan Permissions</p>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Daftar Roles</h5>
                <a href="<?= base_url('superadmin/roles/create') ?>" class="btn btn-sm btn-success float-end">
                    <i class="bi bi-plus-lg"></i> Tambah Role
                </a>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Permission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($roles) && is_array($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><?= esc($role['id']) ?></td>
                                    <td><?= esc($role['role_name']) ?></td>
                                    <td><?= esc($role['description']) ?></td>
                                    <td><?= esc($role['permissions']) ?></td>
                                    <td>
                                        <a href="<?= base_url('superadmin/roles/edit/' . $role['id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a>
                                        <a href="<?= base_url('superadmin/roles/delete/' . $role['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this role?');">
                                            <i class="bi bi-trash-fill"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No roles found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>



<?= $this->endSection() ?>