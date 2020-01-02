<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:01 PM
 */

namespace App\Repository;


use App\Model\FailedJobs;

class FailedJobRepository extends BaseRepository
{
    public function __construct(FailedJobs $model)
    {
        parent::__construct($model);
    }

}