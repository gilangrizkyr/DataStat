<?php

/**
 * ============================================================================
 * DASHBOARD WIDGET MODEL
 * ============================================================================
 * 
 * Path: app/Models/Owner/DashboardWidgetModel.php
 * 
 * Deskripsi:
 * Model untuk mengelola widgets di dashboard.
 * Setiap widget menampilkan satu statistik dengan positioning dan config.
 * 
 * Table: dashboard_widgets
 * 
 * Fields:
 * - id (PK)
 * - dashboard_id (FK)
 * - statistic_config_id (FK)
 * - widget_title - Judul widget (optional override)
 * - position_x, position_y - Posisi di grid
 * - width, height - Ukuran widget
 * - sort_order - Urutan tampil
 * - widget_config (JSON) - Config tambahan
 * - is_visible - Tampil atau tidak
 * - created_at, updated_at
 * 
 * Relations:
 * - belongsTo: dashboards, statistic_configs
 * 
 * Used by: Owner, Viewer
 * ============================================================================
 */

namespace App\Models\Owner;

use CodeIgniter\Model;

class DashboardWidgetModel extends Model
{
    protected $table            = 'dashboard_widgets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'dashboard_id',
        'statistic_config_id',
        'widget_title',
        'position_x',
        'position_y',
        'width',
        'height',
        'sort_order',
        'widget_config',
        'is_visible',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'dashboard_id'         => 'required|integer',
        'statistic_config_id'  => 'required|integer',
        'width'                => 'permit_empty|integer|greater_than[0]|less_than_equal_to[12]',
        'height'               => 'permit_empty|integer|greater_than[0]',
        'sort_order'           => 'permit_empty|integer',
        'is_visible'           => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'dashboard_id' => [
            'required' => 'Dashboard ID harus diisi'
        ],
        'statistic_config_id' => [
            'required' => 'Statistic Config ID harus diisi'
        ],
        'width' => [
            'less_than_equal_to' => 'Width maksimal 12 (grid system)'
        ]
    ];

    /**
     * Get widgets dengan statistic info
     */
    public function getWithStatistic($dashboardId)
    {
        return $this->select('dashboard_widgets.*, 
                             statistic_configs.stat_name, 
                             statistic_configs.visualization_type,
                             statistic_configs.cached_result,
                             datasets.dataset_name')
                    ->join('statistic_configs', 'statistic_configs.id = dashboard_widgets.statistic_config_id')
                    ->join('datasets', 'datasets.id = statistic_configs.dataset_id')
                    ->where('dashboard_widgets.dashboard_id', $dashboardId)
                    ->orderBy('dashboard_widgets.sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Get visible widgets only
     */
    public function getVisible($dashboardId)
    {
        return $this->where('dashboard_id', $dashboardId)
                    ->where('is_visible', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Update widget position & size
     */
    public function updatePosition($widgetId, $data)
    {
        $allowedFields = ['position_x', 'position_y', 'width', 'height', 'sort_order'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            return $this->update($widgetId, $updateData);
        }
        
        return false;
    }

    /**
     * Batch update positions (untuk drag & drop)
     */
    public function batchUpdatePositions($widgets)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($widgets as $widget) {
            if (isset($widget['id'])) {
                $this->updatePosition($widget['id'], $widget);
            }
        }

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Reorder widgets
     */
    public function reorder($dashboardId, $widgetIds)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($widgetIds as $index => $widgetId) {
            $this->update($widgetId, [
                'sort_order' => $index,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Toggle visibility
     */
    public function toggleVisibility($widgetId)
    {
        $widget = $this->find($widgetId);
        if (!$widget) return false;

        $newStatus = $widget['is_visible'] == 1 ? 0 : 1;
        return $this->update($widgetId, ['is_visible' => $newStatus]);
    }

    /**
     * Duplicate widget
     */
    public function duplicate($widgetId)
    {
        $widget = $this->find($widgetId);
        if (!$widget) return false;

        unset($widget['id']);
        $widget['widget_title'] = $widget['widget_title'] . ' (Copy)';
        $widget['sort_order'] = ($widget['sort_order'] ?? 0) + 1;
        $widget['created_at'] = date('Y-m-d H:i:s');

        return $this->insert($widget);
    }

    /**
     * Get widget count per dashboard
     */
    public function getCountByDashboard($dashboardId)
    {
        return $this->where('dashboard_id', $dashboardId)->countAllResults();
    }

    /**
     * Check if statistic sudah ada di dashboard
     */
    public function isStatisticExists($dashboardId, $statisticConfigId)
    {
        return $this->where('dashboard_id', $dashboardId)
                    ->where('statistic_config_id', $statisticConfigId)
                    ->countAllResults() > 0;
    }
}