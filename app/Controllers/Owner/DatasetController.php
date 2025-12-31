<?php

namespace App\Controllers\Owner;

/**
 * ============================================================================
 * OWNER DATASET CONTROLLER
 * ============================================================================
 *
 * Path: app/Controllers/Owner/DatasetController.php
 *
 * Deskripsi:
 * Controller untuk mengelola dataset (upload, view, edit, delete).
 * Owner dapat upload file Excel, lihat preview data, edit schema, dan hapus dataset.
 *
 * Fitur:
 * - List semua dataset
 * - Upload Excel file
 * - Preview data Excel
 * - Edit schema (field, tipe data, label)
 * - Delete dataset
 * - Download dataset
 *
 * Role: Owner
 * ============================================================================
 */

use App\Controllers\BaseController;
use App\Models\Owner\DatasetModel;
use App\Models\Owner\DatasetRecordModel;
use App\Libraries\ExcelReader;
use App\Libraries\SchemaMapper;

class DatasetController extends BaseController
{
    protected $datasetModel;
    protected $recordModel;
    protected $excelReader;
    protected $schemaMapper;

    public function __construct()
    {
        $this->datasetModel = new DatasetModel();
        $this->recordModel = new DatasetRecordModel();
        $this->excelReader = new ExcelReader();
        $this->schemaMapper = new SchemaMapper();
        helper(['form', 'url', 'filesystem', 'dataset', 'security']);
    }

