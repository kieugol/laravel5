<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class TransferOrderDetail extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_transfer_detail';

    protected $fillable = [
        'transfer_id',
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
    
    public function transfer()
    {
        return $this->hasOne(TransferOrder::class, 'id', 'transfer_id');
    }
    
    public function account()
    {
        return $this->hasOne(MasterAccount::class, 'id', 'account_id');
    }
}
