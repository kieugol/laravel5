<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class MasterPCC extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_pcc";

    protected $fillable = [
        'id',
        'code',
        'name',
        'period',
        'month',
        'year',
        'from_date',
        'to_date',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
