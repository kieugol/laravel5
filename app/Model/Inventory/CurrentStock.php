<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class CurrentStock extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_current_stock_header';

    protected $fillable = [
        'material_id',
        'uom_id',
        'quantity_recipe',
        'quantity_store',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function material()
    {
        return $this->belongsTo(MasterMaterial::class, 'material_id', 'id');
    }
    
    public function net_weight_uom()
    {
        return $this->belongsTo(MasterUom::class, 'uom_id', 'id');
    }
}
