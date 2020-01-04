<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:17 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;
use App\Model\UserAdmin;

class RecipeLog extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_recipe_log";

    protected $fillable = [
        'id',
        'recipe_id',
        'material_from_id',
        'material_to_id',
        'other_recipe_from_id',
        'other_recipe_to_id',
        'usage_from',
        'usage_to',
        'created_date',
        'updated_date',
        'created_by',
        'updated_by'
    ];

    public function recipe()
    {
        return $this->belongsTo(MasterRecipe::class, 'recipe_id');
    }
    
    public function material_from()
    {
        return $this->belongsTo(MasterMaterial::class, 'material_from_id');
    }
    
    public function material_to()
    {
        return $this->belongsTo(MasterMaterial::class, 'material_to_id');
    }
    
    public function other_recipe_from()
    {
        return $this->belongsTo(MasterRecipe::class, 'other_recipe_from_id');
    }
    
    public function other_recipe_to()
    {
        return $this->belongsTo(MasterRecipe::class, 'other_recipe_to_id');
    }
    
    public function user_created()
    {
        return $this->belongsTo(UserAdmin::class, 'created_by');
    }

}
