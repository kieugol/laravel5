<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:22 PM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;

class MasterRecipeSku extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_recipe_sku";

    protected $fillable = [
        'id',
        'sku',
        'recipe_id',
        'is_active',
        'created_by',
        'updated_by'
    ];

}
