<?php

namespace App\Model;


class RecipeConfiguration extends BaseModel
{
    protected $table = 'recipe_configuration';
    
    protected $fillable = ['plucode', 'skucode', 'qty', 'uom_id'];
    
    public function uom()
    {
        return $this->hasOne('App\Model\Uom', "id", "uom_id");
    }
    
    
    public function sku()
    {
        return $this->hasOne('App\Model\Sku', "skucode", "skucode");
    }
}
