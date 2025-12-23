<?php

/**
 * ============================================================================
 * AUDIT LOGGER LIBRARY
 * ============================================================================
 * 
 * Path: app/Libraries/AuditLogger.php
 * 
 * Deskripsi:
 * Library untuk logging aktivitas user di sistem (audit trail).
 * Wrapper untuk LogActivityModel dengan helper methods.
 * 
 * Features:
 * - Easy logging interface
 * - Auto-capture IP & user agent
 * - Support untuk JSON data
 * - Pre-defined activity types
 * - Batch logging support
 * 
 * Used by: Semua controllers yang memerlukan audit trail
 * ============================================================================
 */

namespace App\Libraries;

use App\Models\Superadmin\LogActivityModel;

class AuditLogger
{
    protected $request;
    protected $session;
    
    /**
     * Activity types
     */
    const TYPE_LOGIN = 'login';
    const TYPE_LOGOUT = 'logout';
    const TYPE_FAILED_LOGIN = 'failed_login';
    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';
    const TYPE_VIEW = 'view';
    const TYPE_EXPORT = 'export';
    const TYPE_IMPORT = 'import';
    const TYPE_DOWNLOAD = 'download';
    const TYPE_UPLOAD = 'upload';
    const TYPE_PRINT = 'print';
    const TYPE_SHARE = 'share';
    const TYPE_INVITE = 'invite';
    const TYPE_APPROVE = 'approve';
    const TYPE_REJECT = 'reject';
    
