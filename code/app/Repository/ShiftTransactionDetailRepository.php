<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:11 PM
 */

namespace App\Repository;


use App\Model\ShiftTransactionDetail;

class ShiftTransactionDetailRepository extends BaseRepository
{
    public function __construct(ShiftTransactionDetail $model)
    {
        parent::__construct($model);
    }

}