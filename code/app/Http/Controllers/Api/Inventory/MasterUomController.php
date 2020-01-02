<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 3/4/2019
 * Time: 11:24 AM
 */

namespace App\Http\Controllers\Api\Inventory;


use App\Admin\Controllers\BaseController;
use App\Repository\Inventory\MasterUomRepository;
use Illuminate\Http\Response;

class MasterUomController extends BaseController
{
    private $master_uom_repository;
    public function __construct(MasterUomRepository $master_uom_repository)
    {
        parent::__construct();
        $this->master_uom_repository = $master_uom_repository;
    }

    public function list()
    {
        return response([
            'message' => '',
            'status'  => true,
            'data'    => $this->master_uom_repository->all(),
        ], Response::HTTP_OK);
    }
}