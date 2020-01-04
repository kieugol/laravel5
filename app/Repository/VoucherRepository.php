<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:16 PM
 */

namespace App\Repository;


use App\Model\Voucher;

class VoucherRepository extends BaseRepository
{
    public function __construct(Voucher $model)
    {
        parent::__construct($model);
    }

}