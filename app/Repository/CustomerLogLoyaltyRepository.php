<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 5:58 PM
 */

namespace App\Repository;


use App\Model\CustomerLogLoyalty;

class CustomerLogLoyaltyRepository extends BaseRepository
{
    public function __construct(CustomerLogLoyalty $model)
    {
        parent::__construct($model);
    }

}