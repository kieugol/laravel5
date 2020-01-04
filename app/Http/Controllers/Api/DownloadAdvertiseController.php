<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadAdvertise;
use App\Repository\{AdsMenuRepository, AdsMonitorRepository};
use Illuminate\Http\{Request};
use App\Libraries\Api;
use Illuminate\Support\Facades\Log;

class DownloadAdvertiseController extends Controller
{
    const ADS_MENU_DIR_NAME    = 'ads_menu';
    const ADS_MONITOR_DIR_NAME = 'ads_monitor';
    
    private $request;
    private $adsMenuRepository;
    private $adsMonitorRepository;
    protected $basePathAdsMenu;
    protected $basePathAdsMonitor;
    protected $baseAssetUpload;
    
    public function __construct(Request $request, AdsMenuRepository $adsMenuRepository, AdsMonitorRepository $adsMonitorRepository)
    {
        $this->request              = $request;
        $this->adsMenuRepository    = $adsMenuRepository;
        $this->adsMonitorRepository = $adsMonitorRepository;
        $this->basePathAdsMenu      = public_path('upload/' . self::ADS_MENU_DIR_NAME);
        $this->basePathAdsMonitor   = public_path('upload/' . self::ADS_MONITOR_DIR_NAME);
    }
    
    public function downloadLedMenu()
    {
        $this->validate($this->request, [
            'code.*'      => 'required',
            'file_path.*' => 'required',
            'file_name.*' => 'required',
        ], []);
        
        $params = $this->request->all();
        
        $responseData = [
            'data'    => '',
            'message' => trans('download_ads.downloading_led_file')
        ];
        
        foreach ($params as $row) {
            $dataCreate = [
                'code'       => $row['code'],
                'filename'   => self::ADS_MENU_DIR_NAME . DIRECTORY_SEPARATOR . $row['file_name'],
                'base_url'   => BASE_URL_UPLOAD,
                'is_actived' => STATUS_ACTIVE
            ];
    
            Log::info('Download ads : ', $dataCreate);
            
            $result                   = $this->adsMenuRepository->create($dataCreate);
            $row['id']                = $result->id;
            $row['location_download'] = $this->basePathAdsMenu;
            
            DownloadAdvertise::dispatch($row)->onQueue(QUEUE_DOWNLOAD_ADS);
            
            $responseData['data'] = [
                'code'   => $row['code'],
                'status' => ADS_STATUS_DOWNLOADING
            ];
        }
        
        return Api::response($responseData);
    }
    
    public function downloadMonitor()
    {
        $this->validate($this->request, [
            'code.*'      => 'required',
            'file_path.*' => 'required',
            'file_name.*' => 'required',
        ], []);
        
        $params       = $this->request->all();
        $responseData = [
            'data'    => '',
            'message' => trans('download_ads.downloading_monitor_file')
        ];
        
        foreach ($params as $row) {
            $dataCreate = [
                'code'             => $row['code'],
                'filename'         => self::ADS_MONITOR_DIR_NAME . DIRECTORY_SEPARATOR . $row['file_name'],
                'base_url'         => BASE_URL_UPLOAD,
                'is_actived'       => STATUS_ACTIVE
            ];
            
            $result                   = $this->adsMonitorRepository->create($dataCreate);
            $row['id']                = $result->id;
            $row['location_download'] = $this->basePathAdsMonitor;
            
            DownloadAdvertise::dispatch($row)->onQueue(QUEUE_DOWNLOAD_ADS);
            
            $responseData['data'] = [
                'code'   => $row['code'],
                'status' => ADS_STATUS_DOWNLOADING
            ];
        }
        
        return Api::response($responseData);
    }
}
