<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:17 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class WastedMaterial extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_wasted_material";

    protected $fillable = [
        'id',
        'material_detail_id',
        'quantity',
        'master_pcc_id',
        'created_by',
        'updated_by'
    ];

    public function material_detail()
    {
        return $this->belongsTo(MasterMaterialDetail::class, 'material_detail_id');
    }

}