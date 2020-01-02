<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/24/2019
 * Time: 1:31 PM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;

class MasterMaterialDetailSupplier extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_material_detail_supplier";

    protected $fillable = [
        'material_detail_id',
        'supplier_id',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function material_detail()
    {
        return $this->belongsTo(MasterMaterialDetail::class, 'material_detail_id');
    }

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

}