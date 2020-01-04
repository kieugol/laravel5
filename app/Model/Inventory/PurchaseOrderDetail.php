<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class PurchaseOrderDetail extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_purchase_detail';

    protected $fillable = [
        'purchase_id',
        'account_id',
        'material_id',
        'material_detail_id',
        'uom_id',
        'account_id',
        'quantity',
        'is_active',
        'price',
        'total',
        'created_by',
        'updated_by'
    ];

    public function purchase()
    {
        return $this->hasOne(PurchaseOrder::class, 'id', 'purchase_id');
    }
    
    public function material()
    {
        return $this->hasOne(MasterMaterial::class, 'id', 'material_id');
    }
    
    public function material_detail()
    {
        return $this->hasOne(MasterMaterialDetail::class, 'id', 'material_detail_id');
    }
    
    public function account()
    {
        return $this->hasOne(MasterAccount::class, 'id', 'account_id');
    }

    public function uom()
    {
        return $this->hasOne(MasterUom::class, 'id', 'uom_id');
    }
}
