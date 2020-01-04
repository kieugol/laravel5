<?php

namespace App\Model;

class Job extends BaseModel
{
    protected $table = 'jobs';

    protected $fillable = [
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at'
    ];

    public $timestamps = false;
}
