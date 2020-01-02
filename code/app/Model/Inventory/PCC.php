<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class PCC extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_pcc";

    protected $fillable = [
        'id',
        'master_pcc_id',
        'material_detail_id',
        'begining_quantity',
        'begining_price',
        'begining_total',
        'total_available',
        'end_of_inventory',
        'usage',
        'usage_sales_mix',
        'price',
        'actual_cost_of_sales',
        'potential_cost_of_sales',
        'total_ending',
        'price_variance',
        'total_cost_variance',
        'percentage_variance',
        'created_date',
        'updated_date',
        'created_by',
        'updated_by'
    ];
}
