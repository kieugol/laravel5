<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:04 PM
 */

namespace App\Repository;


use App\Model\OrderLogPrint;

class OrderLogPrintRepository extends BaseRepository
{
    public function __construct(OrderLogPrint $model)
    {
        parent::__construct($model);
    }

}