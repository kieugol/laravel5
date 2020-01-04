<?php

namespace App\Model;

class OrderDetail extends BaseModel {

    protected $table = "order_detail";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function children() {
        return $this->hasMany('App\Model\OrderDetail', 'order_detail_id');
    }

}
