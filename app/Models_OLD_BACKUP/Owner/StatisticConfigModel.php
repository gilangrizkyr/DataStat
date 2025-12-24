<?php

/**
 * ============================================================================
 * STATISTIC CONFIG MODEL
 * ============================================================================
 * 
 * Path: app/Models/Owner/StatisticConfigModel.php
 * 
 * Deskripsi:
 * Model untuk tabel statistic_configs (konfigurasi statistik).
 * Menyimpan semua config untuk menghitung statistik dari dataset.
 * 
 * Table: statistic_configs
 * Primary Key: id
 * 
 * Fields:
 * - id, application_id, dataset_id, stat_name, stat_slug
 * - description, metric_type, target_field, group_by_fields (JSON)
 * - filters (JSON), custom_formula, calculation_config (JSON)
 * - visualization_type, visualization_config (JSON)
 * - sort_by, sort_order, limit_rows
 * - cached_result (JSON), last_calculated
 * - is_active, created_by, created_at, updated_at, deleted_at
 * 
 * Features:
 * - Soft delete support
 * - Multiple JSON fields
 * - Cache support
 * - Complex calculations
 * 
 * Used by: Owner, Viewer
 * ============================================================================
 */

namespace App\Models\Owner;

use CodeIgniter\Model;

class StatisticConfigModel extends Model
{
    protected $table            = 'statistic_configs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'application_id',
        'dataset_id',
        'stat_name',
        'stat_slug',
        'description',
        'metric_type',
        'target_field',
        'group_by_fields',
        'filters',
        'custom_formula',
        'calculation_config',
        'visualization_type',
        'visualization_config',
        'sort_by',
        'sort_order',
        'limit_rows',
        'cached_result',
        'last_calculated',
        'is_active',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'application_id'     => 'required|integer',
        'dataset_id'         => 'required|integer',
        'stat_name'          => 'required|min_length[3]|max_length[255]',
        'metric_type'        => 'required|in_list[count,sum,average,min,max,percentage,ratio,growth,ranking,custom_formula]',
        'visualization_type' => 'required|in_list[table,bar_chart,pie_chart,line_chart,area_chart,kpi_card,progress_bar,donut_chart,scatter_chart]'
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug'];
    protected $beforeUpdate   = ['generateSlug'];

    protected $casts = [
        'group_by_fields'       => 'json',
        'filters'               => 'json',
        'calculation_config'    => 'json',
        'visualization_config'  => 'json',
        'cached_result'         => 'json',
        'is_active'             => 'boolean'
    ];

    protected function generateSlug(array $data)
    {
        if (isset($data['data']['stat_name']) && empty($data['data']['stat_slug'])) {
            $slug = url_title($data['data']['stat_name'], '-', true);
            
            $existingSlug = $this->where('stat_slug', $slug)
                                 ->where('application_id', $data['data']['application_id'] ?? null)
                                 ->where('deleted_at', null)
                                 ->first();
            
            if ($existingSlug) {
                $slug = $slug . '-' . uniqid();
            }
            
            $data['data']['stat_slug'] = $slug;
        }

        return $data;
    }

    public function getByApplication($applicationId, $activeOnly = false)
    {
        $builder = $this->where('application_id', $applicationId)
                        ->where('deleted_at', null);
        
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function getByDataset($datasetId, $activeOnly = false)
    {
        $builder = $this->where('dataset_id', $datasetId)
                        ->where('deleted_at', null);
        
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }
        
        return $builder->findAll();
    }

    public function getBySlug($slug, $applicationId)
    {
        return $this->where('stat_slug', $slug)
                    ->where('application_id', $applicationId)
                    ->where('deleted_at', null)
                    ->first();
    }

    public function getWithDataset($statisticId = null, $applicationId = null)
    {
        $builder = $this->select('statistic_configs.*, datasets.dataset_name, datasets.schema_config')
                        ->join('datasets', 'datasets.id = statistic_configs.dataset_id')
                        ->where('statistic_configs.deleted_at', null);
        
        if ($statisticId) {
            $builder->where('statistic_configs.id', $statisticId);
            return $builder->first();
        }
        
        if ($applicationId) {
            $builder->where('statistic_configs.application_id', $applicationId);
        }
        
        return $builder->findAll();
    }

    public function updateCache($statisticId, $result)
    {
        return $this->update($statisticId, [
            'cached_result' => json_encode($result),
            'last_calculated' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function clearCache($statisticId)
    {
        return $this->update($statisticId, [
            'cached_result' => null,
            'last_calculated' => null
        ]);
    }

    public function toggleActive($statisticId)
    {
        $stat = $this->find($statisticId);
        
        if (!$stat) {
            return false;
        }
        
        $newStatus = $stat['is_active'] == 1 ? 0 : 1;
        
        return $this->update($statisticId, [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function search($keyword, $applicationId)
    {
        return $this->select('statistic_configs.*, datasets.dataset_name')
                    ->join('datasets', 'datasets.id = statistic_configs.dataset_id')
                    ->where('statistic_configs.application_id', $applicationId)
                    ->where('statistic_configs.deleted_at', null)
                    ->groupStart()
                        ->like('statistic_configs.stat_name', $keyword)
                        ->orLike('statistic_configs.description', $keyword)
                    ->groupEnd()
                    ->findAll();
    }

    public function getByMetricType($metricType, $applicationId)
    {
        return $this->where('metric_type', $metricType)
                    ->where('application_id', $applicationId)
                    ->where('deleted_at', null)
                    ->findAll();
    }
}