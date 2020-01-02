<?php

namespace App\Model;


class MenuAutoCook extends BaseModel
{
    protected $table = 'menu_auto_cook';
    
//    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri'];

    public function menu()
    {
        return $this->hasOne('App\Model\Menu', "id", "menu_id");
    }
}
