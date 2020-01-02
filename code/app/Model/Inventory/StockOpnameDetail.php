<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/22/2019
 * Time: 2:32 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class StockOpnameDetail extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_stock_opname_detail";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'stock_opname_id',
        'material_detail_id',
        'quantity_supplier_uom',
        'quantity_supplier_uom',
        'quantity_convert',
        'unit_id',
        'location_id',
        'is_active',
        'created_by',
        'updated_by'
    ];
    
    public function material_detail()
    {
        return $this->belongsTo(MasterMaterialDetail::class, 'material_detail_id');
    }
    
    public function unit()
    {
        return $this->belongsTo(MasterUom::class, 'unit_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
