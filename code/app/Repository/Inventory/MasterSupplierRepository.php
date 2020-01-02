<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\MasterSupplier;
use App\Repository\BaseRepository;

class MasterSupplierRepository extends BaseRepository
{

    public function __construct(MasterSupplier $model)
    {
        parent::__construct($model);
    }
    
    public function getAll()
    {
        return $this->model
            ->select([
                MasterSupplier::getColumnName('id'),
                MasterSupplier::getColumnName('code'),
                MasterSupplier::getColumnName('name')
            ])
            ->where(MasterSupplier::getColumnName('is_active'), STATUS_ACTIVE)
            ->get();
    }

    public function getByCode($code)
    {
        return $this->model
            ->select('id')
            ->where('code', $code)
            ->first();
    }
}
