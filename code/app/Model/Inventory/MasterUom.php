<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 5:19 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class MasterUom extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_uom";

    protected $fillable = [
        'id',
        'code',
        'name',
        'is_active',
        'created_by',
        'updated_by'
    ];
}