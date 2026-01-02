<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-2"></i>Invite User ke Workspace
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('owner/users') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar User
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?= $this->include('components/flash_messages') ?>

                    <!-- Info Alert -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi:</strong> User yang diinvite harus sudah terdaftar di sistem terlebih dahulu.
                        Jika user belum memiliki akun, minta mereka untuk register terlebih dahulu.
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <form action="<?= base_url('owner/users/invite') ?>" method="post" id="inviteForm">
                                <?= csrf_field() ?>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Detail User</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="email">Email User <span class="text-danger">*</span></label>
                                            <input type="email"
                                                class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                                                id="email"
                                                name="email"
                                                value="<?= old('email') ?>"
                                                placeholder="Masukkan email user yang akan diinvite"
                                                required>
                                            <?php if (session('errors.email')): ?>
                                                <div class="invalid-feedback">
                                                    <?= session('errors.email') ?>
                                                </div>
                                            <?php endif; ?>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Pastikan email ini sudah terdaftar di sistem
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="role_id">Role yang Diberikan <span class="text-danger">*</span></label>
                                            <select class="form-control <?= session('errors.role_id') ? 'is-invalid' : '' ?>"
                                                id="role_id"
                                                name="role_id"
                                                required>
                                                <option value="">Pilih Role</option>
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?= $role['id'] ?>"
                                                        <?= old('role_id') == $role['id'] ? 'selected' : '' ?>>
                                                        <?= esc($role['role_label']) ?> - <?= esc($role['description']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (session('errors.role_id')): ?>
                                                <div class="invalid-feedback">
                                                    <?= session('errors.role_id') ?>
                                                </div>
                                            <?php endif; ?>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-question-circle"></i> Role menentukan akses user terhadap data dan fitur workspace
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="message">Pesan Personal (Opsional)</label>
                                            <textarea class="form-control"
                                                id="message"
                                                name="message"
                                                rows="3"
                                                placeholder="Tulis pesan yang akan dikirim bersama invitation (opsional)"><?= old('message') ?></textarea>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-envelope"></i> Pesan ini akan disertakan dalam email invitation
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Konfirmasi & Kirim</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                id="send_email"
                                                name="send_email"
                                                value="1"
                                                checked>
                                            <label class="form-check-label" for="send_email">
                                                Kirim email invitation otomatis
                                            </label>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-envelope"></i> Sistem akan mengirim email ke user dengan link aktivasi
                                            </small>
                                        </div>

                                        <hr>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> User akan langsung mendapatkan akses setelah invitation dikirim
                                                </small>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-secondary" onclick="history.back()">
                                                    <i class="fas fa-times"></i> Batal
                                                </button>
                                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                                    <i class="fas fa-paper-plane"></i> Kirim Invitation
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-4">
                            <!-- Role Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Panduan Role</h5>
                                </div>
                                <div class="card-body">
                                    <div id="role-info">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-question-circle fa-2x mb-2"></i>
                                            <p>Pilih role untuk melihat informasi detail</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Invitations -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Invitation Terbaru</h5>
                                </div>
                                <div class="card-body">
                                    <div id="recent-invitations">
                                        <small class="text-muted">Memuat...</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Tips -->
                            <div class="card border-warning">
                                <div class="card-header bg-warning">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-lightbulb"></i> Tips Invite User
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success"></i>
                                            Pastikan email user sudah terdaftar
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success"></i>
                                            Pilih role sesuai tanggung jawab user
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success"></i>
                                            Owner dapat mengelola semua data
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-check text-success"></i>
                                            Viewer hanya dapat melihat data
                                        </li>
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

<script>
    $(document).ready(function() {
        const rolesData = <?= json_encode($roles) ?>;

        // Update role information when role changes
        $('#role_id').on('change', function() {
            const selectedRoleId = $(this).val();
            const selectedRole = rolesData.find(role => role.id == selectedRoleId);

            if (selectedRole) {
                let roleHtml = `
                <h6 class="text-primary">${selectedRole.role_label}</h6>
                <p class="text-muted small mb-3">${selectedRole.description}</p>
                <div class="mb-2">
                    <strong>Permissions:</strong>
                </div>
                <ul class="list-unstyled small">
            `;

                // Add permissions based on role
                switch (selectedRole.role_name) {
                    case 'owner':
                        roleHtml += `
                        <li><i class="fas fa-check text-success"></i> Full access ke semua data</li>
                        <li><i class="fas fa-check text-success"></i> Mengelola user workspace</li>
                        <li><i class="fas fa-check text-success"></i> Membuat dan mengedit dashboard</li>
                        <li><i class="fas fa-check text-success"></i> Mengelola statistik</li>
                    `;
                        break;
                    case 'viewer':
                        roleHtml += `
                        <li><i class="fas fa-check text-success"></i> Melihat semua data</li>
                        <li><i class="fas fa-times text-muted"></i> Tidak dapat mengedit data</li>
                        <li><i class="fas fa-times text-muted"></i> Tidak dapat mengelola user</li>
                        <li><i class="fas fa-eye text-info"></i> Hanya dapat melihat dashboard</li>
                    `;
                        break;
                }

                roleHtml += '</ul>';
                $('#role-info').html(roleHtml);
            } else {
                $('#role-info').html(`
                <div class="text-center text-muted">
                    <i class="fas fa-question-circle fa-2x mb-2"></i>
                    <p>Pilih role untuk melihat informasi detail</p>
                </div>
            `);
            }
        });

        // Trigger initial role info
        $('#role_id').trigger('change');

        // Email validation
        $('#email').on('blur', function() {
            const email = $(this).val();
            if (email && !isValidEmail(email)) {
                $(this).addClass('is-invalid');
                if ($(this).next('.invalid-feedback').length === 0) {
                    $(this).after('<div class="invalid-feedback">Format email tidak valid</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        });

        // Form validation
        $('#inviteForm').on('submit', function(e) {
            const email = $('#email').val().trim();
            const roleId = $('#role_id').val();

            let isValid = true;
            let errors = [];

            if (!email) {
                isValid = false;
                errors.push('Email wajib diisi');
            } else if (!isValidEmail(email)) {
                isValid = false;
                errors.push('Format email tidak valid');
            }

            if (!roleId) {
                isValid = false;
                errors.push('Role wajib dipilih');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi form dengan benar:\n' + errors.join('\n'));
                return false;
            }

            // Show loading state
            const submitBtn = $('#submitBtn');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim Invitation...');

            // Re-enable button after 10 seconds as fallback
            setTimeout(function() {
                submitBtn.prop('disabled', false).html(originalText);
            }, 10000);
        });

        // Load recent invitations (mock data for now)
        loadRecentInvitations();

        function loadRecentInvitations() {
            // This would normally be an AJAX call to get recent invitations
            const mockInvitations = [{
                    email: 'john@example.com',
                    role: 'Viewer',
                    date: '2024-01-15',
                    status: 'Sent'
                },
                {
                    email: 'jane@example.com',
                    role: 'Owner',
                    date: '2024-01-10',
                    status: 'Accepted'
                }
            ];

            if (mockInvitations.length > 0) {
                let html = '<div class="list-group list-group-flush">';
                mockInvitations.forEach(invitation => {
                    html += `
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">${invitation.email}</small>
                                <br>
                                <small class="badge badge-${invitation.status === 'Accepted' ? 'success' : 'info'}">${invitation.status}</small>
                                <small class="text-muted">${invitation.role}</small>
                            </div>
                            <small class="text-muted">${invitation.date}</small>
                        </div>
                    </div>
                `;
                });
                html += '</div>';
                $('#recent-invitations').html(html);
            } else {
                $('#recent-invitations').html('<small class="text-muted">Belum ada invitation</small>');
            }
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    });
</script>

<style>
    .card {
        box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
        border: 1px solid rgba(0, 0, 0, .125);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
    }

    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .text-primary {
        color: #007bff !important;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-info {
        background-color: #17a2b8;
    }
</style>

<?= $this->endSection() ?>