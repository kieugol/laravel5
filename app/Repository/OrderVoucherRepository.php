<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:08 PM
 */

namespace App\Repository;


use App\Model\OrderVoucher;

class OrderVoucherRepository extends BaseRepository
{
    public function __construct(OrderVoucher $model)
    {
        parent::__construct($model);
    }

}