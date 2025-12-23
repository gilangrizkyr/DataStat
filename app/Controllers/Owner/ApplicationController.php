<?php

/**
 * ============================================================================
 * OWNER APPLICATION CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Owner/ApplicationController.php
 * 
 * Deskripsi:
 * Controller untuk mengelola aplikasi/workspace owner.
 * Owner dapat membuat, mengedit, dan mengatur aplikasi mereka.
 * 
 * Fitur:
 * - Buat aplikasi/workspace baru
 * - Edit informasi aplikasi
 * - Pengaturan aplikasi (warna, logo, dll)
 * - View detail aplikasi
 * 
 * Role: Owner
 * ============================================================================
 */

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\Owner\ApplicationModel;
use App\Models\Superadmin\UserRoleModel;

class ApplicationController extends BaseController
{
    protected $applicationModel;
    protected $userRoleModel;

    public function __construct()
    {
        $this->applicationModel = new ApplicationModel();
        $this->userRoleModel = new UserRoleModel();
        helper(['form', 'url', 'text']);
    }

    /**
     * Tampilkan detail aplikasi
     */
    public function index()
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        // Jika belum punya aplikasi, redirect ke create
        if (!$applicationId) {
            return redirect()->to('/owner/application/create')
                ->with('info', 'Silakan buat aplikasi/workspace terlebih dahulu');
        }

        $application = $this->applicationModel->find($applicationId);

        if (!$application) {
            return redirect()->to('/owner/application/create')
                ->with('error', 'Aplikasi tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Aplikasi',
            'application' => $application,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/application/index', $data);
    }

    /**
     * Form buat aplikasi baru
     */
    public function create()
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        // Cek apakah sudah punya aplikasi
        $userId = session()->get('user_id');
        $existingApp = $this->applicationModel
            ->where('user_id', $userId)
            ->where('deleted_at', null)
            ->first();

        if ($existingApp) {
            return redirect()->to('/owner/application')
                ->with('info', 'Anda sudah memiliki aplikasi. Silakan kelola aplikasi yang ada.');
        }

        $data = [
            'title' => 'Buat Aplikasi Baru',
            'validation' => \Config\Services::validation()
        ];

        return view('owner/application/create', $data);
    }

