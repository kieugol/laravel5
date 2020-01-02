<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 5:57 PM
 */

namespace App\Repository;


use App\Model\CustomerAddress;

class CustomerAddressRepository extends BaseRepository
{
    public function __construct(CustomerAddress $model)
    {
        parent::__construct($model);
    }

}