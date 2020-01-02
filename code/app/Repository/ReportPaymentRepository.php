<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:10 PM
 */

namespace App\Repository;


use App\Model\ReportPayment;

class ReportPaymentRepository extends BaseRepository
{
    public function __construct(ReportPayment $model)
    {
        parent::__construct($model);
    }

}