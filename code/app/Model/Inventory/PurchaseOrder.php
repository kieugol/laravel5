<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;
use App\Model\UserAdmin;

class PurchaseOrder extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_purchase';

    protected $fillable = [
        'code',
        'store_code',
        'supplier_id',
        'account_id',
        'pcc_id',
        'path',
        'file_name',
        'confirmed_date',
        'delivery_date',
        'description',
        'quantity',
        'total',
        'status_id',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function supplier()
    {
        return $this->hasOne(MasterSupplier::class, 'id', 'supplier_id');
    }
    
    public function account()
    {
        return $this->hasOne(MasterAccount::class, 'id', 'account_id');
    }
    
    public function receive()
    {
        return $this->hasOne(ReceiveOrder::class, 'purchase_id');
    }
    
    public function purchase_detail()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_id');
    }
    
    public function user_admin()
    {
        return $this->hasOne(UserAdmin::class, 'id', 'created_by');
    }

    public function user_admin_updated()
    {
        return $this->hasOne(UserAdmin::class, 'id', 'updated_by');
    }

    public function master_pcc()
    {
        return $this->hasOne(MasterPCC::class, 'id', 'pcc_id');
    }
}
