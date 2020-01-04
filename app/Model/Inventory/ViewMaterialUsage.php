<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 2/19/2019
 * Time: 8:34 AM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;

class ViewMaterialUsage extends BaseModel
{
    protected $table = "view_material_usage";
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'sku',
        'recipe_id',
        'code',
        'name',
        'usage',
        'unit',
        'price',
        'total',
        'order_date'
    ];

}
