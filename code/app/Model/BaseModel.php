<?php

/**
 * BaseModel.php
 *
 * The base class model for all model extend
 *
 * @category  Model
 * @author krol kvrol@diqit.io
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class BaseModel extends  Model
{
    public static function getTableName()
    {
        return (new static)->getTable();
    }
    
    public static function getTbl()
    {
        return (new static)->getTable();
    }

    public static function getPriKeyName()
    {
        return (new static)->getKeyName();
    }

    public static function getColumnName($column)
    {
        return self::getTableName() . '.' . $column;
    }
    
    public static function getCol($column)
    {
        return self::getTableName() . '.' . $column;
    }
}
