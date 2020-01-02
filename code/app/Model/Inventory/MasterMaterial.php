<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 4:50 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class MasterMaterial extends BaseModel
{

    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_material";

    protected $fillable = [
        'id',
        'group_id',
        'type_id',
        'account_id',
        'genstore_id',
        'code',
        'name',
        'recipe_rate',
        'recipe_rate_uom_id',
        'description',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function account()
    {
        return $this->belongsTo(MasterAccount::class, 'account_id');
    }
    
    public function material_detail()
    {
        return $this->hasMany(MasterMaterialDetail::class,'material_id');
    }
    
    public function recipe_uom()
    {
        return $this->belongsTo(MasterUom::class, 'recipe_rate_uom_id');
    }
    
    public function current_stock()
    {
        return $this->hasOne(CurrentStock::class, 'material_id');
    }
}
