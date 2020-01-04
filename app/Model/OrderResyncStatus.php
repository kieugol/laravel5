<?php

namespace App\Model;

use App\Model\Order;

class OrderResyncStatus extends BaseModel
{
    protected $table = "cc_order_resync_status";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    public function order()
    {
        return $this->hasOne(Order::class, "id", "order_id");
    }
}
