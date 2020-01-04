<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;
use App\Model\Outlet;
use App\Model\UserAdmin;

class TransferOrder extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_transfer';

    protected $fillable = [
        'id',
        'store_code',
        'type',
        'from_outlet_id',
        'to_outlet_id',
        'pcc_id',
        'invoice_number',
        'transaction_date',
        'total',
        'status_id',
        'path',
        'file_name',
        'path_cosyst',
        'file_name_cosyst',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function from_outlet()
    {
        return $this->belongsTo(Outlet::class, 'from_outlet_id', 'id');
    }

    public function to_outlet()
    {
        return $this->belongsTo(Outlet::class, 'to_outlet_id', 'id');
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
