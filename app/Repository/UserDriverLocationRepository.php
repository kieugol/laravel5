<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:15 PM
 */

namespace App\Repository;


use App\Model\UserDriverLocation;

class UserDriverLocationRepository extends BaseRepository
{
    public function __construct(UserDriverLocation $model)
    {
        parent::__construct($model);
    }

}