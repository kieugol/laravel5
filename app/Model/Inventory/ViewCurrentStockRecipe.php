<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 2/19/2019
 * Time: 11:20 AM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;

class ViewCurrentStockRecipe extends BaseModel
{
    protected $table = "view_current_stock_recipe";
    public $timestamps = false;

    protected $fillable = [
        'id',
        'recipe_id',
        'recipe_code',
        'name',
        'quantity',
        'is_active'
    ];
}