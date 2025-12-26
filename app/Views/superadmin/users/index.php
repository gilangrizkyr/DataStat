<!-- Menampikan data users untuk crud -->

<?= $this->extend('layouts/superadmin') ?>
<?= $this->section('content') ?>

<div class="page-title">
    <h1><i class="bi bi-people-fill me-2"></i> <?= esc($title) ?></h1>
    <p class="mb-0">Kelola semua pengguna sistem DataStat</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Daftar User</h5>
                <a href="<?= base_url('superadmin/users/create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Tambah User Baru
                </a>
            </div>
            <div class="card-body">

                <!-- Filter & Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau bidang..." value="<?= esc($search) ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" class="d-flex gap-2 justify-content-end">
                            <select name="status" class="form-select" style="width: auto;">
                                <option value="">Semua Status</option>
                                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                            </select>
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <?php if ($search || $status !== null && $status !== ''): ?>
                                <a href="<?= base_url('superadmin/users') ?>" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Nama Lengkap</th>
                                <th width="25%">Email</th>
                                <th width="15%">Bidang</th>
                                <th width="10%">Role</th>
                                <th width="10%">Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Tidak ada data user ditemukan</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $index => $user): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2" style="width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                    <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= esc($user['nama_lengkap']) ?></div>
                                                    <small class="text-muted">ID: <?= $user['id'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="bi bi-envelope me-1 text-muted"></i>
                                            <?= esc($user['email']) ?>
                                        </td>
                                        <td>
                                            <?php if ($user['bidang']): ?>
                                                <span class="badge bg-info"><?= esc($user['bidang']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-shield-check me-1"></i>
                                                <?= $user['role_count'] ?> Role<?= $user['role_count'] != 1 ? 's' : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active'] == 1): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Tidak Aktif
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= base_url('superadmin/users/detail/' . $user['id']) ?>"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= base_url('superadmin/users/edit/' . $user['id']) ?>"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDelete('<?= base_url('superadmin/users/delete/' . $user['id']) ?>', 'Apakah Anda yakin ingin menghapus user <?= esc($user['nama_lengkap']) ?>?')"
                                                    title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Stats Summary -->
                <?php if (!empty($users)): ?>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Total User</h6>
                                        <h4 class="mb-0 text-primary"><?= count($users) ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card success">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Aktif</h6>
                                        <h4 class="mb-0 text-success">
                                            <?= count(array_filter($users, fn($u) => $u['is_active'] == 1)) ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card danger">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon">
                                        <i class="bi bi-x-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Tidak Aktif</h6>
                                        <h4 class="mb-0 text-danger">
                                            <?= count(array_filter($users, fn($u) => $u['is_active'] == 0)) ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card warning">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon">
                                        <i class="bi bi-app-indicator"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Total Aplikasi</h6>
                                        <h4 class="mb-0 text-warning">
                                            <?= array_sum(array_column($users, 'app_count')) ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('.datatable')) {
            $('.datatable').DataTable().destroy();
        }

        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            pageLength: 25,
            order: [
                [1, 'asc']
            ],
            columnDefs: [{
                orderable: false,
                targets: [6]
            }],
            responsive: true,
            stateSave: true
        });
    });
</script>
<?= $this->endSection() ?>