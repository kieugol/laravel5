<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:22 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class MasterRecipe extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_recipe";

    protected $fillable = [
        'id',
        'uom_id',
        'code',
        'plucode',
        'sku',
        'name',
        'usage',
        'expired_in',
        'price',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function recipe_detail()
    {
        return $this->hasMany(MasterRecipeDetail::class, 'recipe_id');
    }
    
    public function uom()
    {
        return $this->hasOne(MasterUom::class, 'id', 'uom_id');
    }
    
}
