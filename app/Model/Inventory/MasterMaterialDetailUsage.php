<?php

namespace App\Model\Inventory;


use App\Model\BaseModel;
use App\Model\Order;

class MasterMaterialDetailUsage extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_material_detail_usage";

    protected $fillable = [
        'order_id',
        'material_detail_id',
        'usage',
        'price',
        'total',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function material_detail()
    {
        return $this->belongsTo(MasterMaterialDetail::class, 'material_detail_id');
    }

}
