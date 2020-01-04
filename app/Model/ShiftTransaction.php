<?php

namespace App\Model;

class ShiftTransaction extends BaseModel
{
    protected $table = 'shift_transaction';
    
//    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri'];
//
    public function user()
    {
        return $this->hasOne('App\Model\User', "id", "user_id");
    }
}
