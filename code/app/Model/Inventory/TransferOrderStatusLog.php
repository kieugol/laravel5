<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class TransferOrderStatusLog extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_transfer_log_status';

    protected $fillable = [
        'transfer_id',
        'status_old',
        'status_new',
        'description',
        'created_by',
        'updated_by'
    ];

}
