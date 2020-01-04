<?php

namespace App\Jobs;

use App\Repository\Api\LogJobRepository;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncAdvanceOrderToPosApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order_id;
    protected $log_job_repository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LogJobRepository $logJobRepository)
    {
        $this->log_job_repository = $logJobRepository;
        //send api to posapi
         try {
             $url = API_POSAPI . '/orderadvance/update_order_advanced';
             $client = new Client(['timeout' => 10, 'verify' => false]);
             $params = [
                 'order_id' => $this->order_id,
             ];
             $res = $client->request(METHOD_POST, $url, ['form_params' => $params]);
             $data = $res->getBody()->getContents();
             $data = json_decode($data, true);
         }  catch (ClientException $e) {
             $response = $e->getResponse();
             $data = $response->getBody()->getContents();
         }
        $insert_log_jobs = array(
            'order_id' => $this->order_id,
            'method' => METHOD_POST,
            'url' => $url,
            'params' => json_encode($this->order_id),
            'response' => json_encode($data),
            'created_date' => date('Y-m-d H:i:s')
        );
        $this->log_job_repository->insert($insert_log_jobs);
    }
}
