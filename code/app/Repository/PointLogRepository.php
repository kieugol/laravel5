<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:09 PM
 */

namespace App\Repository;


use App\Model\PointLog;

class PointLogRepository extends BaseRepository
{
    public function __construct(PointLog $model)
    {
        parent::__construct($model);
    }

}