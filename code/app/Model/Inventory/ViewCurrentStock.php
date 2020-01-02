<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 2/19/2019
 * Time: 11:20 AM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;

class ViewCurrentStock extends BaseModel
{
    protected $table = "view_curent_stock";
    public $timestamps = false;

    protected $fillable = [
        'material_code',
        'material_name',
        'quantity_recipe',
        'recipe_uom',
        'quantity_outlet',
        'outlet_uom'
    ];
}