<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;
use App\Model\UserAdmin;

class ReceiveOrder extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_receive';

    protected $fillable = [
        'id',
        'store_code',
        'supplier_id',
        'purchase_id',
        'pcc_id',
        'invoice_number',
        'transaction_date',
        'description',
        'total',
        'status_id',
        'path',
        'file_name',
        'is_active',
        'is_returnable',
        'created_by',
        'updated_by'
    ];

    public function supplier()
    {
        return $this->hasOne(MasterSupplier::class, 'id', 'supplier_id');
    }

    public function purchase()
    {
        return $this->hasOne(PurchaseOrder::class, 'id', 'purchase_id');
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
