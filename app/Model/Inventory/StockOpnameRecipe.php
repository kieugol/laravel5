<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;
use App\Model\UserAdmin;

class StockOpnameRecipe extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_stock_opname_recipe";

    public $timestamps = false;

    protected $fillable = [
        'stock_opname_id',
        'recipe_id',
        'recipe_detail_id',
        'recipe_qty',
        'material_id',
        'material_detail_id',
        'material_detail_qty',
        'total_ending',
        'is_active',
        'created_by',
        'updated_by'
    ];
    
    
    public function material()
    {
        return $this->belongsTo(MasterMaterial::class, 'material_id');
    }
    
    public function material_detail()
    {
        return $this->belongsTo(MasterMaterialDetail::class, 'material_detail_id');
    }
    
    public function user_admin()
    {
        return $this->hasOne(UserAdmin::class, 'id', 'created_by');
    }
    
    public function user_admin_updated()
    {
        return $this->hasOne(UserAdmin::class, 'id', 'updated_by');
    }
}
