<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\{CurrentStock,
    MasterMaterial,
    MasterMaterialDetail,
    MasterPCC,
    MasterSupplier,
    MasterUom,
    MasterAccount,
    MasterUomDetail,
    PCC,
    PCCDetail};
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class MasterMaterialDetailRepository extends BaseRepository
{
    public function __construct(MasterMaterialDetail $model)
    {
        parent::__construct($model);
    }

    public function getMaterialByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getMaterialByCodes($codes)
    {
        return $this->model->whereIn('code', $codes)->get();
    }

    public function getMaterialDetailById($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function getAllForCreateStockOptName()
    {
        //$objModel = PCCDetail::select(PCCDetail::getCol('material_detail_id'))->where(PCCDetail::getCol('transaction_type'), "'" . TRANSACTION_BEGINNING . "'");
        //$rawQuery = str_replace_array('?', $objModel->getBindings(), $objModel->toSql());
        //$rawQueryPCC  = PCC::select('material_detail_id')->groupBy('material_detail_id')->toSql();
        $result = $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                MasterMaterialDetail::getCol('material_id'),
                MasterMaterialDetail::getCol('id AS material_detail_id'),
                MasterMaterialDetail::getCol('code'),
                MasterMaterialDetail::getCol('name'),
                MasterMaterialDetail::getCol('supplier_uom_id'),
                MasterMaterialDetail::getCol('smaller_uom_id'),
                MasterMaterialDetail::getCol('smaller_uom_detail_id'),
                MasterMaterialDetail::getCol('outlet_uom_id'),
                MasterMaterialDetail::getCol('outlet_uom_detail_id'),
                MasterMaterialDetail::getCol('recipe_uom_id'),
                MasterMaterialDetail::getCol('recipe_uom_detail_id'),
                MasterMaterialDetail::getCol('report_uom_id'),
                CurrentStock::getCol('quantity_recipe AS potential_ending'),
                'uom_net_weight.name AS net_weight_name',
                'uom_supplier.name AS supplier_uom_description',
                'uom_smaller.name AS smaller_uom_description',
                'uom_outlet.name AS outlet_uom_description',
                'uom_recipe.name AS recipe_uom_description',
                MasterUom::getCol('name as report_uom_description'),
                MasterUomDetail::getCol('conversion_rate AS conversion_rate_smaller'),
                'uom_detail_tmp.conversion_rate AS conversion_rate_outlet',
                DB::raw("group_concat(" . PCC::getCol('total_available') . ") AS total_available"),
                DB::raw("group_concat(DISTINCT(" . PCC::getCol('master_pcc_id') . ")) AS master_pcc_ids"),
            ])
            ->join(MasterUom::getTbl(), MasterUom::getCol('id'), MasterMaterialDetail::getCol('report_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_supplier', 'uom_supplier.id', MasterMaterialDetail::getCol('supplier_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_smaller', 'uom_smaller.id', MasterMaterialDetail::getCol('smaller_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_outlet', 'uom_outlet.id', MasterMaterialDetail::getCol('outlet_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_recipe', 'uom_recipe.id', MasterMaterialDetail::getCol('recipe_uom_id'))
            ->leftJoin(PCC::getTbl(), PCC::getCol('material_detail_id'), MasterMaterialDetail::getCol('id'))
            ->join(MasterUomDetail::getTbl(), MasterUomDetail::getCol('id'), MasterMaterialDetail::getCol('smaller_uom_detail_id'))
            ->join(MasterUomDetail::getTbl() . ' AS uom_detail_tmp', 'uom_detail_tmp.id', MasterMaterialDetail::getCol('outlet_uom_detail_id'))
            ->leftJoin(CurrentStock::getTbl(), CurrentStock::getCol('material_id'), MasterMaterialDetail::getCol('material_id'))
            ->join(MasterUom::getTbl() . ' AS uom_net_weight', 'uom_net_weight.id', CurrentStock::getCol('uom_id'))
            ->where(MasterMaterialDetail::getCol('is_active'), STATUS_ACTIVE)
            ->where(MasterMaterialDetail::getCol('has_transaction'), STATUS_ACTIVE)
//            ->where(function ($sub) use($rawQuery, $rawQueryPCC) {
//                    ->WhereRaw(MasterMaterialDetail::getCol('id') . " IN ($rawQuery)")
//                    ->orWhereRaw(MasterMaterialDetail::getCol('id') . " IN ($rawQueryPCC)");
//            })
            ->orderBy(MasterMaterialDetail::getCol('id'), 'ASC')
            ->groupBy(MasterMaterialDetail::getCol('id'))
            ->get();

        return $result;
    }

    public function getMaterialDetailTransferOut()
    {
        return $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                MasterMaterialDetail::getCol('code'),
                MasterMaterialDetail::getCol('name'),
                MasterMaterialDetail::getCol('material_id'),
                MasterUomDetail::getCol('name AS smaller_uom_detail_name'),
                DB::raw('0 as quantity'),
                DB::raw('0 as price'),
                'supplier_uom.id AS supplier_uom_id',
                'report_uom.id AS report_uom_id',
                'supplier_uom.name AS supplier_uom_name',
                'report_uom.name AS report_uom_name',
                MasterAccount::getCol('id AS account_id'),
                MasterAccount::getCol('code AS account_code'),
                MasterAccount::getCol('name AS account_name')
            ])
            ->join(MasterMaterial::getTbl(), MasterMaterial::getCol('id'), MasterMaterialDetail::getCol('material_id'))
            ->join(MasterAccount::getTbl(), MasterAccount::getCol('id'), MasterMaterial::getCol('account_id'))
            ->join(MasterSupplier::getTbl(), MasterSupplier::getCol('id'), MasterMaterialDetail::getCol('supplier_id'))
            ->join(MasterUom::getTbl() . ' as supplier_uom', 'supplier_uom.id', MasterMaterialDetail::getCol('supplier_uom_id'))
            ->join(MasterUom::getTbl() . ' as report_uom', 'report_uom.id', MasterMaterialDetail::getCol('report_uom_id'))
            ->join(MasterUomDetail::getTbl(), MasterUomDetail::getCol('id'), MasterMaterialDetail::getCol('smaller_uom_detail_id'))
            ->where(MasterMaterialDetail::getCol('is_active'), STATUS_ACTIVE)
            ->where(MasterMaterialDetail::getCol('has_transaction'), STATUS_ACTIVE)
            ->get();
    }

    public function UpdateByIds(array $data, array $ids)
    {
        return $this->model->whereIn(MasterMaterialDetail::getPriKeyName(), $ids)->update($data);
    }

    public function getRecipeUomDetailByMaterialId($material_id)
    {
        $query_builder = $this->model->select([
            MasterMaterial::getCol('recipe_rate_uom_id'),
            MasterMaterial::getCol('recipe_rate'),
            MasterMaterialDetail::getCol('recipe_uom_id'),
            MasterMaterialDetail::getCol('outlet_uom_id'),
            MasterUomDetail::getCol('id'),
            MasterUomDetail::getCol('name'),
            MasterUomDetail::getCol('conversion_rate'),
            MasterUom::getCol('name AS outlet_uom_name')
        ])
            ->join(MasterMaterial::getTbl(), MasterMaterial::getCol('id'), MasterMaterialDetail::getCol('material_id'))
            ->join(MasterUomDetail::getTbl(), MasterUomDetail::getCol('id'), MasterMaterialDetail::getCol('recipe_uom_detail_id'))
            ->join(MasterUom::getTbl(), MasterUom::getCol('id'), MasterMaterialDetail::getCol('outlet_uom_id'))
            ->where(MasterMaterialDetail::getCol('material_id'), $material_id)
            ->where(MasterMaterialDetail::getCol('is_active'), STATUS_ACTIVE)
            ->groupBy(MasterUomDetail::getCol('name'));

        $items = $query_builder->get();

        return $items;
    }

    public function getMaterialDetailForTotalUsage()
    {
        $query = $this->model->where('is_active', 1)
            ->where('has_transaction', 1)
            ->groupBy('material_id');
        return $query->get();
    }

    public function getDataAfterReceiveForStockOptNameUpdate($arrMaterialDetailID)
    {
        $result = $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                MasterMaterialDetail::getCol('material_id'),
                MasterMaterialDetail::getCol('id AS material_detail_id'),
                MasterMaterialDetail::getCol('code'),
                MasterMaterialDetail::getCol('name'),
                MasterMaterialDetail::getCol('supplier_uom_id'),
                MasterMaterialDetail::getCol('smaller_uom_id'),
                MasterMaterialDetail::getCol('smaller_uom_detail_id'),
                MasterMaterialDetail::getCol('outlet_uom_id'),
                MasterMaterialDetail::getCol('outlet_uom_detail_id'),
                MasterMaterialDetail::getCol('recipe_uom_id'),
                MasterMaterialDetail::getCol('recipe_uom_detail_id'),
                MasterMaterialDetail::getCol('report_uom_id'),
                'uom_supplier.name AS supplier_uom_description',
                'uom_smaller.name AS smaller_uom_description',
                'uom_outlet.name AS outlet_uom_description',
                'uom_recipe.name AS recipe_uom_description',
                MasterUom::getCol('name as report_uom_description'),
                MasterUomDetail::getCol('conversion_rate AS conversion_rate_smaller'),
                'uom_detail_tmp.conversion_rate AS conversion_rate_outlet',
                DB::raw("group_concat(" . PCC::getCol('total_available') . ") AS total_available"),
                DB::raw("group_concat(DISTINCT(" . PCC::getCol('master_pcc_id') . ")) AS master_pcc_ids"),
            ])
            ->join(MasterUom::getTbl(), MasterUom::getCol('id'), MasterMaterialDetail::getCol('report_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_supplier', 'uom_supplier.id', MasterMaterialDetail::getCol('supplier_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_smaller', 'uom_smaller.id', MasterMaterialDetail::getCol('smaller_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_outlet', 'uom_outlet.id', MasterMaterialDetail::getCol('outlet_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_recipe', 'uom_recipe.id', MasterMaterialDetail::getCol('recipe_uom_id'))
            ->leftJoin(PCC::getTbl(), PCC::getCol('material_detail_id'), MasterMaterialDetail::getCol('id'))
            ->join(MasterUomDetail::getTbl(), MasterUomDetail::getCol('id'), MasterMaterialDetail::getCol('smaller_uom_detail_id'))
            ->join(MasterUomDetail::getTbl() . ' AS uom_detail_tmp', 'uom_detail_tmp.id', MasterMaterialDetail::getCol('outlet_uom_detail_id'))
            ->where(MasterMaterialDetail::getCol('is_active'), STATUS_ACTIVE)
            ->where(MasterMaterialDetail::getCol('has_transaction'), STATUS_ACTIVE)
            ->whereNotIn(MasterMaterialDetail::getCol('id'), $arrMaterialDetailID)
            ->orderBy(MasterMaterialDetail::getCol('id'), 'ASC')
            ->groupBy(MasterMaterialDetail::getCol('id'))
            ->get()
            ->toArray();

        return $result;
    }

    public function getMaterialDetailWithLatestPccDetailByIds($material_detail_ids)
    {
        $objModel = PCCDetail::select([DB::raw('max(' . PCCDetail::getCol('id') . ')')])
            ->groupBy(PCCDetail::getCol('material_detail_id'));
        $objQuery = str_replace_array('?', $objModel->getBindings(), $objModel->toSql());
        return $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                PCCDetail::getCol('price_in_outlet')
            ])
            ->join(PCCDetail::getTbl(), PCCDetail::getCol('material_detail_id'), MasterMaterialDetail::getCol('id'))
            ->whereIn(MasterMaterialDetail::getCol('id'), $material_detail_ids)
            ->where(function ($sub) use($objQuery) {
                $sub->WhereRaw(PCCDetail::getCol('id') . " IN ($objQuery)");
            })
            ->get();
    }

    public function getMaterialDetailWithLatestPccByIds($material_detail_ids)
    {
        $objModel = PCC::select([DB::raw('max('.PCC::getCol('id').')')])
            ->groupBy(PCC::getCol('material_detail_id'));
        $objQuery = str_replace_array('?', $objModel->getBindings(), $objModel->toSql());
        return $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                PCC::getCol('begining_price')
            ])
            ->join(PCC::getTbl(), PCC::getCol('material_detail_id'), MasterMaterialDetail::getCol('id'))
            ->whereIn(MasterMaterialDetail::getCol('id'), $material_detail_ids)
            ->where(function ($sub) use($objQuery) {
                $sub->WhereRaw(PCC::getCol('id') . " IN ($objQuery)");
            })
            ->get();
    }

    /**
     * get by list of codes
     * @param $list_codes
     * @return mixed
     */
    public function getByCodes($list_codes)
    {
        $query_builder = $this->model
            ->whereIn('code', $list_codes);

        $items = $query_builder->get();

        return $items;
    }

    /**
     * get all material detail
     * @return mixed
     */
    public function getAllMaterialDetail() {
        $query = $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                MasterMaterialDetail::getCol('code'),
                MasterMaterialDetail::getCol('name'),
                'report_uom.name AS report_uom_name'
            ])
            ->join(MasterUom::getTbl() . ' as report_uom', 'report_uom.id',  MasterMaterialDetail::getCol('report_uom_id'))
            ->where(MasterMaterialDetail::getCol('is_active'), STATUS_ACTIVE)
            ->orderBy(MasterMaterialDetail::getCol('code'));

        return $query->get();
    }
    /**
     * @param $material_detail
     * @param $uom_detail_list
     * @return int
     */
    public function convertToReportUomRate($material_detail, $uom_detail_list) {
        $final_conversion_rate = 1;
        $arr_uoms              = [
            [
                'key'   => $material_detail['supplier_uom_id'],
                'value' => $material_detail['smaller_uom_detail_id']
            ],
            [
                'key'   => $material_detail['smaller_uom_id'],
                'value' => $material_detail['outlet_uom_detail_id']
            ],
            [
                'key'   => $material_detail['outlet_uom_id'],
                'value' => 1
            ],
        ];
        $condition_uom_id = $material_detail['report_uom_id'];
        foreach ($arr_uoms as $arr_uom) {
            if ($condition_uom_id != $arr_uom['key']) {
                $final_conversion_rate *= $uom_detail_list[$arr_uom['value']]->conversion_rate;
            } else {
                break;
            }
        }
        return $final_conversion_rate;
    }
}
