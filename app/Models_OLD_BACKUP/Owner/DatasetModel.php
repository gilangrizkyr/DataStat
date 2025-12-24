<?php

/**
 * ============================================================================
 * DATASET MODEL
 * ============================================================================
 * 
 * Path: app/Models/Owner/DatasetModel.php
 * 
 * Deskripsi:
 * Model untuk tabel datasets (metadata file Excel yang diupload).
 * Menyimpan informasi file, schema, dan status upload.
 * 
 * Table: datasets
 * Primary Key: id
 * 
 * Fields:
 * - id, application_id, dataset_name, dataset_slug
 * - description, file_path, file_name, file_size
 * - schema_config (JSON), upload_status, error_message
 * - total_rows, total_columns, uploaded_by
 * - created_at, updated_at, deleted_at
 * 
 * Features:
 * - Soft delete support
 * - JSON casting untuk schema_config
 * - Upload status tracking
 * - File management
 * 
 * Used by: Owner, Viewer
 * ============================================================================
 */

namespace App\Models\Owner;

use CodeIgniter\Model;

class DatasetModel extends Model
{
    protected $table            = 'datasets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'application_id',
        'dataset_name',
        'dataset_slug',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'schema_config',
        'upload_status',
        'error_message',
        'total_rows',
        'total_columns',
        'uploaded_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'application_id' => 'required|integer',
        'dataset_name'   => 'required|min_length[3]|max_length[255]',
        'file_path'      => 'required',
        'upload_status'  => 'required|in_list[processing,completed,failed]'
    ];

    protected $validationMessages = [
        'application_id' => [
            'required' => 'Application ID harus diisi'
        ],
        'dataset_name' => [
            'required'   => 'Nama dataset harus diisi',
            'min_length' => 'Nama dataset minimal 3 karakter'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug'];
    protected $beforeUpdate   = ['generateSlug'];

    protected $casts = [
        'schema_config' => 'json',
        'total_rows'    => 'integer',
        'total_columns' => 'integer',
        'file_size'     => 'integer'
    ];

    protected function generateSlug(array $data)
    {
        if (isset($data['data']['dataset_name']) && empty($data['data']['dataset_slug'])) {
            $slug = url_title($data['data']['dataset_name'], '-', true);
            
            $existingSlug = $this->where('dataset_slug', $slug)
                                 ->where('application_id', $data['data']['application_id'] ?? null)
                                 ->where('deleted_at', null)
                                 ->first();
            
            if ($existingSlug) {
                $slug = $slug . '-' . uniqid();
            }
            
            $data['data']['dataset_slug'] = $slug;
        }

        return $data;
    }

    public function getByApplication($applicationId, $status = null)
    {
        $builder = $this->where('application_id', $applicationId)
                        ->where('deleted_at', null);
        
        if ($status) {
            $builder->where('upload_status', $status);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function getBySlug($slug, $applicationId)
    {
        return $this->where('dataset_slug', $slug)
                    ->where('application_id', $applicationId)
                    ->where('deleted_at', null)
                    ->first();
    }

    public function getCompleted($applicationId = null)
    {
        $builder = $this->where('upload_status', 'completed')
                        ->where('deleted_at', null);
        
        if ($applicationId) {
            $builder->where('application_id', $applicationId);
        }
        
        return $builder->findAll();
    }

    public function getWithUploader($datasetId = null, $applicationId = null)
    {
        $builder = $this->select('datasets.*, users.nama_lengkap as uploader_name')
                        ->join('users', 'users.id = datasets.uploaded_by')
                        ->where('datasets.deleted_at', null);
        
        if ($datasetId) {
            $builder->where('datasets.id', $datasetId);
            return $builder->first();
        }
        
        if ($applicationId) {
            $builder->where('datasets.application_id', $applicationId);
        }
        
        return $builder->orderBy('datasets.created_at', 'DESC')->findAll();
    }

    public function updateStatus($datasetId, $status, $errorMessage = null)
    {
        $updateData = [
            'upload_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($errorMessage) {
            $updateData['error_message'] = $errorMessage;
        }
        
        return $this->update($datasetId, $updateData);
    }

    public function updateSchema($datasetId, array $schema)
    {
        return $this->update($datasetId, [
            'schema_config' => json_encode($schema),
            'total_columns' => count($schema),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function search($keyword, $applicationId)
    {
        return $this->where('application_id', $applicationId)
                    ->where('deleted_at', null)
                    ->groupStart()
                        ->like('dataset_name', $keyword)
                        ->orLike('description', $keyword)
                    ->groupEnd()
                    ->findAll();
    }

    public function getStats($applicationId)
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                COUNT(*) as total_datasets,
                SUM(CASE WHEN upload_status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN upload_status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN upload_status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(total_rows) as total_data_rows,
                SUM(file_size) as total_file_size
            FROM datasets
            WHERE application_id = ? AND deleted_at IS NULL
        ", [$applicationId]);
        
        return $query->getRowArray();
    }

    public function deleteWithRecords($datasetId)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Soft delete dataset
            $this->update($datasetId, ['deleted_at' => date('Y-m-d H:i:s')]);
            
            // Delete records (hard delete karena data besar)
            $db->table('dataset_records')
               ->where('dataset_id', $datasetId)
               ->delete();
            
            $db->transComplete();
            
            return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }
}