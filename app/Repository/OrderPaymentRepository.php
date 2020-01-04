<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:07 PM
 */

namespace App\Repository;


use App\Model\OrderPayment;

class OrderPaymentRepository extends BaseRepository
{
    public function __construct(OrderPayment $model)
    {
        parent::__construct($model);
    }

}