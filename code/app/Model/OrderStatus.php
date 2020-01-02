<?php

namespace App\Model;


class OrderStatus extends BaseModel
{
    protected $table = 'order_status';

    private static $list;

    public static function getList(){
        if(self::$list){
            return self::$list;
        }

        self::$list = self::all()->pluck("name", "id");
        return self::$list;
    }
    //public $timestamps = false;
}
