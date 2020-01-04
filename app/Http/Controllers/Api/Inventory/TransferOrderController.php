<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Libraries\Api;
use App\Repository\Inventory\MasterMaterialDetailRepository;
use App\Repository\Inventory\ReceiveOrderDetailRepository;

class TransferOrderController extends Controller
{
    private $material_detail_repository;
    private $receive_detail_repository;

    public function __construct(
        MasterMaterialDetailRepository $material_detail_repository,
        ReceiveOrderDetailRepository $receive_detail_repository)
    {
        $this->material_detail_repository = $material_detail_repository;
        $this->receive_detail_repository  = $receive_detail_repository;
    }

    public function getMaterialDetailTransferOut()
    {
        $arr_material_id = [];
        $arr_material_detail = [];
        $material_details = $this->material_detail_repository->getMaterialDetailTransferOut();
        foreach ($material_details as &$item) {
            $uoms           = [
                //$item->supplier_uom_id => $item->supplier_uom_name,
                $item->report_uom_id   => $item->report_uom_name
            ];
            $item->uom_id   = $item->report_uom_id;
            $item->uoms     = $uoms;
            $arr_material_detail[$item->id] = $item;
        }
        // Get price from PCC
        $material_detail_price_pcc = $this->material_detail_repository->getMaterialDetailWithLatestPccByIds(array_keys($arr_material_detail));
        foreach ($material_detail_price_pcc as $item) {
            if (isset($arr_material_detail[$item->id])) {
                $arr_material_detail[$item->id]->price = $item->begining_price;
            }
        }
        // Get price from PCC detail
        $material_detail_price_pcc_detail = $this->material_detail_repository->getMaterialDetailWithLatestPccDetailByIds(array_keys($arr_material_detail));
        foreach ($material_detail_price_pcc_detail as $item) {
            if (isset($arr_material_detail[$item->id]) && $arr_material_detail[$item->id]->price == 0) {
                $arr_material_detail[$item->id]->price = $item->price_in_outlet;
            }
        }
        $response = [
            'message' => 'Get material detail successfully .',
            'data'    => $arr_material_detail
        ];

        return Api::response($response);
    }

}
