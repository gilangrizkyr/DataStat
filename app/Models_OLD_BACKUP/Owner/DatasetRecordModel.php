<?php

/**
 * ============================================================================
 * DATASET RECORD MODEL
 * ============================================================================
 * 
 * Path: app/Models/Owner/DatasetRecordModel.php
 * 
 * Deskripsi:
 * Model untuk tabel dataset_records (data aktual dari Excel).
 * Menyimpan setiap baris data sebagai JSON.
 * 
 * Table: dataset_records
 * Primary Key: id
 * 
 * Fields:
 * - id, dataset_id, row_num, data_json, created_at
 * 
 * Features:
 * - JSON storage untuk flexibility
 * - Batch insert support
 * - Query optimization
 * 
 * Used by: Owner (via Libraries)
 * ============================================================================
 */

namespace App\Models\Owner;

use CodeIgniter\Model;

class DatasetRecordModel extends Model
{
    protected $table            = 'dataset_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'dataset_id',
        'row_num',
        'data_json',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    protected $validationRules = [
        'dataset_id' => 'required|integer',
        'row_num'    => 'required|integer',
        'data_json'  => 'required'
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $casts = [
        'data_json' => 'json',
        'row_num'   => 'integer'
    ];

    public function getByDataset($datasetId, $limit = null, $offset = null)
    {
        $builder = $this->where('dataset_id', $datasetId)
                        ->orderBy('row_num', 'ASC');
        
        if ($limit) {
            $builder->limit($limit, $offset ?? 0);
        }
        
        return $builder->findAll();
    }

    public function getRowCount($datasetId)
    {
        return $this->where('dataset_id', $datasetId)->countAllResults();
    }

    public function deleteByDataset($datasetId)
    {
        return $this->where('dataset_id', $datasetId)->delete();
    }

    public function getFieldValues($datasetId, $fieldName, $distinct = false)
    {
        $db = \Config\Database::connect();
        
        $distinctClause = $distinct ? 'DISTINCT' : '';
        
        $query = $db->query("
            SELECT {$distinctClause} JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.{$fieldName}')) as value
            FROM dataset_records
            WHERE dataset_id = ?
            AND JSON_EXTRACT(data_json, '$.{$fieldName}') IS NOT NULL
        ", [$datasetId]);
        
        return array_column($query->getResultArray(), 'value');
    }

    public function searchRecords($datasetId, $fieldName, $keyword)
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT *
            FROM dataset_records
            WHERE dataset_id = ?
            AND JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.{$fieldName}')) LIKE ?
            ORDER BY row_num ASC
        ", [$datasetId, "%{$keyword}%"]);
        
        return $query->getResultArray();
    }
}