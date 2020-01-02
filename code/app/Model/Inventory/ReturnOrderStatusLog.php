<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class ReturnOrderStatusLog extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_return_log_status';

    protected $fillable = [
        'return_id',
        'status_old',
        'status_new',
        'description',
        'created_by',
        'updated_by'
    ];

}
