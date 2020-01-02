<?php

namespace App\Model;

class AdsMenu extends BaseModel
{
    protected $table = 'ads_menu';
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    
//     public $timestamps = false;
    
    protected $fillable = [
        'code',
        'base_url',
        'filename',
        'is_actived',
    ];
}
