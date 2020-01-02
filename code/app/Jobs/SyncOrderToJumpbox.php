<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use App\Repository\Api\SyncOrderRepository;
use App\Repository\Api\LogJobRepository;

class SyncOrderToJumpbox implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order_id;
    protected $sync_order_repository;
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
    public function handle(SyncOrderRepository $syncOrderRepository, LogJobRepository $logJobRepository)
    {
        $this->sync_order_repository = $syncOrderRepository;
        $this->log_job_repository = $logJobRepository;
        $order = $this->sync_order_repository->getOrderByOrderId($this->order_id);
        $order_details = $this->sync_order_repository->getOrderDetailByOrderId($this->order_id);
        $order_delivery = $this->sync_order_repository->getOrderDeliveryByOrderId($this->order_id);
        $order_payments = $this->sync_order_repository->getOrderPaymentByOrderId($this->order_id);
        $order_log_time = $this->sync_order_repository->getOrderLogTimeByOrderId($this->order_id);
        $url = API_JUMPBOX.'/sync/order';
        $params = [
            'order' => (array)$order,
            'order_details' => $order_details,
            'order_delivery' => (array)$order_delivery,
            'order_payments' => $order_payments,
            'order_log_time' => (array)$order_log_time
        ];
        try {
            $client = new Client(['timeout' => 5000, 'verify' => false]);
            $res = $client->request(METHOD_POST, $url, ['form_params' => $params]);
            $data = $res->getBody()->getContents();
            $data = json_decode($data, true);
            $status = SUCCESS;
        }  catch (ClientException $e) {
            $response = $e->getResponse();
            $data = $response->getBody()->getContents();
            $status = FAIL;
        }
        $insert_log_jobs = array(
            'order_id' => $this->order_id,
            'method' => METHOD_POST,
            'url' => $url,
            'params' => json_encode($params),
            'response' => json_encode($data),
            'status'  => $status,
            'created_date' => date('Y-m-d H:i:s')
        );
        $this->log_job_repository->insert($insert_log_jobs);
    }
}
