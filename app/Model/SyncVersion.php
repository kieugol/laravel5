<?php

namespace App\Model;


class SyncVersion extends BaseModel
{
    protected $table = 'sync_version';

    protected $fillable = ['id', 'menu', 'file_name', 'is_sync'];
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
