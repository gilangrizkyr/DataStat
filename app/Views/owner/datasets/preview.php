<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-table me-2"></i>Preview: <?= esc($dataset['dataset_name']) ?></h4>
                <div>
                    <a href="<?= base_url('owner/datasets/view/' . $dataset['id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button class="btn btn-success" onclick="exportData()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            
            <!-- Search & Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari data...">
                </div>
                <div class="col-md-3">
                    <select id="columnFilter" class="form-select">
                        <option value="">Semua Kolom</option>
                        <?php foreach ($columns as $column): ?>
                            <option value="<?= esc($column) ?>"><?= esc($column) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="limitSelect" class="form-select">
                        <option value="25">25 rows</option>
                        <option value="50">50 rows</option>
                        <option value="100" selected>100 rows</option>
                        <option value="500">500 rows</option>
                    </select>
                </div>
            </div>
            
            <!-- Data Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="previewTable">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <?php foreach ($columns as $column): ?>
                                <th><?= esc($column) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($records as $record): 
                            $data = json_decode($record['data_json'], true);
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <?php foreach ($columns as $column): ?>
                                <td><?= esc($data[$column] ?? '-') ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Info -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Menampilkan <?= count($records) ?> dari <?= number_format($total_records) ?> records
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
            
        </div>
    </div>
    
</div>

<script>
// Simple client-side search
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#previewTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});

// Export data
function exportData() {
    window.location.href = '<?= base_url('owner/datasets/export/' . $dataset['id']) ?>';
}
</script>

<?= $this->endSection() ?>