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
                                <th style="width: 40%">Nama Kolom</th>
                                <th style="width: 25%">Tipe Data</th>
                                <th style="width: 20%">Format (Optional)</th>
                                <th style="width: 15%">Nullable</th>
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
                                    <strong><?= esc($column['name']) ?></strong>
                                    <input type="hidden" name="schema[<?= $i ?>][name]" value="<?= esc($column['name']) ?>">
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
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="schema[<?= $i ?>][nullable]" 
                                               value="1"
                                               <?= !empty($column['nullable']) ? 'checked' : '' ?>>
                                    </div>
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
</script>

<?= $this->endSection() ?>