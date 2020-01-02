<?php

namespace App\Repository;

use App\Model\LogCurl;

class LogCurlRepository extends BaseRepository {

    public function __construct(LogCurl $model) {
        parent::__construct($model);
    }

    public function update_status_success($log_curl_id)
    {
        $data = $this->model->select('number_of_retry')->where('id', $log_curl_id)->first();
        $this->model->where('id', $log_curl_id)->update([
            'status' => SUCCESS,
            'number_of_retry' => $data->number_of_retry + 1
        ]);
    }

}
