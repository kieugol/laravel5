<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 5:19 PM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;

class MasterUomDetail extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_uom_detail";

    protected $fillable = [
        'name',
        'from_uom_id',
        'from_uom_qty',
        'to_uom_id',
        'to_uom_qty',
        'conversion_rate',
        'is_active',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];
}
