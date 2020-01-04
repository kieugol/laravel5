<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 5:56 PM
 */

namespace App\Repository;


use App\Model\Customer;

class CustomerRepository extends BaseRepository
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

}