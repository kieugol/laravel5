<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:05 PM
 */

namespace App\Repository;


use App\Model\OrderLogStatus;

class OrderLogStatusRepository extends BaseRepository
{
    public function __construct(OrderLogStatus $model)
    {
        parent::__construct($model);
    }

}