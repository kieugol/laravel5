<?php

namespace App\Model;

class AdsMonitor extends BaseModel
{
    protected $table = 'ads_monitor';
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    
    protected $fillable = [
        'code',
        'base_url',
        'filename',
        'is_actived',
    ];
}
