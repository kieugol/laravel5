<?php

namespace App\Model;


class SyncOrderDelivery extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'cc_sync_order_delivery';

    protected $fillable = [
        'order_id',
        'order_code',
        'is_sync',
        'number_of_retry',
        'description',
        'is_active',
        'created_by',
        'updated_by'
    ];

}