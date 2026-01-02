<?php

/**
 * ============================================================================
 * DATASET API CONTROLLER
 * ============================================================================
 *
 * Path: app/Controllers/Api/DatasetApiController.php
 *
 * Deskripsi:
 * API Controller untuk mengelola dataset operations via AJAX/JSON.
 * Menyediakan endpoints untuk mendapatkan field information dari dataset.
 *
 * Endpoints:
 * - GET /api/dataset/fields/{id} - Get dataset fields
 * - GET /api/dataset/preview/{id} - Get dataset preview
 *
 * Role: Owner, Superadmin, Viewer
 * ============================================================================
 */

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Owner\DatasetModel;

class DatasetApiController extends BaseController
{
    protected $datasetModel;

    public function __construct()
    {
        $this->datasetModel = new DatasetModel();
        helper(['form', 'url']);
    }

    /**
     * Get dataset fields
     * GET /api/dataset/fields/{id}
     */
    public function getFields($datasetId)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        // Validate dataset ID
        if (!is_numeric($datasetId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid dataset ID'
            ])->setStatusCode(400);
        }

        // Check if user is authenticated and application is selected
        if (!session()->has('application_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Application not selected'
            ])->setStatusCode(403);
        }

        try {
            // Get dataset info
            $dataset = $this->datasetModel
                ->where('id', $datasetId)
                ->where('upload_status', 'completed')
                ->where('deleted_at', null)
                ->first();

            if (!$dataset) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Dataset not found'
                ])->setStatusCode(404);
            }

            // Check ownership (user must be owner of the application)
            $applicationId = session()->get('application_id');
            if ($dataset['application_id'] != $applicationId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Access denied'
                ])->setStatusCode(403);
            }

            // Get schema from dataset
            $schema = json_decode($dataset['schema_definition'], true);

            if (!$schema || !isset($schema['fields'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Dataset schema not available'
                ])->setStatusCode(500);
            }

            // Format fields for response
            $fields = [];
            foreach ($schema['fields'] as $field) {
                $fields[] = [
                    'name' => $field['name'],
                    'type' => $field['type'],
                    'nullable' => $field['nullable'] ?? true
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'fields' => $fields
            ]);
        } catch (\Exception $e) {
            log_message('error', 'DatasetApiController::getFields error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Internal server error'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get dataset preview
     * GET /api/dataset/preview/{id}
     */
    public function preview($datasetId)
    {
        // Check if request is AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        // Validate dataset ID
        if (!is_numeric($datasetId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid dataset ID'
            ])->setStatusCode(400);
        }

        try {
            // Get dataset info
            $dataset = $this->datasetModel
                ->where('id', $datasetId)
                ->where('upload_status', 'completed')
                ->where('deleted_at', null)
                ->first();

            if (!$dataset) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Dataset not found'
                ])->setStatusCode(404);
            }

            // Check ownership
            $applicationId = session()->get('application_id');
            if ($dataset['application_id'] != $applicationId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Access denied'
                ])->setStatusCode(403);
            }

            // Get preview data (first 10 rows)
            $previewData = json_decode($dataset['preview_data'], true);

            if (!$previewData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Preview data not available'
                ])->setStatusCode(500);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $previewData
            ]);
        } catch (\Exception $e) {
            log_message('error', 'DatasetApiController::preview error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Internal server error'
            ])->setStatusCode(500);
        }
    }
}
