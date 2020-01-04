<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class MasterSupplier extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_supplier";

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'fax',
        'terms_of_payment',
        'is_active',
        'is_import_do',
        'created_by',
        'updated_by'
    ];
}
