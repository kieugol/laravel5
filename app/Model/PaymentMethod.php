<?php

namespace App\Model;

class PaymentMethod extends BaseModel
{
    protected $table = 'payment_method';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

}
