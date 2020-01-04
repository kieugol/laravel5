<?php

namespace App\Jobs;

use App\Repository\Inventory\MasterAccountRepository;
use App\Repository\Inventory\MasterGenstoreRepository;
use App\Repository\Inventory\MasterGroupRepository;
use App\Repository\Inventory\MasterMaterialDetailRepository;
use App\Repository\Inventory\MasterMaterialDetailSupplierRepository;
use App\Repository\Inventory\MasterMaterialRepository;
use App\Repository\Inventory\MasterPCCRepository;
use App\Repository\Inventory\MasterRecipeDetailRepository;
use App\Repository\Inventory\MasterRecipeRepository;
use App\Repository\Inventory\MasterRecipeSkuRepository;
use App\Repository\Inventory\MasterSupplierRepository;
use App\Repository\Inventory\MasterTypeRepository;
use App\Repository\Inventory\MasterUomDetailRepository;
use App\Repository\Inventory\MasterUomRepository;
use App\Repository\Inventory\SyncMasterRepository;
use App\Repository\LogJobsRepository;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class InsertDataToPos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $version;
    private $outlet_code;
    private $file_name;
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($version, $outlet_code, $file_name)
    {
        $this->version  = $version;
        $this->outlet_code  = $outlet_code;
        $this->file_name   = $file_name;
    }

    public function handle(
        MasterAccountRepository $masterAccountRepository,
        MasterGenstoreRepository $masterGenstoreRepository,
        MasterGroupRepository $masterGroupRepository,
        MasterMaterialRepository $masterMaterialRepository,
        MasterMaterialDetailRepository $masterMaterialDetailRepository,
        MasterMaterialDetailSupplierRepository $masterMaterialDetailSupplierRepository,
        MasterPCCRepository $masterPCCRepository,
        MasterRecipeRepository $masterRecipeRepository,
        MasterRecipeDetailRepository $masterRecipeDetailRepository,
        MasterRecipeSkuRepository $masterRecipeSkuRepository,
        MasterSupplierRepository $masterSupplierRepository,
        MasterTypeRepository $masterTypeRepository,
        MasterUomRepository $masterUomRepository,
        MasterUomDetailRepository $masterUomDetailRepository,
        LogJobsRepository $logJobsRepository,
        SyncMasterRepository $syncMasterRepository
    )
    {
        $url      = URL_SYNC_MASTER_TO_JUMPBOX . '/update-status-detail';
        $params   = [
            'version'  => $this->version,
            'store_code' => $this->outlet_code,
            'is_sync'    => SUCCESS
        ];
        try {
            $path              = public_path(INVENTORY_FOLDER_SYNC_MASTER) . '/' . $this->file_name;
            $data              = file_get_contents($path);
            $data              = json_decode($data, true);

            DB::beginTransaction();
            if (isset($data['master_account'])) {
                $masterAccountRepository->truncateData();
                foreach ($data['master_account'] as $item) {
                    $masterAccountRepository->insert($item);
                }
            }
            if (isset($data['master_genstore'])) {
                $masterGenstoreRepository->truncateData();
                foreach ($data['master_genstore'] as $item) {
                    $masterGenstoreRepository->insert($item);
                }
            }
            if (isset($data['master_group'])) {
                $masterGroupRepository->truncateData();
                foreach ($data['master_group'] as $item) {
                    $masterGroupRepository->insert($item);
                }
            }
            if (isset($data['master_material'])) {
                $materials = $masterMaterialRepository->all()->keyBy('code')->toArray();
                $masterMaterialRepository->truncateData();
                foreach ($data['master_material'] as $item) {
                    if (isset($materials[$item['code']])) {
                        $item['price'] = $materials[$item['code']]['price'];
                        $item['moving_price'] = $materials[$item['code']]['moving_price'];
                    }
                    $masterMaterialRepository->insert($item);
                }
            }
            if (isset($data['master_material_detail'])) {
                $material_details = $masterMaterialDetailRepository->all()->keyBy('code')->toArray();
                $masterMaterialDetailRepository->truncateData();
                foreach ($data['master_material_detail'] as $item) {
                    if (isset($material_details[$item['code']])) {
                        $item['has_transaction'] = $material_details[$item['code']]['has_transaction'];
                    }
                    $masterMaterialDetailRepository->insert($item);
                }
            }
            if (isset($data['master_material_detail_supplier'])) {
                $masterMaterialDetailSupplierRepository->truncateData();
                foreach ($data['master_material_detail_supplier'] as $item) {
                    $masterMaterialDetailSupplierRepository->insert($item);
                }
            }
            if (isset($data['master_pcc'])) {
                $masterPCCRepository->truncateData();
                foreach ($data['master_pcc'] as $item) {
                    $masterPCCRepository->insert($item);
                }
            }
            if (isset($data['master_recipe'])) {
                $masterRecipeRepository->truncateData();
                foreach ($data['master_recipe'] as $item) {
                    $masterRecipeRepository->insert($item);
                }
            }
            if (isset($data['master_recipe_detail'])) {
                $masterRecipeDetailRepository->truncateData();
                foreach ($data['master_recipe_detail'] as $item) {
                    $masterRecipeDetailRepository->insert($item);
                }
            }
            if (isset($data['master_recipe_sku'])) {
                $masterRecipeSkuRepository->truncateData();
                foreach ($data['master_recipe_sku'] as $item) {
                    $masterRecipeSkuRepository->insert($item);
                }
            }
            if (isset($data['master_supplier'])) {
                $masterSupplierRepository->truncateData();
                foreach ($data['master_supplier'] as $item) {
                    $masterSupplierRepository->insert($item);
                }
            }
            if (isset($data['master_type'])) {
                $masterTypeRepository->truncateData();
                foreach ($data['master_type'] as $item) {
                    $masterTypeRepository->insert($item);
                }
            }
            if (isset($data['master_uom'])) {
                $masterUomRepository->truncateData();
                foreach ($data['master_uom'] as $item) {
                    $masterUomRepository->insert($item);
                }
            }
            if (isset($data['master_uom_detail'])) {
                $masterUomDetailRepository->truncateData();
                foreach ($data['master_uom_detail'] as $item) {
                    $masterUomDetailRepository->insert($item);
                }
            }
            $syncMasterRepository->updateWithConditions(['is_sync' => SUCCESS], ['file_name' => $this->file_name]);

            // Update status into Jumpbox
            $client   = new Client(['timeout' => 5000, 'verify' => false]);
            $res      = $client->request(METHOD_POST, $url, ['form_params' => $params]);
            $data     = $res->getBody()->getContents();
            $data     = json_decode($data, true);
            $response = json_encode($data);

            DB::commit();
        } catch (\Exception $ex) {
            $response      = $ex->getMessage();
        }

        $insert_log_jobs = array(
            'order_id'     => '',
            'method'       => METHOD_POST,
            'url'          => $url,
            'params'       => json_encode($params),
            'response'     => $response,
            'created_date' => date('Y-m-d H:i:s')
        );
        $logJobsRepository->insert($insert_log_jobs);
    }

}
