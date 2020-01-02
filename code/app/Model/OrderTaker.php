<?php

namespace App\Model;

class OrderTaker extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'app_order_taker';
    protected $fillable = [
        'version',
        'order_taker_code',
        'base_url',
        'file_name',
        'description',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
