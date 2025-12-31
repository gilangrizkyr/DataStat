<?php

/**
 * ============================================================================
 * OWNER STATISTIC CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Owner/StatisticController.php
 * 
 * Deskripsi:
 * Controller untuk mengelola statistik yang sudah dibuat.
 * CRUD untuk statistik configuration, view hasil perhitungan, export, dll.
 * 
 * Fitur:
 * - List semua statistik
 * - Create statistik baru (basic form)
 * - Edit statistik
 * - Delete statistik
 * - Duplicate statistik
 * - Toggle active/inactive
 * - Recalculate statistik
 * 
 * Note: Untuk statistik builder yang kompleks ada di StatisticBuilderController
 * 
 * Role: Owner
 * ============================================================================
 */

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\Owner\StatisticConfigModel;
use App\Models\Owner\DatasetModel;
use App\Libraries\ComputationEngine;

class StatisticController extends BaseController
{
    protected $statisticModel;
    protected $datasetModel;
    protected $computationEngine;

    public function __construct()
    {
        $this->statisticModel = new StatisticConfigModel();
        $this->datasetModel = new DatasetModel();
        $this->computationEngine = new ComputationEngine();
        helper(['form', 'url', 'text']);
    }

    /**
     * List semua statistik
     */
    public function index()
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return redirect()->to('/owner/application/create');
        }

        // Get all statistics
        $statistics = $this->statisticModel
            ->select('statistic_configs.*, datasets.dataset_name, users.nama_lengkap as creator_name')
            ->join('datasets', 'datasets.id = statistic_configs.dataset_id')
            ->join('users', 'users.id = statistic_configs.created_by')
            ->where('statistic_configs.application_id', $applicationId)
            ->where('statistic_configs.deleted_at', null)
            ->orderBy('statistic_configs.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Kelola Statistik',
            'statistics' => $statistics
        ];

        return view('owner/statistics/index', $data);
    }

    /**
     * Form create statistik sederhana
     * Untuk yang kompleks menggunakan StatisticBuilderController
     */
    public function create()
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return redirect()->to('/owner/application/create');
        }

        // Get available datasets
        $datasets = $this->datasetModel
            ->where('application_id', $applicationId)
            ->where('upload_status', 'completed')
            ->where('deleted_at', null)
            ->findAll();

        if (empty($datasets)) {
            return redirect()->to('/owner/datasets/upload')
                ->with('info', 'Silakan upload dataset terlebih dahulu sebelum membuat statistik');
        }

        $data = [
            'title' => 'Buat Statistik Baru',
            'datasets' => $datasets,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/statistics/create', $data);
    }

    /**
     * Store statistik baru
     */
    public function store()
    {
        // Validasi input
        $rules = [
            'stat_name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama statistik harus diisi',
                    'min_length' => 'Nama statistik minimal 3 karakter',
                    'max_length' => 'Nama statistik maksimal 255 karakter'
                ]
            ],
            'dataset_id' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Dataset harus dipilih',
                    'numeric' => 'Dataset tidak valid'
                ]
            ],
            'metric_type' => [
                'rules' => 'required|in_list[count,sum,average,min,max,percentage,ratio,growth,ranking,custom_formula]',
                'errors' => [
                    'required' => 'Tipe metrik harus dipilih',
                    'in_list' => 'Tipe metrik tidak valid'
                ]
            ],
            'visualization_type' => [
                'rules' => 'required|in_list[table,bar_chart,pie_chart,line_chart,area_chart,kpi_card,progress_bar,donut_chart,scatter_chart]',
                'errors' => [
                    'required' => 'Tipe visualisasi harus dipilih',
                    'in_list' => 'Tipe visualisasi tidak valid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $applicationId = session()->get('application_id');
            $userId = session()->get('user_id');

            $statName = $this->request->getPost('stat_name');
            $slug = url_title($statName, '-', true);

            // Ensure unique slug
            $existingSlug = $this->statisticModel
                ->where('application_id', $applicationId)
                ->where('stat_slug', $slug)
                ->first();

            if ($existingSlug) {
                $slug = $slug . '-' . uniqid();
            }

            // Data statistik
            $statData = [
                'application_id' => $applicationId,
                'dataset_id' => $this->request->getPost('dataset_id'),
                'stat_name' => $statName,
                'stat_slug' => $slug,
                'description' => $this->request->getPost('description'),
                'metric_type' => $this->request->getPost('metric_type'),
                'target_field' => $this->request->getPost('target_field'),
                'visualization_type' => $this->request->getPost('visualization_type'),
                'is_active' => 1,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $statisticId = $this->statisticModel->insert($statData);

            if ($statisticId) {
                // Log aktivitas
                $this->logActivity('create', 'statistics', 'Owner membuat statistik: ' . $statName, [
                    'statistic_id' => $statisticId
                ]);

                // Redirect ke builder untuk konfigurasi lanjutan
                return redirect()->to('/owner/statistics/builder/' . $statisticId)
                    ->with('success', 'Statistik berhasil dibuat! Silakan konfigurasi detail statistik.');
            } else {
                throw new \Exception('Gagal menyimpan statistik');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat statistik: ' . $e->getMessage());
        }
    }

    /**
     * Edit statistik
     */
    public function edit($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get statistik
        $statistic = $this->statisticModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            return redirect()->to('/owner/statistics')
                ->with('error', 'Statistik tidak ditemukan');
        }

        // Get datasets
        $datasets = $this->datasetModel
            ->where('application_id', $applicationId)
            ->where('upload_status', 'completed')
            ->where('deleted_at', null)
            ->findAll();

        $data = [
            'title' => 'Edit Statistik: ' . $statistic['stat_name'],
            'statistic' => $statistic,
            'datasets' => $datasets,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/statistics/edit', $data);
    }

    /**
     * Update statistik
     */
    public function update($id)
    {
        // Validasi
        $rules = [
            'stat_name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama statistik harus diisi',
                    'min_length' => 'Nama statistik minimal 3 karakter',
                    'max_length' => 'Nama statistik maksimal 255 karakter'
                ]
            ],
            'metric_type' => [
                'rules' => 'required|in_list[count,sum,average,min,max,percentage,ratio,growth,ranking,custom_formula]',
                'errors' => [
                    'required' => 'Tipe metrik harus dipilih',
                    'in_list' => 'Tipe metrik tidak valid'
                ]
            ],
            'visualization_type' => [
                'rules' => 'required|in_list[table,bar_chart,pie_chart,line_chart,area_chart,kpi_card,progress_bar,donut_chart,scatter_chart]',
                'errors' => [
                    'required' => 'Tipe visualisasi harus dipilih',
                    'in_list' => 'Tipe visualisasi tidak valid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $applicationId = session()->get('application_id');

        // Cek ownership
        $statistic = $this->statisticModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            return redirect()->to('/owner/statistics')
                ->with('error', 'Statistik tidak ditemukan');
        }

        try {
            $updateData = [
                'stat_name' => $this->request->getPost('stat_name'),
                'description' => $this->request->getPost('description'),
                'metric_type' => $this->request->getPost('metric_type'),
                'target_field' => $this->request->getPost('target_field'),
                'visualization_type' => $this->request->getPost('visualization_type'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->statisticModel->update($id, $updateData);

            if ($updated) {
                // Log aktivitas
                $this->logActivity('update', 'statistics', 'Owner update statistik: ' . $updateData['stat_name'], [
                    'statistic_id' => $id
                ]);

                return redirect()->to('/owner/statistics')
                    ->with('success', 'Statistik berhasil diupdate');
            } else {
                return redirect()->back()
                    ->with('error', 'Tidak ada perubahan data');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update statistik: ' . $e->getMessage());
        }
    }

    /**
     * Detail statistik dengan hasil perhitungan
     */
    public function detail($id)
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Get statistik
        $statistic = $this->statisticModel
            ->select('statistic_configs.*, datasets.dataset_name')
            ->join('datasets', 'datasets.id = statistic_configs.dataset_id')
            ->where('statistic_configs.id', $id)
            ->where('statistic_configs.application_id', $applicationId)
            ->where('statistic_configs.deleted_at', null)
            ->first();

        if (!$statistic) {
            return redirect()->to('/owner/statistics')
                ->with('error', 'Statistik tidak ditemukan');
        }

        // Hitung statistik (atau ambil dari cache)
        try {
            // Prepare config for computation engine
            $config = $statistic;
            // Decode JSON fields
            if (!empty($config['group_by_fields']) && is_string($config['group_by_fields'])) {
                $config['group_by_fields'] = json_decode($config['group_by_fields'], true);
            }
            if (!empty($config['filters']) && is_string($config['filters'])) {
                $config['filters'] = json_decode($config['filters'], true);
            }
            if (!empty($config['calculation_config']) && is_string($config['calculation_config'])) {
                $config['calculation_config'] = json_decode($config['calculation_config'], true);
            }
            if (!empty($config['visualization_config']) && is_string($config['visualization_config'])) {
                $config['visualization_config'] = json_decode($config['visualization_config'], true);
            }

            $result = $this->computationEngine->calculate($config);
        } catch (\Exception $e) {
            return redirect()->to('/owner/statistics')
                ->with('error', 'Error menghitung statistik: ' . $e->getMessage());
        }

        $data = [
            'title' => 'Detail Statistik: ' . $statistic['stat_name'],
            'statistic' => $statistic,
            'result' => $result
        ];

        return view('owner/statistics/detail', $data);
    }

    /**
     * Preview statistik (untuk AJAX)
     */
    public function preview($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        // Get statistik
        $statistic = $this->statisticModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Statistik tidak ditemukan'
            ]);
        }

        try {
            // Prepare config for computation engine
            $config = $statistic;
            // Decode JSON fields
            if (!empty($config['group_by_fields']) && is_string($config['group_by_fields'])) {
                $config['group_by_fields'] = json_decode($config['group_by_fields'], true);
            }
            if (!empty($config['filters']) && is_string($config['filters'])) {
                $config['filters'] = json_decode($config['filters'], true);
            }
            if (!empty($config['calculation_config']) && is_string($config['calculation_config'])) {
                $config['calculation_config'] = json_decode($config['calculation_config'], true);
            }
            if (!empty($config['visualization_config']) && is_string($config['visualization_config'])) {
                $config['visualization_config'] = json_decode($config['visualization_config'], true);
            }

            // Calculate statistik
            $result = $this->computationEngine->calculate($config);

            return $this->response->setJSON([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle active/inactive
     */
    public function toggleActive($id)
    {
        $applicationId = session()->get('application_id');

        $statistic = $this->statisticModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Statistik tidak ditemukan'
                ]);
            } else {
                return redirect()->to('/owner/statistics')
                    ->with('error', 'Statistik tidak ditemukan');
            }
        }

        try {
            $newStatus = $statistic['is_active'] == 1 ? 0 : 1;

            $updated = $this->statisticModel->update($id, [
                'is_active' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($updated) {
                // Log aktivitas
                $this->logActivity('update', 'statistics', 'Owner toggle status statistik: ' . $statistic['stat_name'], [
                    'statistic_id' => $id,
                    'new_status' => $newStatus
                ]);

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Status statistik berhasil diubah',
                        'new_status' => $newStatus
                    ]);
                } else {
                    return redirect()->to('/owner/statistics')
                        ->with('success', 'Status statistik berhasil diubah');
                }
            } else {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal mengubah status'
                    ]);
                } else {
                    return redirect()->to('/owner/statistics')
                        ->with('error', 'Gagal mengubah status');
                }
            }
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            } else {
                return redirect()->to('/owner/statistics')
                    ->with('error', 'Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Duplicate statistik
     */
    public function duplicate($id)
    {
        $applicationId = session()->get('application_id');
        $userId = session()->get('user_id');

        $statistic = $this->statisticModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            return redirect()->to('/owner/statistics')
                ->with('error', 'Statistik tidak ditemukan');
        }

        try {
            // Copy data statistik
            unset($statistic['id']);
            $statistic['stat_name'] = $statistic['stat_name'] . ' (Copy)';
            $statistic['stat_slug'] = $statistic['stat_slug'] . '-copy-' . uniqid();
            $statistic['created_by'] = $userId;
            $statistic['created_at'] = date('Y-m-d H:i:s');
            $statistic['updated_at'] = date('Y-m-d H:i:s');

            $newId = $this->statisticModel->insert($statistic);

            if ($newId) {
                // Log aktivitas
                $this->logActivity('duplicate', 'statistics', 'Owner duplicate statistik: ' . $statistic['stat_name'], [
                    'original_id' => $id,
                    'new_id' => $newId
                ]);

                return redirect()->to('/owner/statistics/edit/' . $newId)
                    ->with('success', 'Statistik berhasil diduplikasi');
            } else {
                throw new \Exception('Gagal menduplikasi statistik');
            }
        } catch (\Exception $e) {
            return redirect()->to('/owner/statistics')
                ->with('error', 'Gagal duplicate statistik: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate statistik
     */
    public function recalculate($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        $applicationId = session()->get('application_id');

        $statistic = $this->statisticModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Statistik tidak ditemukan'
            ]);
        }

        try {
            // Prepare config for computation engine
            $config = $statistic;
            // Decode JSON fields
            if (!empty($config['group_by_fields']) && is_string($config['group_by_fields'])) {
                $config['group_by_fields'] = json_decode($config['group_by_fields'], true);
            }
            if (!empty($config['filters']) && is_string($config['filters'])) {
                $config['filters'] = json_decode($config['filters'], true);
            }
            if (!empty($config['calculation_config']) && is_string($config['calculation_config'])) {
                $config['calculation_config'] = json_decode($config['calculation_config'], true);
            }
            if (!empty($config['visualization_config']) && is_string($config['visualization_config'])) {
                $config['visualization_config'] = json_decode($config['visualization_config'], true);
            }

            // Recalculate
            $result = $this->computationEngine->calculate($config, true); // Force recalculate

            // Update cache dan last_calculated
            $this->statisticModel->update($id, [
                'cached_result' => json_encode($result),
                'last_calculated' => date('Y-m-d H:i:s')
            ]);

            // Log aktivitas
            $this->logActivity('recalculate', 'statistics', 'Owner recalculate statistik: ' . $statistic['stat_name'], [
                'statistic_id' => $id
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Statistik berhasil dihitung ulang',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete statistik (soft delete)
     */
    public function delete($id)
    {
        $applicationId = session()->get('application_id');

        // Debug: Check if statistic exists
        $statistic = $this->statisticModel
            ->where('id', $id)
            ->where('application_id', $applicationId)
            ->where('deleted_at', null)
            ->first();

        if (!$statistic) {
            // Debug: Check if statistic exists at all
            $exists = $this->statisticModel->find($id);
            $message = 'Statistik tidak ditemukan';
            if ($exists) {
                if ($exists['application_id'] != $applicationId) {
                    $message = 'Statistik tidak milik aplikasi ini';
                } elseif ($exists['deleted_at'] != null) {
                    $message = 'Statistik sudah dihapus sebelumnya';
                }
            }

            if ($this->request->isAJAX() || $this->request->getMethod() === 'POST') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $message
                ]);
            } else {
                return redirect()->to('/owner/statistics')
                    ->with('error', $message);
            }
        }

        // Handle GET request - show confirmation page
        if ($this->request->getMethod() === 'GET') {
            $data = [
                'title' => 'Konfirmasi Hapus Statistik',
                'statistic' => $statistic
            ];

            return view('owner/statistics/delete', $data);
        }

        // Handle POST/AJAX request - perform deletion
        try {
            // Use direct database update for soft delete to avoid model issues
            $db = \Config\Database::connect();
            $deleted = $db->table('statistic_configs')
                ->where('id', $id)
                ->where('application_id', $applicationId)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            if ($deleted) {
                // Log aktivitas
                $this->logActivity('delete', 'statistics', 'Owner menghapus statistik: ' . $statistic['stat_name'], [
                    'statistic_id' => $id
                ]);

                if ($this->request->isAJAX() || $this->request->getMethod() === 'POST') {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Statistik berhasil dihapus'
                    ]);
                } else {
                    return redirect()->to('/owner/statistics')
                        ->with('success', 'Statistik berhasil dihapus');
                }
            } else {
                if ($this->request->isAJAX() || $this->request->getMethod() === 'POST') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal menghapus statistik: Tidak ada data yang diupdate'
                    ]);
                } else {
                    return redirect()->to('/owner/statistics')
                        ->with('error', 'Gagal menghapus statistik: Tidak ada data yang diupdate');
                }
            }
        } catch (\Exception $e) {
            if ($this->request->isAJAX() || $this->request->getMethod() === 'POST') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            } else {
                return redirect()->to('/owner/statistics')
                    ->with('error', 'Error: ' . $e->getMessage());
            }
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
