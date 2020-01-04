<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class ReturnOrderDetail extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_return_detail';

    protected $fillable = [
        'id',
        'return_id',
        'material_id',
        'material_detail_id',
        'uom_id',
        'account_id',
        'quantity',
        'price',
        'total',
        'is_active',
        'created_by',
        'updated_by'
    ];

}
