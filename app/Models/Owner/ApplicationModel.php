<?php

namespace App\Models\Owner;

use CodeIgniter\Model;

class ApplicationModel extends Model
{
    protected $table            = 'applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields = [
        'owner_id',
        'app_name',
        'app_description',
        'bidang',
        'settings',
        'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Get all with owner
     */
    public function getAllWithOwner()
    {
        return $this->select('applications.*, users.nama_lengkap as owner_name, users.email as owner_email')
                    ->join('users', 'users.id = applications.owner_id')
                    ->findAll();
    }

    /**
     * Get with owner
     */
    public function getWithOwner($id)
    {
        return $this->select('applications.*, users.nama_lengkap as owner_name, users.email as owner_email')
                    ->join('users', 'users.id = applications.owner_id')
                    ->where('applications.id', $id)
                    ->first();
    }

    /**
     * Toggle active
     */
    public function toggleActive($id)
    {
        $app = $this->find($id);
        if (!$app) return false;

        return $this->update($id, [
            'is_active' => !$app['is_active']
        ]);
    }
}