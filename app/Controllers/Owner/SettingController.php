<?php

/**
 * ============================================================================
 * OWNER SETTING CONTROLLER
 * ============================================================================
 * 
 * Path: app/Controllers/Owner/SettingController.php
 * 
 * Deskripsi:
 * Controller untuk pengaturan workspace owner.
 * Setting preferences, notifications, data retention, dll.
 * 
 * Fitur:
 * - View settings
 * - Update general settings
 * - Notification preferences
 * - Data retention settings
 * - Export/import settings
 * 
 * Role: Owner
 * ============================================================================
 */

namespace App\Controllers\Owner;

use App\Controllers\BaseController;
use App\Models\Owner\ApplicationModel;

class SettingController extends BaseController
{
    protected $applicationModel;

    public function __construct()
    {
        $this->applicationModel = new ApplicationModel();
        helper(['form', 'url']);
    }

    /**
     * View settings
     */
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        $applicationId = session()->get('application_id');

        if (!$applicationId) {
            return redirect()->to('/owner/application/create');
        }

        $application = $this->applicationModel->find($applicationId);

        // Parse settings JSON
        $settings = json_decode($application['settings'] ?? '{}', true);

        $data = [
            'title' => 'Pengaturan',
            'application' => $application,
            'settings' => $settings,
            'validation' => \Config\Services::validation()
        ];

        return view('owner/settings/index', $data);
    }

    /**
     * Update settings
     */
    public function update()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        try {
            $applicationId = session()->get('application_id');

            // Build settings array
            $settings = [
                'notifications' => [
                    'email_enabled' => $this->request->getPost('email_notifications') ? true : false,
                    'dataset_upload' => $this->request->getPost('notify_upload') ? true : false,
                    'statistic_calculated' => $this->request->getPost('notify_calculated') ? true : false
                ],
                'data_retention' => [
                    'auto_cleanup_enabled' => $this->request->getPost('auto_cleanup') ? true : false,
                    'retention_days' => $this->request->getPost('retention_days') ?? 365
                ],
                'features' => [
                    'allow_public_dashboard' => $this->request->getPost('public_dashboard') ? true : false,
                    'allow_data_export' => $this->request->getPost('data_export') ? true : false,
                    'enable_api_access' => $this->request->getPost('api_access') ? true : false
                ],
                'display' => [
                    'records_per_page' => $this->request->getPost('records_per_page') ?? 50,
                    'default_chart_type' => $this->request->getPost('default_chart') ?? 'bar_chart',
                    'date_format' => $this->request->getPost('date_format') ?? 'd-m-Y'
                ]
            ];

            // Prepare update data
            $updateData = [
                'settings' => json_encode($settings),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Add app_name and bidang if provided
            if ($this->request->getPost('app_name')) {
                $updateData['app_name'] = $this->request->getPost('app_name');
            }
            if ($this->request->getPost('bidang')) {
                $updateData['bidang'] = $this->request->getPost('bidang');
            }

            $this->applicationModel->update($applicationId, $updateData);

            $this->logActivity('update', 'settings', 'Owner update workspace settings');

            return redirect()->back()->with('success', 'Pengaturan berhasil disimpan');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Export settings
     */
    public function export()
    {
        if (!session()->get('logged_in') || session()->get('role_name') !== 'owner') {
            return redirect()->to('/login');
        }

        try {
            $applicationId = session()->get('application_id');
            $application = $this->applicationModel->find($applicationId);

            $exportData = [
                'application' => [
                    'name' => $application['app_name'],
                    'bidang' => $application['bidang'],
                    'color_theme' => $application['color_theme']
                ],
                'settings' => json_decode($application['settings'] ?? '{}', true),
                'exported_at' => date('Y-m-d H:i:s'),
                'version' => '1.0'
            ];

            $this->logActivity('export', 'settings', 'Owner export settings');

            return $this->response
                ->setHeader('Content-Type', 'application/json')
                ->setHeader('Content-Disposition', 'attachment; filename="settings-' . date('YmdHis') . '.json"')
                ->setBody(json_encode($exportData, JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export: ' . $e->getMessage());
        }
    }

    /**
     * Import settings
     */
    public function import()
    {
        $rules = [
            'settings_file' => 'uploaded[settings_file]|ext_in[settings_file,json]|max_size[settings_file,1024]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        try {
            $file = $this->request->getFile('settings_file');
            $content = file_get_contents($file->getTempName());
            $importData = json_decode($content, true);

            if (!$importData || !isset($importData['settings'])) {
                return redirect()->back()->with('error', 'File settings tidak valid');
            }

            $applicationId = session()->get('application_id');

            // Update settings
            $this->applicationModel->update($applicationId, [
                'settings' => json_encode($importData['settings']),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->logActivity('import', 'settings', 'Owner import settings');

            return redirect()->back()->with('success', 'Settings berhasil diimport');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Reset settings to default
     */
    public function reset()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        try {
            $applicationId = session()->get('application_id');

            $defaultSettings = [
                'notifications' => [
                    'email_enabled' => true,
                    'dataset_upload' => true,
                    'statistic_calculated' => true
                ],
                'data_retention' => [
                    'auto_cleanup_enabled' => false,
                    'retention_days' => 365
                ],
                'features' => [
                    'allow_public_dashboard' => true,
                    'allow_data_export' => true,
                    'enable_api_access' => false
                ],
                'display' => [
                    'records_per_page' => 50,
                    'default_chart_type' => 'bar_chart',
                    'date_format' => 'd-m-Y'
                ]
            ];

            $this->applicationModel->update($applicationId, [
                'settings' => json_encode($defaultSettings),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->logActivity('reset', 'settings', 'Owner reset settings to default');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Settings berhasil direset ke default'
            ]);

        } catch (\Exception $e) {
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