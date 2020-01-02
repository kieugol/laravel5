<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/21/2019
 * Time: 4:50 PM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;

class MasterType extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_master_type";

    protected $fillable = [
        'id',
        'group_id',
        'account_id',
        'code',
        'name',
        'description',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function account()
    {
        return $this->belongsTo(MasterAccount::class, 'account_id');
    }
}
