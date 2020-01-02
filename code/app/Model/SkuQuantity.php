<?php

namespace App\Model;


class SkuQuantity extends BaseModel {

    protected $table = 'sku_quantity';
//    protected $primaryKey = "skucode"

    public $timestamps = false;

    public function sku()
    {
        return $this->hasOne('App\Model\Sku', "skucode", "skucode");
    }
}
