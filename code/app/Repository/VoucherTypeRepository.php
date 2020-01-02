<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:50 PM
 */

namespace App\Repository;


use App\Model\VoucherType;

class VoucherTypeRepository extends BaseRepository
{
    public function __construct(VoucherType $model)
    {
        parent::__construct($model);
    }

}