<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:02 PM
 */

namespace App\Repository;


use App\Model\OrderDelivery;

class OrderDeliveryRepository extends BaseRepository
{
    public function __construct(OrderDelivery $model)
    {
        parent::__construct($model);
    }

}