<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:11 PM
 */

namespace App\Http\Controllers\Api\Inventory;

use App\Admin\Controllers\BaseController;
use App\Jobs\InsertDataToPos;
use App\Libraries\Api;
use App\Repository\Inventory\SyncMasterRepository;
use Illuminate\Http\Request;

class SyncDataController extends BaseController
{
    private $_request;
    private $sync_master_repository;

    public function __construct(
        Request $request,
        SyncMasterRepository $sync_master_repository
    )
    {
        parent::__construct();
        $this->_request               = $request;
        $this->sync_master_repository = $sync_master_repository;
    }

    public function syncData()
    {
        $data = $this->_request->all();
        $file = $this->_request->file('file');

        if (!empty($data)) {
            try {
                $path = public_path(INVENTORY_FOLDER_SYNC_MASTER);
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $file->move($path, $data['file_name']);
                // Put data to queue insert data to pos
                InsertDataToPos::dispatch($data['version'], $data['outlet_code'], $data['file_name'])->onQueue(INVENTORY_QUEUE_INSERT_DATA_TO_POS);
                return Api::response([
                    'status' => true,
                    'message' => 'Sync data to Pos successfully'
                ]);
            } catch (\Exception $e) {
                return Api::response([
                    'status' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }

    }

    public function createNewSyncVersion()
    {
        $data = $this->request->all();
        $this->sync_master_repository->create([
            'version'   => $data['version'],
            'file_name' => $data['file_name'],
            'is_sync'   => 0
        ]);
        return Api::response([
            'message' => 'Create new sync version successfully'
        ]);
    }

}
