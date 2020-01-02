<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Repository\Api\OrderTakerRepository;
use App\Repository\Api\LogJobRepository;
use App\Helpers\ConfigHelp;

class DownloadOrderTaker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderTakerRep;
    protected $logJobRep;
    protected $orderTakerId;
    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderTakerId, $request)
    {
        $this->orderTakerId = $orderTakerId;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrderTakerRepository $orderTakerRep, LogJobRepository $logJobRepository)
    {
        $this->orderTakerRep = $orderTakerRep;
        $this->logJobRep = $logJobRepository;

        $params = [
            'pos_code' => ConfigHelp::get("outlet_code"),
            'order_taker_code' => $this->request['order_taker_code'],
            'status' => DOWNLOAD_ORDER_TAKER_SUCCESS
        ];
        $arrContextOptions = [
            "ssl"=> [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ];

        try {
            $content = file_get_contents($this->request['path_file'], false, stream_context_create($arrContextOptions));
            $file_name = pathinfo($this->request['path_file'], PATHINFO_BASENAME);
            $base_pah = public_path('/upload/order_taker');
            if (!file_exists($base_pah)) {
                mkdir($base_pah, 0777, true);
            }
            $destination = $base_pah . '/' . $file_name;
            file_put_contents($destination, $content);

            $this->orderTakerRep->update(['is_active' => STATUS_ACTIVE], $this->orderTakerId);
        }  catch (\Exception $e) {
            $params['status'] = DOWNLOAD_ORDER_TAKER_FAILED;
            $this->orderTakerRep->destroy($this->orderTakerId);
        }

        $this->sendApiUpdateStatus($params);
    }

    protected function sendApiUpdateStatus($params)
    {
        $url = env('JUMPBOX_API_UPDATE_STATUS_SYNC_ORDER_TAKER');
        try {
            $client = new Client(['timeout' => 10, 'verify' => false]);
            $res = $client->request(METHOD_POST, $url, ['json' => $params]);
            $data = $res->getBody()->getContents();
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $data = $response->getBody()->getContents();
        }

        $datalogJobs = [
            'order_id' => 0,
            'method' => METHOD_POST,
            'url' => $url,
            'params' => json_encode($params),
            'response' => $data,
            'created_date' => date('Y-m-d H:i:s')
        ];

        $this->logJobRep->insert($datalogJobs);
    }

}
