<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class ViewDailyReceivingSummary extends BaseModel
{
    protected $table = "view_daily_receive_summary";
    public $timestamps = false;

    protected $fillable = [
        'total',
        'account_code',
        'date_time'
    ];

}
