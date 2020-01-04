<?php

namespace App\Model;


class Config extends BaseModel
{
    protected $table = 'config';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'type',
        'key',
        'name',
        'value',
        'is_active'
    ];
}
