<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kelola User Workspace</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('owner/users/invite') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Invite User
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?= $this->include('components/flash_messages') ?>

                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Belum ada user di workspace ini</h4>
                            <p class="text-muted">Invite user pertama untuk mulai berkolaborasi</p>
                            <a href="<?= base_url('owner/users/invite') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Invite User Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Bidang</th>
                                        <th>Role</th>
                                        <th>Bergabung</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-primary text-white mr-2">
                                                        <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong><?= esc($user['nama_lengkap']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= esc($user['email']) ?></td>
                                            <td><?= esc($user['bidang'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $user['role_name'] == 'owner' ? 'primary' : 'info' ?>">
                                                    <?= esc($user['role_label']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($user['joined_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                                    <?= $user['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?= base_url('owner/users/manage-roles/' . $user['id']) ?>"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="Kelola Role">
                                                        <i class="fas fa-user-cog"></i>
                                                    </a>
                                                    <?php if ($user['id'] != session()->get('user_id')): ?>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-remove-user"
                                                            data-user-id="<?= $user['id'] ?>"
                                                            data-user-name="<?= esc($user['nama_lengkap']) ?>"
                                                            title="Hapus dari Workspace">
                                                            <i class="fas fa-user-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Total: <?= count($users) ?> user di workspace ini
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus User -->
<div class="modal fade" id="removeUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus <strong id="userName"></strong> dari workspace ini?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    User akan kehilangan akses ke semua data dan dashboard di workspace ini.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmRemove">Hapus User</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let userIdToRemove = null;

        $('.btn-remove-user').on('click', function() {
            userIdToRemove = $(this).data('user-id');
            const userName = $(this).data('user-name');
            $('#userName').text(userName);
            $('#removeUserModal').modal('show');
        });

        $('#confirmRemove').on('click', function() {
            if (userIdToRemove) {
                $.ajax({
                    url: '<?= base_url('owner/users/remove/') ?>' + userIdToRemove,
                    method: 'POST',
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat menghapus user');
                    }
                });
            }
        });
    });
</script>

<style>
    .avatar-circle {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
</style>

<?= $this->endSection() ?>