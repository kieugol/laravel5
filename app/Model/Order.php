<?php

namespace App\Model;

class Order extends BaseModel
{
    protected $table = 'order';
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
 
    public function details() {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function order_details() {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function order_payments() {
        return $this->hasMany(OrderPayment::class, 'order_id');
    }

    public function order_type() {
        return $this->belongsTo(OrderType::class, 'order_type_id');
    }

    public function order_status() {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function customer() {
    return $this->hasOne(Customer::class, "id", "customer_id");
}

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user_encash() {
        return $this->belongsTo(User::class, 'encash_by');
    }
    public function order()
    {
       return $this->hasOne(Order::class, "id", "order_id");
    }
}
