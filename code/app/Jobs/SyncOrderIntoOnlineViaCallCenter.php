<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Repository\Api\{LogJobRepository, SyncOrderRepository};
use App\Repository\{JobRepository, SyncOrderDeliveryRepository};
use Illuminate\Http\{Request, Response};

class SyncOrderIntoOnlineViaCallCenter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logJobRep;
    protected $orderRep;
    protected $jobRep;
    protected $syncOrderRep;
    protected $orderId;
    protected $timeout = 20;
    
    /**
     * Create a new job instance.
     *
     * @param $orderId
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle (
        SyncOrderRepository $orderRep,
        LogJobRepository $logJobRep,
        JobRepository $jobRep,
        SyncOrderDeliveryRepository $syncOrderRep
    )
    {
        $this->orderRep     = $orderRep;
        $this->logJobRep    = $logJobRep;
        $this->jobRep       = $jobRep;
        $this->syncOrderRep = $syncOrderRep;
    
        $orderDetail       = $this->orderRep->getOrderDetailByOrderId($this->orderId);
        $orderDetailFilter = $this->filterOrderDetail($orderDetail);

        $detailParams = [
            'order_detail'   => $orderDetailFilter,
            'order_delivery' => $this->orderRep->getOrderDeliveryByOrderId($this->orderId),
            'order_log_time' => $this->orderRep->getOrderLogTimeByOrderId($this->orderId),
            'order_payment'  => $this->orderRep->getOrderPaymentByOrderId($this->orderId),
        ];
    
        $params       = $this->orderRep->getOrderByOrderId($this->orderId);
        $params       = json_decode(json_encode($params, true), true);
        $detailParams = json_decode(json_encode($detailParams, true), true);
        $params       += $detailParams;
        $apiJUA       = env('CC_JUA_API_SYNC_ORDER_FOR_ONLINE');
        
        $result = $this->sendOrderDeliveryToCC($apiJUA, $params);
        $this->insertLogJob($apiJUA, $params, $result['http_code'], $result['data'], $result['err_mgs']);
        if ($result['http_code'] != Response::HTTP_OK) {
            // send to HIN server if not success at JUA
            $apiHIN = env('CC_HIN_API_SYNC_ORDER_FOR_ONLINE');
            $result = $this->sendOrderDeliveryToCC($apiHIN, $params);
            $this->insertLogJob($apiHIN, $params, $result['http_code'], $result['data'], $result['err_mgs']);
        }

        $response = json_decode($result['data'], true);

        $orderDeliverySync = $this->syncOrderRep->findByOrderId($this->orderId);
        if (empty($orderDeliverySync)) {
            $this->syncOrderRep->insert([
                'order_id'   => $this->orderId,
                'order_code' => $params['number'],
                'is_sync'    => $response['status'] ?? 0
            ]);
        } else {
            $this->syncOrderRep->update([
                'number_of_retry' => ($orderDeliverySync->number_of_retry + 1),
                'is_sync'         => $response['status'] ?? 0,
            ], $orderDeliverySync->id);
        }
    }

    protected function filterOrderDetail($result)
    {
        foreach ($result as &$row) {
            $id             = $row->id;
            $row->is_pizza  = $row->category_id == CATEGORY_PIZZA ? true : false;
            $row->is_option = $row->option_menu_id > 0 ? true : false;

            if (empty($row->order_detail_id)) {
                $row->menu_child = [];
                foreach ($result as $index => &$item) {
                    $parent_id       = $item->order_detail_id;
                    $item->is_pizza  = $item->category_id == CATEGORY_PIZZA ? true : false;
                    $item->is_option = $item->option_menu_id > 0 ? true : false;
                    if ($parent_id == $id || ($row->is_pizza && in_array($item->plucode, [EXTRA_CHEESE_JUMBO, EXTRA_CHEESE_REG]))) {
                        $row->menu_child[] = $item;
                        // Drop child item when inserted
                        unset($result[$index]);
                    }
                }
            }
        }

        return array_values($result);
    }
    
    protected function sendOrderDeliveryToCC($api, $params)
    {
        $result = [
            'data'      => '',
            'err_mgs'   => '',
            'http_code' => Response::HTTP_OK,
        ];
        
        try {
            $client         = new Client(['timeout' => $this->timeout, 'verify' => false]);
            $res            = $client->request(Request::METHOD_POST, $api, ['json' => $params]);
            $result['data'] = $res->getBody()->getContents();
        } catch (RequestException $reqEx) {
            $result['err_mgs'] = $reqEx->getMessage();
            if ($reqEx->hasResponse()) {
                $result['data']      = $reqEx->getResponse()->getBody()->getContents();
                $result['http_code'] = $reqEx->getCode();
            } else {
                $result['http_code'] = Response::HTTP_SERVICE_UNAVAILABLE;
            }
        }
        
        return $result;
    }
    
    protected function insertLogJob($url, $params, $httpCode, $data, $errMgs)
    {
        $this->logJobRep->insert([
            'order_id'     => $params['id'] ?? 0,
            'method'       => Request::METHOD_POST,
            'url'          => $url,
            'http_code'    => $httpCode,
            'params'       => json_encode($params, true),
            'response'     => $data,
            'error'        => $errMgs,
            'created_date' => date('Y-m-d H:i:s')
        ]);
    }
}
