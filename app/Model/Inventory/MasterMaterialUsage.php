<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 4:50 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class MasterMaterialUsage extends BaseModel
{

    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_material_usage";

    protected $fillable = [
        'order_id',
        'master_pcc_id',
        'material_id',
        'usage',
        'weight',
        'price',
        'total',
        'is_recipe',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
