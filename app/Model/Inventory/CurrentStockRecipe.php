<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class CurrentStockRecipe extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_current_stock_recipe';

    protected $fillable = [
        'recipe_id',
        'recipe_code',
        'quantity',
        'is_active',
        'created_by',
        'updated_by'
    ];

}
