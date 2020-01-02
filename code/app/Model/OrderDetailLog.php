<?php

namespace App\Model;

class OrderDetailLog extends BaseModel
{
    protected $table = 'order_detail_log';
    protected $fillable = [
        'order_id',
        'order_detail_id',
        'is_combo',
        'price',
        'sub_price',
        'has_tax',
        'old_quantity',
        'quantity',
        'cooked',
        'status',
        'plucode',
        'category_id',
        'short_name',
        'menu_id',
        'option_menu_id',
        'menu_name',
        'menu_price',
        'variant_id',
        'variant_name',
        'variant_price',
        'addon_id',
        'addon_name',
        'addon_price',
        'cheese_plucode',
        'cheese_price',
        'remark',
        'action',
        'created_by',
        'updated_by'
    ];
}
