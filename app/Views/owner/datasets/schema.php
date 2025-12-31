<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">

    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-cog me-2"></i>Konfigurasi Schema: <?= esc($dataset['dataset_name']) ?></h4>
        </div>
        <div class="card-body">

            <form method="POST" action="<?= base_url('owner/datasets/update-schema/' . $dataset['id']) ?>">

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Konfigurasikan tipe data untuk setiap kolom agar statistik dapat dihitung dengan benar.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 35%">Nama Kolom</th>
                                <th style="width: 25%">Tipe Data</th>
                                <th style="width: 20%">Format (Optional)</th>
                                <th style="width: 20%">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $schema = json_decode($dataset['schema_config'] ?? '[]', true);
                            $i = 0;
                            foreach ($schema as $column):
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($column['field_name']) ?></strong>
                                        <input type="hidden" name="schema[<?= $i ?>][field_name]" value="<?= esc($column['field_name']) ?>">
                                    </td>
                                    <td>
                                        <select name="schema[<?= $i ?>][type]" class="form-select">
                                            <option value="text" <?= ($column['type'] ?? '') === 'text' ? 'selected' : '' ?>>Text</option>
                                            <option value="integer" <?= ($column['type'] ?? '') === 'integer' ? 'selected' : '' ?>>Integer</option>
                                            <option value="decimal" <?= ($column['type'] ?? '') === 'decimal' ? 'selected' : '' ?>>Decimal</option>
                                            <option value="date" <?= ($column['type'] ?? '') === 'date' ? 'selected' : '' ?>>Date</option>
                                            <option value="datetime" <?= ($column['type'] ?? '') === 'datetime' ? 'selected' : '' ?>>DateTime</option>
                                            <option value="boolean" <?= ($column['type'] ?? '') === 'boolean' ? 'selected' : '' ?>>Boolean</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text"
                                            name="schema[<?= $i ?>][format]"
                                            class="form-control form-control-sm"
                                            value="<?= esc($column['format'] ?? '') ?>"
                                            placeholder="e.g., Y-m-d">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-column" data-index="<?= $i ?>" title="Hapus Kolom">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php
                                $i++;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= base_url('owner/datasets/view/' . $dataset['id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- Documentation -->
    <div class="card mt-4">
        <div class="card-header">
            <h5><i class="fas fa-book me-2"></i>Dokumentasi Tipe Data</h5>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Text</dt>
                <dd class="col-sm-9">String / karakter (contoh: nama, alamat, keterangan)</dd>

                <dt class="col-sm-3">Integer</dt>
                <dd class="col-sm-9">Bilangan bulat tanpa desimal (contoh: jumlah penduduk, umur)</dd>

                <dt class="col-sm-3">Decimal</dt>
                <dd class="col-sm-9">Bilangan dengan desimal (contoh: harga, persentase, rating)</dd>

                <dt class="col-sm-3">Date</dt>
                <dd class="col-sm-9">Tanggal (contoh: 2024-12-25)</dd>

                <dt class="col-sm-3">DateTime</dt>
                <dd class="col-sm-9">Tanggal dan waktu (contoh: 2024-12-25 14:30:00)</dd>

                <dt class="col-sm-3">Boolean</dt>
                <dd class="col-sm-9">True/False, 1/0, Yes/No (contoh: status aktif)</dd>
            </dl>
        </div>
    </div>

</div>

<script>
    // Show format input only for date/datetime types
    document.querySelectorAll('select[name*="[type]"]').forEach(select => {
        const formatInput = select.closest('tr').querySelector('input[name*="[format]"]');

        select.addEventListener('change', function() {
            if (['date', 'datetime'].includes(this.value)) {
                formatInput.disabled = false;
                formatInput.placeholder = this.value === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';
            } else {
                formatInput.disabled = true;
                formatInput.value = '';
            }
        });

        // Trigger on load
        select.dispatchEvent(new Event('change'));
    });

    // Handle column deletion
    document.querySelectorAll('.delete-column').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const columnName = row.querySelector('input[name*="[field_name]"]').value;

            // Show confirmation dialog
            if (confirm('Apakah Anda yakin ingin menghapus kolom "' + columnName + '"? Data kolom ini akan hilang dari statistik dan tidak dapat dikembalikan.')) {
                // Disable button to prevent double-click
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                // Make AJAX call to delete column
                fetch('<?= base_url('owner/datasets/delete-column') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            'dataset_id': <?= $dataset['id'] ?>,
                            'column_name': columnName
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove row from table
                            row.remove();

                            // Show success message
                            showAlert('success', data.message);

                            // Re-index remaining rows
                            const tbody = document.querySelector('tbody');
                            const rows = tbody.querySelectorAll('tr');

                            rows.forEach((row, newIndex) => {
                                // Update all form field names in this row
                                const inputs = row.querySelectorAll('input, select');
                                inputs.forEach(input => {
                                    const name = input.getAttribute('name');
                                    if (name) {
                                        const newName = name.replace(/\[\d+\]/, `[${newIndex}]`);
                                        input.setAttribute('name', newName);
                                    }
                                });

                                // Update delete button data-index
                                const deleteBtn = row.querySelector('.delete-column');
                                if (deleteBtn) {
                                    deleteBtn.setAttribute('data-index', newIndex);
                                }
                            });
                        } else {
                            // Show error message
                            showAlert('error', data.message);
                            // Re-enable button
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-trash"></i>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('error', 'Terjadi kesalahan saat menghapus kolom.');
                        // Re-enable button
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-trash"></i>';
                    });
            }
        });
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert-dismissible');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insert at the top of the card body
        const cardBody = document.querySelector('.card-body');
        cardBody.insertBefore(alertDiv, cardBody.firstChild);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
</script>

<?= $this->endSection() ?>