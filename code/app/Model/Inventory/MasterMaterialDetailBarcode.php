<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 4:50 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class MasterMaterialDetailBarcode extends BaseModel
{

    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_material_detail_barcode";

    protected $fillable = [
        'id',
        'material_detail_id',
        'barcode',
        'is_active',
        'created_by',
        'updated_by'
    ];

}
