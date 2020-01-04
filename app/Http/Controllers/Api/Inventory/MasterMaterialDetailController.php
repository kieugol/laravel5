<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Libraries\Api;
use App\Repository\Inventory\{
    MasterMaterialDetailRepository,
    MasterMaterialDetailSupplierRepository,
    MasterMaterialDetailBarcodeRepository,
    MasterUomDetailRepository,
    ReceiveOrderDetailRepository,
    LocationRepository,
    MasterMaterialUsageRepository
};
use App\Admin\Controllers\BaseController;
use Illuminate\Http\{Response};

class MasterMaterialDetailController extends BaseController
{

    private $materialDetailRep;
    private $materialDetailSupplierRep;
    private $receiveOrderDetailRep;
    private $locationRep;
    private $masterUomDetailRep;
    private $materialDetailBarcodeRep;
    private $master_material_usage_rep;

    public function __construct(
        MasterMaterialDetailRepository $materialDetailRep,
        MasterMaterialDetailSupplierRepository $materialDetailSupplierRep,
        ReceiveOrderDetailRepository $receiveOrderDetailRep,
        LocationRepository $locationRep,
        MasterUomDetailRepository $masterUomDetailRep,
        MasterMaterialDetailBarcodeRepository $materialDetailBarcodeRep,
        MasterMaterialUsageRepository $master_material_usage_rep
    )
    {
        parent::__construct();
        $this->materialDetailRep         = $materialDetailRep;
        $this->materialDetailSupplierRep = $materialDetailSupplierRep;
        $this->receiveOrderDetailRep     = $receiveOrderDetailRep;
        $this->locationRep               = $locationRep;
        $this->masterUomDetailRep        = $masterUomDetailRep;
        $this->materialDetailBarcodeRep  = $materialDetailBarcodeRep;
        $this->master_material_usage_rep  = $master_material_usage_rep;
    }

    public function getBySupplier($supplierId)
    {
        $arr_material_detail_id = array();
        $arr_material_detail    = array();

        if ($supplierId == ALL) {
            $supplierId = '';
        }

        $material_details = $this->materialDetailSupplierRep->getBySupplierId($supplierId);
        foreach ($material_details as &$item) {
            $arr_material_detail_id[]       = $item->id;
            $arr_material_detail[$item->id] = $item;
            $item->uoms                     = [
                $item->supplier_uom_id . '_-_' . $item->supplier_uom_name => $item->supplier_uom_id . '_-_' . $item->supplier_uom_name,
                $item->smaller_uom_id . '_-_' . $item->smaller_uom_name => $item->smaller_uom_id . '_-_' . $item->smaller_uom_name
            ];
        }

        $material_price = $this->receiveOrderDetailRep->getLatestPriceByMaterialDetailIds($arr_material_detail_id);
        foreach ($material_price as $item) {
            if (isset($arr_material_detail[$item->material_detail_id])) {
                $arr_material_detail[$item->material_detail_id]->price = $item->price;
            }
        }
        return response([
            'message' => '',
            'status'  => true,
            'data'    => $arr_material_detail,
        ], Response::HTTP_OK);
    }

    public function getAll()
    {
        return response([
            'message' => '',
            'status'  => true,
            'data'    => $this->materialDetailSupplierRep->getBySupplierId(),
        ], Response::HTTP_OK);
    }

    public function getListCreateStockOpname()
    {

        $uom_detail_query  = $this->masterUomDetailRep->all();
        $uom_detail        = object_key_column($uom_detail_query, 'id');
        $list              = $this->materialDetailRep->getAllForCreateStockOptName()->toArray();
        $arrayMaterialId   = array_unique(array_column($list, 'material_id'));
        $dataUsageMaterial = $this->master_material_usage_rep->getTotalUsageByMaterialIds($arrayMaterialId);
        $location          = $this->locationRep->all('ASC');

        foreach ($list as &$row) {
            // Conversion to report uom
            $final_conversion_rate = 1;
            $arr_uoms              = [
                [
                    'key'   => $row['supplier_uom_id'],
                    'value' => $row['smaller_uom_detail_id']
                ],
                [
                    'key'   => $row['smaller_uom_id'],
                    'value' => $row['outlet_uom_detail_id']
                ],
                [
                    'key'   => $row['outlet_uom_id'],
                    'value' => 1
                ],
            ];

            $condition_uom_id = $row['report_uom_id'];
            foreach ($arr_uoms as $arr_uom) {
                if ($condition_uom_id != $arr_uom['key']) {
                    $final_conversion_rate *= $uom_detail[$arr_uom['value']]->conversion_rate;
                } else {
                    break;
                }

            }

            $row['final_conversion_rate'] = $final_conversion_rate;
            $row['total_usage_material']  = $dataUsageMaterial[$row['material_id']] ?? 0;

            foreach ($location as $item) {
                $row['locations'][] = [
                    'id'                       => $item->id,
                    'quantity_supplier_uom'    => 0,
                    'supplier_uom_description' => $row['supplier_uom_description'],
                    'quantity_outlet_uom'      => 0,
                    'report_uom_description'   => $row['report_uom_description'],
                ];
            }
            // Transform for total_available
            $arr_pcc_id = explode(',', $row['master_pcc_ids']);
            $arr_total_available = explode(',', $row['total_available']);
            $arr_total_available_final = [];
            foreach ($arr_pcc_id as $index_pcc => $item_pcc) {
                $arr_total_available_final[$item_pcc] = $arr_total_available[$index_pcc];
            }
            $row['arr_total_available_final'] = $arr_total_available_final;
        }

        $response = [
            'message' => '',
            'data'    => $list,
        ];

        return Api::response($response);
    }

    public function getByBarcode()
    {
        $this->validate($this->request, [
            "barcode" => 'required'
        ]);
        $data     = $this->request->all();
        $material_detail = $this->materialDetailBarcodeRep->getByBarcode($data['barcode']);
        return response([
            'message' => '',
            'status'  => true,
            'data'    => $material_detail,
        ], Response::HTTP_OK);
    }

}
