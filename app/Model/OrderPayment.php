<?php

namespace App\Model;

class OrderPayment extends BaseModel
{
    protected $table = "order_payment";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

}
