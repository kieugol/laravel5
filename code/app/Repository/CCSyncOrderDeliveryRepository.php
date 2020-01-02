<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:48 PM
 */

namespace App\Repository;


use App\Model\CCSyncOrderDelivery;

class CCSyncOrderDeliveryRepository extends BaseRepository
{
    public function __construct(CCSyncOrderDelivery $model)
    {
        parent::__construct($model);
    }

}