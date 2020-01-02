<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:13 PM
 */

namespace App\Repository;


use App\Model\SyncLog;

class SyncLogRepository extends BaseRepository
{
    public function __construct(SyncLog $model)
    {
        parent::__construct($model);
    }

}