    /**
     * Proses simpan aplikasi baru
     */
    public function store()
    {
        // Validasi input
        $rules = [
            'app_name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama aplikasi harus diisi',
                    'min_length' => 'Nama aplikasi minimal 3 karakter',
                    'max_length' => 'Nama aplikasi maksimal 255 karakter'
                ]
            ],
            'bidang' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Bidang harus diisi',
                    'min_length' => 'Bidang minimal 3 karakter',
                    'max_length' => 'Bidang maksimal 100 karakter'
                ]
            ],
            'description' => [
                'rules' => 'permit_empty|max_length[1000]',
                'errors' => [
                    'max_length' => 'Deskripsi maksimal 1000 karakter'
                ]
            ],
            'color_theme' => [
                'rules' => 'permit_empty|in_list[blue,green,red,purple,orange,teal,indigo,pink]',
                'errors' => [
                    'in_list' => 'Tema warna tidak valid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $userId = session()->get('user_id');

            // Generate slug dari app_name
            $appName = $this->request->getPost('app_name');
            $slug = url_title($appName, '-', true);
            
            // Pastikan slug unik
            $existingSlug = $this->applicationModel->where('app_slug', $slug)->first();
            if ($existingSlug) {
                $slug = $slug . '-' . uniqid();
            }

            // Data aplikasi
            $appData = [
                'user_id' => $userId,
                'app_name' => $appName,
                'app_slug' => $slug,
                'bidang' => $this->request->getPost('bidang'),
                'description' => $this->request->getPost('description'),
                'color_theme' => $this->request->getPost('color_theme') ?? 'blue',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Upload logo jika ada
            $logo = $this->request->getFile('logo');
            if ($logo && $logo->isValid() && !$logo->hasMoved()) {
                $newName = $logo->getRandomName();
                $logo->move(FCPATH . 'uploads/logos', $newName);
                $appData['logo'] = 'uploads/logos/' . $newName;
            }

            // Insert aplikasi
            $applicationId = $this->applicationModel->insert($appData);

            if (!$applicationId) {
                throw new \Exception('Gagal menyimpan aplikasi');
            }

            // Update user_roles dengan application_id
            $this->userRoleModel
                ->where('user_id', $userId)
                ->where('role_id', session()->get('role_id'))
                ->set(['application_id' => $applicationId])
                ->update();

            // Update session dengan application_id
            session()->set([
                'application_id' => $applicationId,
                'app_name' => $appName,
                'app_slug' => $slug
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal');
            }

            // Log aktivitas
            $this->logActivity('create', 'applications', 'Owner membuat aplikasi baru: ' . $appName, [
                'application_id' => $applicationId
            ]);

            return redirect()->to('/owner/dashboard')
                ->with('success', 'Aplikasi berhasil dibuat! Selamat datang di workspace Anda.');

        } catch (\Exception $e) {
            $db->transRollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat aplikasi: ' . $e->getMessage());
        }
    }

    /**
     * Halaman pengaturan aplikasi
     */
    public function settings()
    {
        // Cek login dan role
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return redirect()->to('/owner/application/create');
        }

        $application = $this->applicationModel->find($applicationId);

        if (!$application) {
            return redirect()->to('/owner/dashboard')
                ->with('error', 'Aplikasi tidak ditemukan');
        }

        $data = [
            'title' => 'Pengaturan Aplikasi',
            'application' => $application,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/application/settings', $data);
    }

    /**
     * Update pengaturan aplikasi
     */
    public function update($id = null)
    {
        // Validasi owner
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login')->with('error', 'Anda harus login sebagai owner');
        }

        $applicationId = $id ?? session()->get('application_id');

        // Cek kepemilikan aplikasi
        $application = $this->applicationModel->find($applicationId);
        
        if (!$application || $application['user_id'] != session()->get('user_id')) {
            return redirect()->to('/owner/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke aplikasi ini');
        }

        // Validasi input
        $rules = [
            'app_name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Nama aplikasi harus diisi',
                    'min_length' => 'Nama aplikasi minimal 3 karakter',
                    'max_length' => 'Nama aplikasi maksimal 255 karakter'
                ]
            ],
            'bidang' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Bidang harus diisi',
                    'min_length' => 'Bidang minimal 3 karakter',
                    'max_length' => 'Bidang maksimal 100 karakter'
                ]
            ],
            'description' => [
                'rules' => 'permit_empty|max_length[1000]',
                'errors' => [
                    'max_length' => 'Deskripsi maksimal 1000 karakter'
                ]
            ],
            'color_theme' => [
                'rules' => 'permit_empty|in_list[blue,green,red,purple,orange,teal,indigo,pink]',
                'errors' => [
                    'in_list' => 'Tema warna tidak valid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $appName = $this->request->getPost('app_name');

            // Data update
            $updateData = [
                'app_name' => $appName,
                'bidang' => $this->request->getPost('bidang'),
                'description' => $this->request->getPost('description'),
                'color_theme' => $this->request->getPost('color_theme') ?? 'blue',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Upload logo baru jika ada
            $logo = $this->request->getFile('logo');
            if ($logo && $logo->isValid() && !$logo->hasMoved()) {
                // Hapus logo lama
                if ($application['logo'] && file_exists(FCPATH . $application['logo'])) {
                    unlink(FCPATH . $application['logo']);
                }

                $newName = $logo->getRandomName();
                $logo->move(FCPATH . 'uploads/logos', $newName);
                $updateData['logo'] = 'uploads/logos/' . $newName;
            }

            // Update aplikasi
            $updated = $this->applicationModel->update($applicationId, $updateData);

            if ($updated) {
                // Update session
                session()->set([
                    'app_name' => $appName
                ]);

                // Log aktivitas
                $this->logActivity('update', 'applications', 'Owner mengupdate aplikasi: ' . $appName, [
                    'application_id' => $applicationId
                ]);

                return redirect()->back()
                    ->with('success', 'Pengaturan aplikasi berhasil diupdate');
            } else {
                return redirect()->back()
                    ->with('error', 'Tidak ada perubahan data');
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal update aplikasi: ' . $e->getMessage());
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