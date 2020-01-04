<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 4:30 PM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;
use App\Model\UserAdmin;

class StockOpname extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_stock_opname";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'store_code',
        'pcc_id',
        'is_sent_ftp',
        'status_id',
        'path',
        'file_name',
        'total_ending_inv',
        'total_current_stock',
        'variance',
        'is_active',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    public function master_pcc() {
        return $this->hasOne(MasterPCC::class, 'id', 'pcc_id');
    }
    
    public function stock_opname_detail() {
        return $this->hasMany(StockOpnameDetail::class, 'stock_opname_id');
    }
    
    public function stock_opname_summary() {
        return $this->hasMany(StockOpnameSummary::class, 'stock_opname_id');
    }
    
    public function user_admin()
    {
        return $this->hasOne(UserAdmin::class, 'id', 'created_by');
    }
    
    public function user_admin_updated()
    {
        return $this->hasOne(UserAdmin::class, 'id', 'updated_by');
    }
}
