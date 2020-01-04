<?php

namespace App\Jobs;

use App\Helpers\ConfigHelp;
use App\Repository\{AdsMenuRepository, AdsMonitorRepository, LogJobsRepository};
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DownloadAdvertise implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $adsMenuRepository;
    protected $objAdsMenuRepositoryName = 'adsMenuRepository';
    protected $adsMonitorRepository;
    protected $objAdsMonitorRepositoryName = 'adsMonitorRepository';
    
    protected $logJobRep;
    protected $adsData;
    protected $basePathAdsMenu;
    
    /**
     * DownloadAdvertise constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->adsData         = $data;
        $this->basePathAdsMenu = public_path('upload/ads_menu');
    }
    
    /**
     * @param AdsMenuRepository    $adsMenuRepository
     * @param AdsMonitorRepository $adsMonitorRepository
     * @param LogJobsRepository    $logJobRepository
     */
    public function handle(AdsMenuRepository $adsMenuRepository, AdsMonitorRepository $adsMonitorRepository, LogJobsRepository $logJobRepository)
    {
        $this->adsMenuRepository    = $adsMenuRepository;
        $this->adsMonitorRepository = $adsMonitorRepository;
        $this->logJobRep            = $logJobRepository;
        
        $params            = [
            "pos_code" => ConfigHelp::get("outlet_code"),
            "code"     => $this->adsData["code"],
            "status"   => ADS_STATUS_SUCCESS
        ];
        $arrContextOptions = [
            "ssl" => [
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ]
        ];
        $apiUrl = env('JUMPBOX_API_UPDATE_STATUS_DOWNLOAD_ADS_MONITOR');
        $objRep = $this->objAdsMonitorRepositoryName;
        if (strcasecmp($this->adsData['location_download'], $this->basePathAdsMenu) == 0) {
            $apiUrl = env('JUMPBOX_API_UPDATE_STATUS_DOWNLOAD_ADS_LED_MENU');
            $objRep = $this->objAdsMenuRepositoryName;
        }
        
        try {
            // Download ads file
            $content_file  = file_get_contents($this->adsData['file_path'], false, stream_context_create($arrContextOptions));
            $file_name     = $this->adsData['file_name'];
            $location_path = $this->adsData['location_download'];
            
            if (!file_exists($location_path)) {
                mkdir($location_path, 0777, true);
            }
            $destination_file = $location_path . DIRECTORY_SEPARATOR . $file_name;
            file_put_contents($destination_file, $content_file);
        } catch (\Exception $ex) {
            Log::error('DOWNLOAD FAILED LED'. $ex->getLine(). '_'.  $ex->getMessage());
            $params['status'] = ADS_STATUS_FAILED;
            $this->{$objRep}->destroy($this->adsData['id']);
        }
        
        $this->sendApiUpdateStatus($apiUrl, $params);
    }
    
    protected function sendApiUpdateStatus($apiUrl, $params)
    {
        try {
            $client = new Client(['timeout' => 10000, 'verify' => false]);
            $res    = $client->request(METHOD_POST, $apiUrl, ['json' => $params]);
            $data   = $res->getBody()->getContents();
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $data     = $response->getBody()->getContents();
        }
        
        $dataLogJobs = [
            'order_id'     => 0,
            'method'       => METHOD_POST,
            'url'          => $apiUrl,
            'params'       => json_encode($params),
            'response'     => $data,
            'created_date' => date("Y-m-d H:i:s")
        ];
        
        $this->logJobRep->insert($dataLogJobs);
    }
}
