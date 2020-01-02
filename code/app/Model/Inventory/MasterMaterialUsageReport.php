<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 4:50 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class MasterMaterialUsageReport extends BaseModel
{

    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_material_usage_report";

    protected $fillable = [
        'order_id',
        'master_pcc_id',
        'sku',
        'header_recipe_id',
        'recipe_id',
        'product_id',
        'product_code',
        'usage',
        'weight',
        'uom_id',
        'price',
        'total',
        'is_active',
        'is_recipe',
        'is_dailybatch',
        'created_by',
        'updated_by'
    ];
}
