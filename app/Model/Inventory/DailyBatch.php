<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:17 PM
 */

namespace App\Model\Inventory;


use App\Model\BaseModel;

class DailyBatch extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_daily_batch";

    protected $fillable = [
        'id',
        'recipe_id',
        'master_pcc_id',
        'quantity',
        'is_active',
        'is_daily_batch',
        'created_by',
        'updated_by'
    ];

    public function recipe()
    {
        return $this->belongsTo(MasterRecipe::class, 'recipe_id');
    }
    
    public function master_pcc()
    {
        return $this->belongsTo(MasterPCC::class, 'master_pcc_id');
    }
}
