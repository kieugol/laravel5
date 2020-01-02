<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:06 PM
 */

namespace App\Repository;


use App\Model\OrderLogTime;

class OrderLogTimeRepository extends BaseRepository
{
    public function __construct(OrderLogTime $model)
    {
        parent::__construct($model);
    }

}