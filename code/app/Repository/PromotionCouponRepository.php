<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:39 PM
 */

namespace App\Repository;


use App\Model\PromotionCoupon;

class PromotionCouponRepository extends BaseRepository
{
    public function __construct(PromotionCoupon $model)
    {
        parent::__construct($model);
    }

}