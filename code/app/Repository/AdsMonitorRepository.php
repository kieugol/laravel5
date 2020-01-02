<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/10/2019
 * Time: 10:38 AM
 */

namespace App\Repository;


use App\Model\AdsMonitor;

class AdsMonitorRepository extends BaseRepository
{
    public function __construct(AdsMonitor $model)
    {
        parent::__construct($model);
    }

}