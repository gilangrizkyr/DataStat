<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Header -->
        <div class="text-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Dataset</h2>
            <p class="text-muted">Edit informasi dataset</p>
        </div>

        <div class="card border-0 shadow">
            <div class="card-body p-4">

                <!-- Dataset Info -->
                <div class="alert alert-info mb-4">
                    <h6><i class="fas fa-info-circle me-2"></i>Informasi Dataset</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>File:</strong> <?= esc($dataset['file_name']) ?><br>
                            <strong>Ukuran:</strong> <?= number_format($dataset['file_size'] / 1024, 2) ?> KB<br>
                        </div>
                        <div class="col-md-6">
                            <strong>Dibuat:</strong> <?= date('d M Y H:i', strtotime($dataset['created_at'])) ?><br>
                            <strong>Status:</strong>
                            <span class="badge bg-<?= $dataset['upload_status'] === 'completed' ? 'success' : 'warning' ?>">
                                <?= ucfirst($dataset['upload_status']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <form method="POST" action="<?= base_url('owner/datasets/update/' . $dataset['id']) ?>">

                    <!-- CSRF Token -->
                    <?= csrf_field() ?>

                    <!-- Dataset Name -->
                    <div class="mb-3">
                        <label for="dataset_name" class="form-label">
                            <i class="fas fa-tag me-2"></i>Nama Dataset *
                        </label>
                        <input type="text"
                            class="form-control <?= (isset($errors['dataset_name'])) ? 'is-invalid' : '' ?>"
                            id="dataset_name"
                            name="dataset_name"
                            value="<?= old('dataset_name', esc($dataset['dataset_name'])) ?>"
                            placeholder="Contoh: Data Penduduk 2024"
                            required>
                        <div class="invalid-feedback">
                            <?= isset($errors['dataset_name']) ? $errors['dataset_name'] : '' ?>
                        </div>
                        <small class="text-muted">Berikan nama yang deskriptif untuk dataset Anda</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-2"></i>Deskripsi
                        </label>
                        <textarea class="form-control <?= (isset($errors['description'])) ? 'is-invalid' : '' ?>"
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Jelaskan tentang dataset ini..."><?= old('description', esc($dataset['description'])) ?></textarea>
                        <div class="invalid-feedback">
                            <?= isset($errors['description']) ? $errors['description'] : '' ?>
                        </div>
                        <small class="text-muted">Maksimal 1000 karakter</small>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('owner/datasets/detail/' . $dataset['id']) ?>" class="btn btn-secondary flex-fill">
                            <i class="fas fa-arrow-left me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>