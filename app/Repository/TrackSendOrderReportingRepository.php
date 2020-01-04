<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:47 PM
 */

namespace App\Repository;


use App\Model\TrackSendOrderReporting;

class TrackSendOrderReportingRepository extends BaseRepository
{
    public function __construct(TrackSendOrderReporting $model)
    {
        parent::__construct($model);
    }

}