<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:11 PM
 */

namespace App\Repository;


use App\Model\ShiftTransaction;

class ShiftTransactionRepository extends BaseRepository
{
    public function __construct(ShiftTransaction $model)
    {
        parent::__construct($model);
    }

}