<?php

namespace App\Model;


class OrderType extends BaseModel
{
    protected $table = "order_type";
    
    public $timestamps = false;
    public $incrementing = false;
    public $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];
//
//    public function order(){
//        return $this->hasMany(Order::class,'order_id', 'id');
//    }
}
