<?php

namespace App\Repository;

use App\Model\OrderResyncStatus;
use App\Repository\LogCurlRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OrderResyncStatusRepository extends BaseRepository
{
    protected $logcurlRepo;
            
    function __construct(OrderResyncStatus $model, LogCurlRepository $logcurl) {
        parent::__construct($model);
        $this->logcurlRepo = $logcurl;
    }

    public function resyncOrderStatus($orderId)
    {
        $response = [
            'message' => 'Resync successfully!',
            'status'  => true,
            'code'    => 200,
            'result'  => '',
        ];
        $errorMgs = '';

        try {
            $client = new Client(['timeout' => 10, 'verify' => false]);
            $params = ['order_id' => $orderId];
            $res    = $client->request('GET', API_POS_RESYNC_ORDER_STATUS, ['query' => $params]);
            $data   = $res->getBody()->getContents();
            $data   = json_decode($data, true);

            $response['message'] = $data['message'];
            $response['status']  = $data['status'];
        } catch (RequestException $ex) {
            $errorMgs            = $ex->getMessage();
            $response['message'] = 'CMS Resync failed';
            $response['code']    = 500;
            $response['status']  = false;
        }

        $this->logcurlRepo->insert([
            'url'          => API_POS_RESYNC_ORDER_STATUS,
            'method'       => 'GET',
            'params'       => json_encode($params),
            'response'     => json_encode($response),
            'http_code'    => $response['code'],
            'error'        => $errorMgs,
            'channel'      => 'POS_CMS',
            'created_date' => date('Y-m-d H:i:s')
        ]);

        return $response;
    }
}

