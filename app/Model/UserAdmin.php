<?php

namespace App\Model;


class UserAdmin extends BaseModel
{
    protected $table = 'admin_users';
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}
