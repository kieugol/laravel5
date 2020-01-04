<?php

namespace App\Model;

class User extends BaseModel
{
    protected $table = 'user';
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    
    protected $fillable = [
        'username',
        'name',
        'code',
        'email',
        'salt',
        'avatar',
        'phone',
        'gender',
        'role_id',
        'password',
        'token',
        'birthday',
        'is_default_login',
        'is_login',
        'is_active',
    ];
    
    public function role()
    {
        return $this->hasOne('App\Model\Role', "id", "role_id");
    }
}
