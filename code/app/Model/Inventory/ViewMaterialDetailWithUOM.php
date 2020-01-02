<?php

namespace App\Model\Inventory;

use App\Model\BaseModel;

class ViewMaterialDetailWithUOM extends BaseModel
{
    protected $table = "view_material_detail_with_uom";
    public $timestamps = false;

    protected $fillable = [
        'material_id',
        'material_code',
        'material_detail_id',
        'material_detail_code',
        'report_unit',
        'supplier_unit',
        'smaller_unit',
        'outlet_unit',
        'conversion_supplier_to_recipe',
        'conversion_smaller_to_recipe',
        'conversion_outlet_to_recipe',
    ];

}
