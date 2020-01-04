<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:22 PM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;

class MasterRecipeDetail extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_recipe_detail";

    protected $fillable = [
        'recipe_id',
        'material_id',
        'other_recipe_id',
        'weight',
        'price',
        'usage',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function material()
    {
        return $this->belongsTo(MasterMaterial::class, 'material_id');
    }

    public function other_recipe()
    {
        return $this->belongsTo(MasterRecipe::class, 'other_recipe_id');
    }
}
