<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class MasterGroup extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_group";

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
