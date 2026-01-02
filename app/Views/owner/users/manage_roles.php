<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kelola Role User</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('owner/users') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar User
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?= $this->include('components/flash_messages') ?>

                    <?php if (!$user): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            User tidak ditemukan atau tidak memiliki akses ke workspace ini.
                        </div>
                    <?php else: ?>
                        <!-- User Info -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white mr-3" style="width: 50px; height: 50px; font-size: 18px;">
                                                <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h5 class="mb-1"><?= esc($user['nama_lengkap']) ?></h5>
                                                <p class="text-muted mb-1"><?= esc($user['email']) ?></p>
                                                <small class="text-muted">
                                                    Bergabung: <?= date('d F Y', strtotime($user['joined_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="<?= base_url('owner/users/manage-roles/' . $user['id']) ?>" method="post" id="roleForm">
                            <?= csrf_field() ?>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Role Saat Ini</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="current_role">Role Aktif:</label>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge badge-<?= $user['role_name'] == 'owner' ? 'primary' : 'info' ?> badge-lg mr-2">
                                                        <?= esc($user['role_label']) ?>
                                                    </span>
                                                    <small class="text-muted">
                                                        <?= esc($user['role_description']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Ubah Role</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="role_id">Pilih Role Baru <span class="text-danger">*</span></label>
                                                <select class="form-control <?= session('errors.role_id') ? 'is-invalid' : '' ?>"
                                                    id="role_id"
                                                    name="role_id"
                                                    required>
                                                    <option value="">Pilih Role</option>
                                                    <?php foreach ($roles as $role): ?>
                                                        <?php if ($role['role_name'] !== 'superadmin'): // Superadmin role shouldn't be assignable
                                                        ?>
                                                            <option value="<?= $role['id'] ?>"
                                                                <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>
                                                                <?= $role['role_name'] == 'owner' && $user['id'] == session()->get('user_id') ? 'disabled' : '' ?>>
                                                                <?= esc($role['role_label']) ?> - <?= esc($role['description']) ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php if (session('errors.role_id')): ?>
                                                    <div class="invalid-feedback">
                                                        <?= session('errors.role_id') ?>
                                                    </div>
                                                <?php endif; ?>
                                                <small class="form-text text-muted">
                                                    Pilih role yang sesuai dengan tanggung jawab user di workspace ini
                                                </small>
                                            </div>

                                            <div class="form-group">
                                                <label for="reason">Alasan Perubahan Role</label>
                                                <textarea class="form-control"
                                                    id="reason"
                                                    name="reason"
                                                    rows="3"
                                                    placeholder="Jelaskan alasan perubahan role ini (opsional)"><?= old('reason') ?></textarea>
                                                <small class="form-text text-muted">
                                                    Alasan ini akan dicatat dalam log aktivitas
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-exclamation-triangle"></i> Perhatian
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-warning">
                                                <strong>Penting!</strong> Perubahan role akan mempengaruhi akses user terhadap:
                                            </div>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-database text-warning"></i> Dataset dan data</li>
                                                <li><i class="fas fa-chart-bar text-warning"></i> Statistik dan visualisasi</li>
                                                <li><i class="fas fa-tachometer-alt text-warning"></i> Dashboard</li>
                                                <li><i class="fas fa-users text-warning"></i> Manajemen user (jika owner)</li>
                                            </ul>

                                            <hr>

                                            <div class="text-center">
                                                <button type="submit" class="btn btn-warning btn-block" id="submitBtn">
                                                    <i class="fas fa-save"></i> Simpan Perubahan Role
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Role Permissions Preview -->
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Pratinjau Permissions</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="permissions-preview">
                                                <small class="text-muted">Pilih role untuk melihat permissions</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const rolesData = <?= json_encode($roles) ?>;

        // Update permissions preview when role changes
        $('#role_id').on('change', function() {
            const selectedRoleId = $(this).val();
            const selectedRole = rolesData.find(role => role.id == selectedRoleId);

            if (selectedRole) {
                let permissionsHtml = '<h6>' + selectedRole.label + '</h6>';
                permissionsHtml += '<p class="text-muted small mb-2">' + selectedRole.description + '</p>';

                // Add permission badges based on role
                const permissions = [];
                switch (selectedRole.name) {
                    case 'owner':
                        permissions.push('<span class="badge badge-primary">Full Access</span>');
                        permissions.push('<span class="badge badge-info">Manage Users</span>');
                        permissions.push('<span class="badge badge-success">Create/Edit All</span>');
                        break;
                    case 'editor':
                        permissions.push('<span class="badge badge-success">Create/Edit</span>');
                        permissions.push('<span class="badge badge-info">View All</span>');
                        break;
                    case 'viewer':
                        permissions.push('<span class="badge badge-secondary">View Only</span>');
                        break;
                }

                permissionsHtml += '<div>' + permissions.join(' ') + '</div>';
                $('#permissions-preview').html(permissionsHtml);
            } else {
                $('#permissions-preview').html('<small class="text-muted">Pilih role untuk melihat permissions</small>');
            }
        });

        // Trigger initial preview
        $('#role_id').trigger('change');

        // Form validation
        $('#roleForm').on('submit', function(e) {
            const roleId = $('#role_id').val();

            if (!roleId) {
                e.preventDefault();
                alert('Mohon pilih role yang akan diberikan');
                return false;
            }

            // Show loading state
            const submitBtn = $('#submitBtn');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            // Re-enable button after 5 seconds as fallback
            setTimeout(function() {
                submitBtn.prop('disabled', false).html(originalText);
            }, 5000);
        });

        // Prevent self-demotion for owner
        <?php if ($user && $user['id'] == session()->get('user_id') && $user['role_name'] == 'owner'): ?>
            $('#role_id option[value="<?= $user['role_id'] ?>"]').prop('disabled', true);
            $('#role_id').after('<small class="form-text text-warning"><i class="fas fa-exclamation-triangle"></i> Anda tidak dapat mengubah role owner sendiri</small>');
        <?php endif; ?>
    });
</script>

<style>
    .badge-lg {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
    }
</style>

<?= $this->endSection() ?>