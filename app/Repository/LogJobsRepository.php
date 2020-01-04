<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 12/24/2018
 * Time: 2:14 PM
 */

namespace App\Repository;


use App\Model\LogJob;

class LogJobsRepository extends BaseRepository
{
    public function __construct(LogJob $model) {
        parent::__construct($model);
    }

    /**
     * @param $log_job_id
     * @return boolean
     */
    public function updateStatusLogJobs($log_job_id)
    {
        $response = '{"status":true,"message":"Sync order is successfully","data":""}';
        return $this->model->where('id', $log_job_id)->update([
            'status' => SUCCESS,
            'response' => $response
        ]);
    }
}