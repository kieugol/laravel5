<?php

namespace App\Model;


class Sku extends BaseModel
{
    protected $table = 'sku';
    
    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri'];
    
    public function uom()
    {
        return $this->hasOne('App\Model\Uom', "id", "uom_id");
    }
}
