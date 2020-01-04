<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:00 PM
 */

namespace App\Repository;


use App\Model\DeviceToken;

class DeviceTokenRepository extends BaseRepository
{
    public function __construct(DeviceToken $model)
    {
        parent::__construct($model);
    }

}