    /**
     * List semua dataset
     */
    public function index()
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return redirect()->to('/owner/application/create')
                ->with('info', 'Silakan buat aplikasi terlebih dahulu');
        }

        // Get all datasets
        $datasets = $this->datasetModel
            ->select('datasets.*, users.nama_lengkap as uploader_name')
            ->join('users', 'users.id = datasets.uploaded_by')
            ->where('datasets.application_id', $applicationId)
            ->where('datasets.deleted_at', null)
            ->orderBy('datasets.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Kelola Dataset',
            'datasets' => $datasets
        ];

        return view('owner/datasets/index', $data);
    }

    /**
     * Halaman upload dataset
     */
    public function upload()
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return redirect()->to('/owner/application/create');
        }

        $data = [
            'title' => 'Upload Dataset',
            'validation' => \Config\Services::validation()
        ];

        return view('owner/datasets/upload', $data);
    }

    /**
     * Proses upload Excel file
     */
    public function store()
    {
        // Check if this is an AJAX request
        $isAjax = $this->request->isAJAX();

        // Validasi file
        $rules = [
            'dataset_name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama dataset harus diisi',
                    'min_length' => 'Nama dataset minimal 3 karakter',
                    'max_length' => 'Nama dataset maksimal 255 karakter'
                ]
            ],
            'description' => [
                'rules' => 'permit_empty|max_length[1000]',
                'errors' => [
                    'max_length' => 'Deskripsi maksimal 1000 karakter'
                ]
            ],
            'excel_file' => [
                'rules' => 'uploaded[excel_file]|ext_in[excel_file,xlsx,xls,csv]|max_size[excel_file,10240]',
                'errors' => [
                    'uploaded' => 'File Excel harus diupload',
                    'ext_in' => 'File harus berformat Excel (xlsx, xls, atau csv)',
                    'max_size' => 'Ukuran file maksimal 10MB'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $applicationId = session()->get('application_id');
            $userId = session()->get('user_id');

            $file = $this->request->getFile('excel_file');

            // Generate unique filename
            $fileName = $file->getRandomName();
            $filePath = 'uploads/datasets/' . $applicationId . '/';

            // Create directory if not exists
            if (!is_dir(FCPATH . $filePath)) {
                mkdir(FCPATH . $filePath, 0755, true);
            }

            // Move file
            $file->move(FCPATH . $filePath, $fileName);
            $fullPath = $filePath . $fileName;

            // Generate slug
            $datasetName = $this->request->getPost('dataset_name');
            $slug = url_title($datasetName, '-', true);

            // Ensure unique slug
            $existingSlug = $this->datasetModel
                ->where('application_id', $applicationId)
                ->where('dataset_slug', $slug)
                ->first();

            if ($existingSlug) {
                $slug = $slug . '-' . uniqid();
            }

            // Insert dataset metadata
            $datasetData = [
                'application_id' => $applicationId,
                'dataset_name' => $datasetName,
                'dataset_slug' => $slug,
                'description' => $this->request->getPost('description'),
                'file_path' => $fullPath,
                'file_name' => $file->getName(),
                'file_size' => $file->getSize(),
                'upload_status' => 'processing',
                'uploaded_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $datasetId = $this->datasetModel->insert($datasetData);

            if (!$datasetId) {
                throw new \Exception('Gagal menyimpan metadata dataset');
            }

            // Read Excel file dan simpan data
            $this->processExcelFile($datasetId, FCPATH . $fullPath);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal');
            }

            // Log aktivitas
            $this->logActivity('upload', 'datasets', 'Owner upload dataset: ' . $datasetName, [
                'dataset_id' => $datasetId,
                'file_name' => $file->getName()
            ]);

            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Dataset berhasil diupload!',
                    'redirect' => base_url('owner/datasets/preview/' . $datasetId)
                ]);
            }

            return redirect()->to('/owner/datasets/preview/' . $datasetId)
                ->with('success', 'Dataset berhasil diupload! Silakan review dan konfirmasi schema.');
        } catch (\Exception $e) {
            $db->transRollback();

            // Delete uploaded file if exists
            if (isset($fullPath) && file_exists(FCPATH . $fullPath)) {
                unlink(FCPATH . $fullPath);
            }

            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal upload dataset: ' . $e->getMessage()
                ]);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal upload dataset: ' . $e->getMessage());
        }
    }

    /**
     * Process Excel file dan simpan ke database
     */
    private function processExcelFile($datasetId, $filePath)
    {
        try {
            // Read Excel menggunakan Library
            $excelData = $this->excelReader->read($filePath);

            if (empty($excelData['data'])) {
                throw new \Exception('File Excel kosong atau tidak valid');
            }

            // Deteksi schema dari header (baris pertama)
            $headers = $excelData['headers'];
            $schema = $this->schemaMapper->detectSchema($headers, $excelData['data']);

            // Simpan schema ke dataset
            $this->datasetModel->update($datasetId, [
                'schema_config' => json_encode($schema),
                'total_columns' => count($headers),
                'total_rows' => count($excelData['data']),
                'upload_status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Simpan data ke dataset_records
            $batch = [];
            foreach ($excelData['data'] as $rowNumber => $row) {
                $batch[] = [
                    'dataset_id' => $datasetId,
                    'row_num' => $rowNumber + 1,
                    'data_json' => json_encode($row),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Insert per 500 rows untuk efisiensi
                if (count($batch) >= 500) {
                    $this->recordModel->insertBatch($batch);
                    $batch = [];
                }
            }

            // Insert sisa data
            if (!empty($batch)) {
                $this->recordModel->insertBatch($batch);
            }
        } catch (\Exception $e) {
            // Update status failed
            $this->datasetModel->update($datasetId, [
                'upload_status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * View dataset overview
     */
    public function view($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return redirect()->to('/owner/datasets')
                ->with('error', 'Dataset tidak ditemukan');
        }

        // Get sample data (5 rows pertama)
        $sampleData = $this->recordModel
            ->where('dataset_id', $id)
            ->limit(5)
            ->findAll();

        // Parse JSON data
        foreach ($sampleData as &$row) {
            $row['data'] = json_decode($row['data_json'], true);
        }

        // Parse schema
        $schema = json_decode($dataset['schema_config'], true);

        $data = [
            'title' => 'Dataset: ' . $dataset['dataset_name'],
            'dataset' => $dataset,
            'schema' => $schema,
            'sample_data' => $sampleData
        ];

        return view('owner/datasets/view', $data);
    }

    /**
     * Preview dataset
     */
    public function preview($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return redirect()->to('/owner/datasets')
                ->with('error', 'Dataset tidak ditemukan');
        }

        // Get sample data (10 rows pertama)
        $sampleData = $this->recordModel
            ->where('dataset_id', $id)
            ->limit(10)
            ->findAll();

        // Parse JSON data
        foreach ($sampleData as &$row) {
            $row['data'] = json_decode($row['data_json'], true);
        }

        // Parse schema
        $schema = json_decode($dataset['schema_config'], true);

        // Extract column names from schema
        $columns = array_column($schema, 'field_name');

        $data = [
            'title' => 'Preview Dataset: ' . $dataset['dataset_name'],
            'dataset' => $dataset,
            'schema' => $schema,
            'records' => $sampleData,
            'columns' => $columns,
            'total_records' => $dataset['total_rows'] ?? 0
        ];

        return view('owner/datasets/preview', $data);
    }

    /**
     * Detail dataset dengan full data
     */
    public function detail($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return redirect()->to('/owner/datasets')
                ->with('error', 'Dataset tidak ditemukan');
        }

        // Pagination
        $perPage = 50;
        $page = $this->request->getVar('page') ?? 1;

        $records = $this->recordModel
            ->where('dataset_id', $id)
            ->paginate($perPage, 'default', $page);

        // Parse JSON data
        foreach ($records as &$row) {
            $row['data'] = json_decode($row['data_json'], true);
        }

        // Parse schema
        $schema = json_decode($dataset['schema_config'], true);

        $data = [
            'title' => 'Detail Dataset: ' . $dataset['dataset_name'],
            'dataset' => $dataset,
            'schema' => $schema,
            'records' => $records,
            'pager' => $this->recordModel->pager
        ];

        return view('owner/datasets/detail', $data);
    }

    /**
     * Edit schema dataset
     */
    public function schema($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return redirect()->to('/owner/datasets')
                ->with('error', 'Dataset tidak ditemukan');
        }

        // Parse schema
        $schema = json_decode($dataset['schema_config'], true);

        $data = [
            'title' => 'Edit Schema: ' . $dataset['dataset_name'],
            'dataset' => $dataset,
            'schema' => $schema,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/datasets/schema', $data);
    }

    /**
     * Update schema dataset
     */
    public function updateSchema($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return redirect()->to('/owner/datasets')
                ->with('error', 'Dataset tidak ditemukan');
        }

        try {
            // Get schema dari form
            $schemaInput = $this->request->getPost('schema');

            if (empty($schemaInput)) {
                throw new \Exception('Schema tidak boleh kosong');
            }

            // Validate and format schema
            $schema = [];
            foreach ($schemaInput as $field) {
                $schema[] = [
                    'field_name' => $field['field_name'],
                    'type' => $field['type'],
                    'format' => $field['format'] ?? ''
                ];
            }

            // Update schema
            $updated = $this->datasetModel->update($id, [
                'schema_config' => json_encode($schema),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($updated) {
                // Log aktivitas
                $this->logActivity('update', 'datasets', 'Owner update schema dataset: ' . $dataset['dataset_name'], [
                    'dataset_id' => $id
                ]);

                return redirect()->to('/owner/datasets/detail/' . $id)
                    ->with('success', 'Schema dataset berhasil diupdate');
            } else {
                return redirect()->back()
                    ->with('error', 'Tidak ada perubahan pada schema');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal update schema: ' . $e->getMessage());
        }
    }

    /**
     * Delete dataset (soft delete)
     */
    public function delete($id)
    {
        // Check if this is an AJAX request
        $isAjax = $this->request->isAJAX();

        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Dataset tidak ditemukan'
                ]);
            }
            return redirect()->to('/owner/datasets')->with('error', 'Dataset tidak ditemukan');
        }

        try {
            // Soft delete
            $deleted = $this->datasetModel->update($id, [
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            if ($deleted) {
                // Log aktivitas
                $this->logActivity('delete', 'datasets', 'Owner menghapus dataset: ' . $dataset['dataset_name'], [
                    'dataset_id' => $id
                ]);

                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Dataset berhasil dihapus'
                    ]);
                }

                return redirect()->to('/owner/datasets')->with('success', 'Dataset berhasil dihapus');
            } else {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal menghapus dataset'
                    ]);
                }
                return redirect()->to('/owner/datasets')->with('error', 'Gagal menghapus dataset');
            }
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
            return redirect()->to('/owner/datasets')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show all records of a dataset
     */
    public function records($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return redirect()->to('/owner/datasets')
                ->with('error', 'Dataset tidak ditemukan');
        }

        // Pagination
        $perPage = 100;
        $page = $this->request->getVar('page') ?? 1;

        $records = $this->recordModel
            ->where('dataset_id', $id)
            ->paginate($perPage, 'default', $page);

        // Parse JSON data
        foreach ($records as &$row) {
            $row['data'] = json_decode($row['data_json'], true);
        }

        // Parse schema
        $schema = json_decode($dataset['schema_config'], true);

        $data = [
            'title' => 'Records Dataset: ' . $dataset['dataset_name'],
            'dataset' => $dataset,
            'schema' => $schema,
            'records' => $records,
            'pager' => $this->recordModel->pager
        ];

        return view('owner/datasets/records', $data);
    }

    /**
     * Edit dataset metadata
     */
    public function edit($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return redirect()->to('/owner/datasets')
                ->with('error', 'Dataset tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Dataset: ' . $dataset['dataset_name'],
            'dataset' => $dataset,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/datasets/edit', $data);
    }

    /**
     * Update dataset metadata
     */
    public function update($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return redirect()->to('/owner/datasets')
                ->with('error', 'Dataset tidak ditemukan');
        }

        // Validasi input
        $rules = [
            'dataset_name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama dataset harus diisi',
                    'min_length' => 'Nama dataset minimal 3 karakter',
                    'max_length' => 'Nama dataset maksimal 255 karakter'
                ]
            ],
            'description' => [
                'rules' => 'permit_empty|max_length[1000]',
                'errors' => [
                    'max_length' => 'Deskripsi maksimal 1000 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $datasetName = $this->request->getPost('dataset_name');
            $description = $this->request->getPost('description');

            // Generate new slug if name changed
            $slug = url_title($datasetName, '-', true);
            if ($datasetName !== $dataset['dataset_name']) {
                $existingSlug = $this->datasetModel
                    ->where('application_id', $applicationId)
                    ->where('dataset_slug', $slug)
                    ->where('id !=', $id)
                    ->first();

                if ($existingSlug) {
                    $slug = $slug . '-' . uniqid();
                }
            }

            // Update dataset
            $updated = $this->datasetModel->update($id, [
                'dataset_name' => $datasetName,
                'dataset_slug' => $slug,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($updated) {
                // Log aktivitas
                $this->logActivity('update', 'datasets', 'Owner update dataset: ' . $datasetName, [
                    'dataset_id' => $id
                ]);

                return redirect()->to('/owner/datasets/detail/' . $id)
                    ->with('success', 'Dataset berhasil diupdate');
            } else {
                return redirect()->back()
                    ->with('error', 'Tidak ada perubahan pada dataset');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal update dataset: ' . $e->getMessage());
        }
    }

    /**
     * Export dataset to Excel
     */
    public function export($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            return redirect()->to('/owner/datasets')
                ->with('error', 'Dataset tidak ditemukan');
        }

        try {
            // Get all records
            $records = $this->recordModel
                ->where('dataset_id', $id)
                ->findAll();

            // Parse schema
            $schema = json_decode($dataset['schema_config'], true);

            // Create Excel file
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $col = 'A';
            foreach ($schema as $field) {
                $sheet->setCellValue($col . '1', $field['field_name'] ?? $field['name'] ?? 'Unknown');
                $col++;
            }

            // Set data
            $rowNum = 2;
            foreach ($records as $record) {
                $data = json_decode($record['data_json'], true);
                $col = 'A';
                foreach ($schema as $field) {
                    $fieldName = $field['field_name'] ?? $field['name'] ?? '';
                    $value = $data[$fieldName] ?? '';
                    $sheet->setCellValue($col . $rowNum, $value);
                    $col++;
                }
                $rowNum++;
            }

            // Create filename
            $filename = $dataset['dataset_slug'] . '_export_' . date('Y-m-d_H-i-s') . '.xlsx';

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal export dataset: ' . $e->getMessage());
        }
    }

    /**
     * Delete column from dataset schema
     */
    public function deleteColumn()
    {
        // Debug: Log the request
        log_message('debug', 'deleteColumn called with method: ' . $this->request->getMethod());

        // Check if this is an AJAX request
        if (!$this->request->isAJAX()) {
            log_message('error', 'deleteColumn: Not an AJAX request');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            log_message('error', 'deleteColumn: Unauthorized - logged_in: ' . session()->get('logged_in') . ', role: ' . session()->get('role_name'));
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $datasetId = $this->request->getPost('dataset_id');
        $columnName = $this->request->getPost('column_name');

        log_message('debug', 'deleteColumn: dataset_id=' . $datasetId . ', column_name=' . $columnName);

        if (!$datasetId || !$columnName) {
            log_message('error', 'deleteColumn: Missing dataset_id or column_name');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dataset ID dan nama kolom harus diisi'
            ]);
        }

        $applicationId = session()->get('application_id');
        log_message('debug', 'deleteColumn: application_id=' . $applicationId);

        // Get dataset
        $dataset = $this->datasetModel
            ->where('id', $datasetId)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$dataset) {
            log_message('error', 'deleteColumn: Dataset not found - id=' . $datasetId . ', app_id=' . $applicationId);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dataset tidak ditemukan'
            ]);
        }

        try {
            // Get current schema
            $schema = json_decode($dataset['schema_config'], true);
            log_message('debug', 'deleteColumn: schema_config=' . $dataset['schema_config']);

            if (!$schema) {
                log_message('error', 'deleteColumn: Invalid schema JSON');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Schema dataset tidak valid'
                ]);
            }

            // Find and remove the column from schema
            $columnFound = false;
            $newSchema = [];
            foreach ($schema as $field) {
                log_message('debug', 'deleteColumn: checking field=' . $field['field_name']);
                if ($field['field_name'] !== $columnName) {
                    $newSchema[] = $field;
                } else {
                    $columnFound = true;
                    log_message('debug', 'deleteColumn: found column to delete=' . $columnName);
                }
            }

            if (!$columnFound) {
                log_message('error', 'deleteColumn: Column not found in schema - column=' . $columnName);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Kolom tidak ditemukan dalam schema'
                ]);
            }

            // Update all records to remove the column from JSON data
            $records = $this->recordModel->where('dataset_id', $datasetId)->findAll();
            foreach ($records as $record) {
                $data = json_decode($record['data_json'], true);
                if (isset($data[$columnName])) {
                    unset($data[$columnName]);
                    $this->recordModel->update($record['id'], [
                        'data_json' => json_encode($data)
                    ]);
                }
            }

            // Update schema in database
            $updated = $this->datasetModel->update($datasetId, [
                'schema_config' => json_encode($newSchema),
                'total_columns' => count($newSchema),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            log_message('debug', 'deleteColumn: update result=' . ($updated ? 'success' : 'failed'));

            if ($updated) {
                // Log aktivitas
                $this->logActivity('update', 'datasets', 'Owner menghapus kolom ' . $columnName . ' dari dataset: ' . $dataset['dataset_name'], [
                    'dataset_id' => $datasetId,
                    'column_name' => $columnName
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Kolom berhasil dihapus dari schema dataset'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal mengupdate schema dataset'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'deleteColumn: Exception - ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Log aktivitas
     */
    private function logActivity($activityType, $module, $description, $data = [])
    {
        $logData = [
            'user_id' => session()->get('user_id'),
            'application_id' => session()->get('application_id'),
            'activity_type' => $activityType,
            'module' => $module,
            'description' => $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'request_data' => json_encode($data)
        ];

        $db = \Config\Database::connect();
        $db->table('log_activities')->insert($logData);
    }
}