    /**
     * Modules
     */
    const MODULE_AUTH = 'auth';
    const MODULE_USERS = 'users';
    const MODULE_ROLES = 'roles';
    const MODULE_APPLICATIONS = 'applications';
    const MODULE_DATASETS = 'datasets';
    const MODULE_STATISTICS = 'statistics';
    const MODULE_DASHBOARDS = 'dashboards';
    const MODULE_SETTINGS = 'settings';
    const MODULE_REPORTS = 'reports';

    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->session = session();
    }

    /**
     * Log aktivitas (main method)
     */
    public function log($activityType, $module, $description, $data = [])
    {
        try {
            $logData = [
                'user_id' => $this->getUserId(),
                'application_id' => $this->getApplicationId(),
                'activity_type' => $activityType,
                'module' => $module,
                'description' => $description,
                'ip_address' => $this->getIpAddress(),
                'user_agent' => $this->getUserAgent(),
                'request_data' => !empty($data) ? $data : null,
                'response_data' => null
            ];

            return LogActivityModel::log($logData);
        } catch (\Exception $e) {
            // Log error but don't throw - jangan sampai audit log mengganggu flow aplikasi
            log_message('error', 'AuditLogger error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log dengan response data
     */
    public function logWithResponse($activityType, $module, $description, $requestData = [], $responseData = [])
    {
        try {
            $logData = [
                'user_id' => $this->getUserId(),
                'application_id' => $this->getApplicationId(),
                'activity_type' => $activityType,
                'module' => $module,
                'description' => $description,
                'ip_address' => $this->getIpAddress(),
                'user_agent' => $this->getUserAgent(),
                'request_data' => !empty($requestData) ? $requestData : null,
                'response_data' => !empty($responseData) ? $responseData : null
            ];

            return LogActivityModel::log($logData);
        } catch (\Exception $e) {
            log_message('error', 'AuditLogger error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Quick log methods
     */

    public function logLogin($email, $success = true)
    {
        $type = $success ? self::TYPE_LOGIN : self::TYPE_FAILED_LOGIN;
        $description = $success 
            ? "User berhasil login: {$email}" 
            : "Login gagal untuk: {$email}";
        
        return $this->log($type, self::MODULE_AUTH, $description, ['email' => $email]);
    }

    public function logLogout()
    {
        $userName = $this->session->get('nama_lengkap') ?? 'Unknown';
        return $this->log(
            self::TYPE_LOGOUT, 
            self::MODULE_AUTH, 
            "User logout: {$userName}"
        );
    }

    public function logCreate($module, $entityName, $entityId)
    {
        return $this->log(
            self::TYPE_CREATE,
            $module,
            "Membuat {$module}: {$entityName}",
            ['entity_id' => $entityId, 'entity_name' => $entityName]
        );
    }

    public function logUpdate($module, $entityName, $entityId, $changes = [])
    {
        return $this->log(
            self::TYPE_UPDATE,
            $module,
            "Mengupdate {$module}: {$entityName}",
            [
                'entity_id' => $entityId, 
                'entity_name' => $entityName,
                'changes' => $changes
            ]
        );
    }

    public function logDelete($module, $entityName, $entityId)
    {
        return $this->log(
            self::TYPE_DELETE,
            $module,
            "Menghapus {$module}: {$entityName}",
            ['entity_id' => $entityId, 'entity_name' => $entityName]
        );
    }

    public function logView($module, $entityName, $entityId)
    {
        return $this->log(
            self::TYPE_VIEW,
            $module,
            "Melihat {$module}: {$entityName}",
            ['entity_id' => $entityId, 'entity_name' => $entityName]
        );
    }

    public function logExport($module, $entityName, $format = 'csv')
    {
        return $this->log(
            self::TYPE_EXPORT,
            $module,
            "Export {$module}: {$entityName} ke {$format}",
            ['entity_name' => $entityName, 'format' => $format]
        );
    }

    public function logUpload($module, $fileName, $fileSize)
    {
        return $this->log(
            self::TYPE_UPLOAD,
            $module,
            "Upload file: {$fileName}",
            ['file_name' => $fileName, 'file_size' => $fileSize]
        );
    }

    public function logDownload($module, $fileName)
    {
        return $this->log(
            self::TYPE_DOWNLOAD,
            $module,
            "Download file: {$fileName}",
            ['file_name' => $fileName]
        );
    }

    public function logPrint($module, $entityName)
    {
        return $this->log(
            self::TYPE_PRINT,
            $module,
            "Print {$module}: {$entityName}",
            ['entity_name' => $entityName]
        );
    }

    public function logShare($module, $entityName, $shareWith = '')
    {
        return $this->log(
            self::TYPE_SHARE,
            $module,
            "Share {$module}: {$entityName}" . ($shareWith ? " dengan {$shareWith}" : ""),
            ['entity_name' => $entityName, 'share_with' => $shareWith]
        );
    }

    public function logInvite($email, $role)
    {
        return $this->log(
            self::TYPE_INVITE,
            self::MODULE_USERS,
            "Mengundang user: {$email} sebagai {$role}",
            ['email' => $email, 'role' => $role]
        );
    }

    /**
     * Batch logging (untuk operasi massal)
     */
    public function logBatch($activityType, $module, $description, $items = [])
    {
        return $this->log(
            $activityType,
            $module,
            $description,
            ['items' => $items, 'count' => count($items)]
        );
    }

    /**
     * Get user ID dari session
     */
    protected function getUserId()
    {
        return $this->session->get('user_id');
    }

    /**
     * Get application ID dari session
     */
    protected function getApplicationId()
    {
        return $this->session->get('application_id');
    }

    /**
     * Get IP address
     */
    protected function getIpAddress()
    {
        return $this->request->getIPAddress();
    }

    /**
     * Get user agent
     */
    protected function getUserAgent()
    {
        $agent = $this->request->getUserAgent();
        if (is_object($agent)) {
            return $agent->getAgentString();
        }
        return (string)$agent;
    }

    /**
     * Log error (untuk debugging)
     */
    public function logError($module, $errorMessage, $context = [])
    {
        return $this->log(
            'error',
            $module,
            $errorMessage,
            array_merge(['error' => true], $context)
        );
    }

    /**
     * Log warning
     */
    public function logWarning($module, $warningMessage, $context = [])
    {
        return $this->log(
            'warning',
            $module,
            $warningMessage,
            array_merge(['warning' => true], $context)
        );
    }

    /**
     * Get recent activities untuk current user
     */
    public function getRecentActivities($limit = 10)
    {
        $model = new LogActivityModel();
        return $model->getByUser($this->getUserId(), $limit);
    }

    /**
     * Get activities untuk current application
     */
    public function getApplicationActivities($limit = 20)
    {
        $applicationId = $this->getApplicationId();
        if (!$applicationId) {
            return [];
        }

        $model = new LogActivityModel();
        return $model->getByApplication($applicationId, $limit);
    }

    /**
     * Check if activity should be logged (untuk filtering)
     */
    public function shouldLog($activityType, $module)
    {
        // Bisa ditambahkan logic untuk filter logging
        // Misalnya: jangan log VIEW untuk certain modules
        
        // Default: log everything
        return true;
    }
}