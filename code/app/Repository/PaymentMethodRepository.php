<?php

/**
 * Created by PhpStorm.
 * User: Bhavya
 * Date: 21/09/2018
 * Time: 4:44 PM
 */

namespace App\Repository;

use App\Model\PaymentMethod;

class PaymentMethodRepository extends BaseRepository
{

    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function getAll()
    {
        $this->model
            ->select(PaymentMethod::getColumnName("*"))
            ->where(PaymentMethod::getColumnName('is_active'), STATUS_ACTIVE)
            ->get();
    }

}
