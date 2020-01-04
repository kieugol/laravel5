<?php

namespace App\Repository;

use App\Model\Job;

class JobRepository extends BaseRepository
{
    const NUMBER_LIMITED_RETRY = 255;

    public function __construct(Job $model)
    {
        parent::__construct($model);
    }

    public function getJobReadyStuck($queueName)
    {
        $result = $this->model
            ->select(Job::getColumnName("*"))
            ->where(Job::getColumnName('queue'), $queueName)
           // ->where(Job::getColumnName('attempts'), self::NUMBER_LIMITED_RETRY)
            ->get();

        return $result;
    }

}
