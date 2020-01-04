<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;
use App\Model\UserAdmin;

class StockOpnameSummary extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_stock_opname_summary";

    public $timestamps = false;

    protected $fillable = [
        'stock_opname_id',
        'material_detail_id',
        'ending_inv',
        'quantity_material',
        'quantity_recipe',
        'is_active',
        'created_by',
        'updated_by'
    ];
    
    public function material_detail()
    {
        return $this->belongsTo(MasterMaterialDetail::class, 'material_detail_id');
    }
}
