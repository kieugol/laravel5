<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class MasterAccount extends BaseModel
{

    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_account";

    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
