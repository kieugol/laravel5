<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class SyncMaster extends BaseModel
{

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = "inventory_sync_master";

    protected $fillable = [
        'id',
        'version',
        'file_name',
        'is_sync'
    ];
}
