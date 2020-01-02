<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;
use App\Model\UserAdmin;

class ReturnOrder extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_return';

    protected $fillable = [
        'id',
        'store_code',
        'supplier_id',
        'pcc_id',
        'invoice_number',
        'transaction_date',
        'receive_id',
        'description',
        'total',
        'status_id',
        'path',
        'file_name',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id', 'id');
    }

    public function receive()
    {
        return $this->belongsTo(ReceiveOrder::class, 'receive_id', 'id');
    }

    public function user_admin()
    {
        return $this->belongsTo(UserAdmin::class, "created_by", "id");
    }

    public function user_admin_updated()
    {
        return $this->belongsTo(UserAdmin::class, "updated_by", "id");
    }
}
