<?= $this->extend('layouts/owner') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Header -->
        <div class="text-center mb-4">
            <h2><i class="fas fa-cloud-upload-alt me-2"></i>Upload Dataset</h2>
            <p class="text-muted">Upload file CSV atau Excel untuk membuat dataset baru</p>
        </div>

        <div class="card border-0 shadow">
            <div class="card-body p-4">

                <!-- Info Alert -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Format yang didukung:</strong> CSV, XLSX, XLS |
                    <strong>Ukuran maksimal:</strong> 10 MB
                </div>

                <!-- Upload Form -->
                <form id="uploadForm" method="POST" action="<?= base_url('owner/datasets/store') ?>" enctype="multipart/form-data">

                    <!-- Drop Zone -->
                    <div class="border border-2 border-dashed rounded p-5 text-center mb-4"
                        id="dropZone"
                        onclick="document.getElementById('fileInput').click()"
                        style="cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-file-upload text-muted mb-3" style="font-size: 4rem;"></i>
                        <h5>Drop file di sini atau klik untuk browse</h5>
                        <p class="text-muted mb-0">Format: CSV, XLSX, XLS (Maks 10 MB)</p>
                        <input type="file"
                            id="fileInput"
                            name="excel_file"
                            accept=".csv,.xlsx,.xls"
                            style="display: none;"
                            onchange="handleFileSelect()"
                            required>
                    </div>

                    <!-- File Info -->
                    <div class="alert alert-success d-none" id="fileInfo">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="fas fa-file me-2"></i><span id="fileName"></span></h6>
                                <small><span id="fileSize"></span> • <span id="fileType"></span></small>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" onclick="clearFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Dataset Name -->
                    <div class="mb-3">
                        <label for="dataset_name" class="form-label">
                            <i class="fas fa-tag me-2"></i>Nama Dataset *
                        </label>
                        <input type="text"
                            class="form-control"
                            id="dataset_name"
                            name="dataset_name"
                            placeholder="Contoh: Data Penduduk 2024"
                            required>
                        <small class="text-muted">Berikan nama yang deskriptif untuk dataset Anda</small>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-2"></i>Deskripsi
                        </label>
                        <textarea class="form-control"
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Jelaskan tentang dataset ini..."></textarea>
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress d-none mb-3" id="progressBar" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar"
                            style="width: 0%"
                            id="progressBarFill">0%</div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('owner/datasets') ?>" class="btn btn-secondary flex-fill">
                            <i class="fas fa-arrow-left me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary flex-fill" id="submitBtn">
                            <i class="fas fa-upload me-2"></i>Upload Dataset
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<script>
    // Handle file selection - MUST BE GLOBAL
    function handleFileSelect() {
        const file = document.getElementById('fileInput').files[0];
        if (!file) return;

        console.log('File selected:', file.name);

        // Validate file type
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['csv', 'xlsx', 'xls'].includes(ext)) {
            alert('Hanya file CSV, XLS, atau XLSX yang diperbolehkan');
            clearFile();
            return;
        }

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file maksimal 10 MB');
            clearFile();
            return;
        }

        // Show file info
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        document.getElementById('fileType').textContent = ext.toUpperCase();
        document.getElementById('fileInfo').classList.remove('d-none');

        // Auto-fill dataset name
        if (!document.getElementById('dataset_name').value) {
            const nameWithoutExt = file.name.replace(/\.[^/.]+$/, "");
            document.getElementById('dataset_name').value = nameWithoutExt;
        }
    }

    // Clear file
    function clearFile() {
        document.getElementById('fileInput').value = '';
        document.getElementById('fileInfo').classList.add('d-none');
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Drag and drop functionality
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.style.backgroundColor = '#f8f9fa';
            dropZone.style.borderColor = '#0d6efd';
        });

        dropZone.addEventListener('dragleave', function() {
            dropZone.style.backgroundColor = '';
            dropZone.style.borderColor = '';
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.style.backgroundColor = '';
            dropZone.style.borderColor = '';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });
    });

    // Form submission with AJAX
    document.addEventListener('DOMContentLoaded', function() {
        const uploadForm = document.getElementById('uploadForm');

        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitBtn');
            const progressBar = document.getElementById('progressBar');
            const progressBarFill = document.getElementById('progressBarFill');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
            progressBar.classList.remove('d-none');

            // Check if jQuery and Swal are loaded
            if (typeof $ === 'undefined') {
                alert('jQuery belum loaded!');
                return;
            }

            if (typeof Swal === 'undefined') {
                alert('SweetAlert2 belum loaded!');
                return;
            }

            // AJAX upload with jQuery
            $.ajax({
                url: uploadForm.action,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            progressBarFill.style.width = percentComplete + '%';
                            progressBarFill.textContent = percentComplete + '%';
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Dataset berhasil diupload',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.redirect || '<?= base_url('owner/datasets') ?>';
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Terjadi kesalahan saat upload', 'error');
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Terjadi kesalahan saat upload';
                    Swal.fire('Error', error, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload Dataset';
                    progressBar.classList.add('d-none');
                    progressBarFill.style.width = '0%';
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>