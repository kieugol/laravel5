<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/24/2019
 * Time: 1:31 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class MasterMaterialDetail extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_material_detail";

    protected $fillable = [
        'id',
        'code',
        'name',
        'material_id',
        'supplier_id',
        'supplier_uom_id',
        'outlet_uom_id',
        'outlet_uom_detail_id',
        'recipe_uom_id',
        'recipe_uom_detail_id',
        'smaller_uom_id',
        'smaller_uom_detail_id',
        'report_uom_id',
        'has_transaction',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function material()
    {
        return $this->belongsTo(MasterMaterial::class, 'material_id');
    }

    public function outlet_uom()
    {
        return $this->belongsTo(MasterUom::class, 'outlet_uom_id');
    }

    public function outlet_uom_detail()
    {
        return $this->belongsTo(MasterUomDetail::class, 'outlet_uom_detail_id');
    }

    public function supplier_uom()
    {
        return $this->belongsTo(MasterUom::class, 'supplier_uom_id');
    }

    public function smaller_uom_detail()
    {
        return $this->belongsTo(MasterUomDetail::class, 'smaller_uom_detail_id');
    }

    public function recipe_uom_detail()
    {
        return $this->belongsTo(MasterUomDetail::class, 'recipe_uom_detail_id');
    }
    
    public function report_uom()
    {
        return $this->belongsTo(MasterUom::class, 'report_uom_id');
    }
}
