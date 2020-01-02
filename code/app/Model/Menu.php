<?php

namespace App\Model;


class Menu extends BaseModel
{
    protected $table = 'menu';
    
//    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri'];
    
//    public function uom()
//    {
//        return $this->hasOne('App\Uom', "id", "uom_id");
//    }
    public function category()
    {
        return $this->hasOne('App\Model\MenuCategory', "id", "category_id");
    }
}
