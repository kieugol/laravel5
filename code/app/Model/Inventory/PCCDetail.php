<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class PCCDetail extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_pcc_detail";

    protected $fillable = [
        'id',
        'master_pcc_id',
        'material_detail_id',
        'day_date',
        'transaction_type',
        'quantity_in_outlet',
        'price_in_outlet',
        'total',
        'created_date',
        'updated_date',
        'created_by',
        'updated_by'
    ];
}
