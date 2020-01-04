<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:01 PM
 */

namespace App\Repository;


use App\Model\OrderCoupon;

class OrderCouponRepository extends BaseRepository
{
    public function __construct(OrderCoupon $model)
    {
        parent::__construct($model);
    }

}