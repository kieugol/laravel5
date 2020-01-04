<?php

namespace App\Model;

class UserDriver extends BaseModel
{
    protected $table = 'user_driver';
    
     public $timestamps = false;
     
    protected $fillable = [
        'id',
        'user_id',
        'status',
        'code',
        'lasttime_delivered',
        'is_active',
    ];
    
    public function user()
    {
        return $this->hasOne('App\Model\User', "id", "user_id");
    }
}
