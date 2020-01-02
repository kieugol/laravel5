<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 2/12/2019
 * Time: 1:35 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class ReceiveOrderDetail extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = 'inventory_receive_detail';

    protected $fillable = [
        'receive_id',
        'material_id',
        'material_detail_id',
        'uom_id',
        'account_id',
        'price_in_outlet',
        'quantity_in_outlet',
        'price_in_recipe',
        'quantity_in_recipe',
        'avg_price',
        'quantity',
        'price',
        'total',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function material_detail()
    {
        return $this->hasOne(MasterMaterialDetail::class, 'id', 'material_detail_id');
    }

    public function account()
    {
        return $this->hasOne(MasterAccount::class, 'id', 'account_id');
    }
}