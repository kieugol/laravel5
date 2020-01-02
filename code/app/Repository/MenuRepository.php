<?php

namespace App\Repository;

use App\Model\Menu;

class MenuRepository extends BaseRepository {

    public function __construct(Menu $model) {
        parent::__construct($model);
    }

    public function getListMenuForAutoCook(){
        $list = $this->getModel()
                ->whereRaw('id NOT IN(select menu_id FROM menu_auto_cook)')
                ->where('is_active', 1)
                ->orderBy("name", "desc")->get();
        $arr = array();
        foreach($list as $menu){
            $arr[$menu->id] = "[$menu->id] $menu->name";
        }
        return $arr;
    }

}
