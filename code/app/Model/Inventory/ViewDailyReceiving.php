<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class ViewDailyReceiving extends BaseModel
{
    protected $table = "view_daily_receive";
    public $timestamps = false;

    protected $fillable = [
        'supplier_code',
        'supplier_name',
        'account_code',
        'invoice_number',
        'total',
        'type_transaction',
        'date_time'
    ];

}
