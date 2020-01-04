<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:25 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\{CurrentStockRecipe, DailyBatch, MasterMaterialDetail, MasterRecipe, MasterUom, PCC};
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class MasterRecipeRepository extends BaseRepository
{
    const SPLIT_KEY = '_split_';

    public function __construct(MasterRecipe $model)
    {
        parent::__construct($model);
    }

    public function getBySku($sku)
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function getAllRecipeForDailyBatch()
    {
        return $this->model
            ->select([
                MasterRecipe::getCol('id'),
                MasterRecipe::getCol('name'),
                MasterRecipe::getCol('code'),
                MasterRecipe::getCol('usage'),
                MasterUom::getCol('name AS uom_name')
            ])
            ->join(MasterUom::getTbl(), MasterUom::getCol('id'), '=', MasterRecipe::getCol('uom_id'))
            ->where(MasterRecipe::getCol('is_daily_batch'), STATUS_ACTIVE)
            ->get();
    }

    public function getForCreateStockOpname($stockOpnameRecipeMapping = [])
    {
        $result               = [];
        $arrOtherRecipeId     = [];
        $dataRecipe           = $this->getMasterDataRecipe();
        
        $this->getAllOtherRecipeId($arrOtherRecipeId, $dataRecipe); // Get all other recipe id in master data
        $arrRecipeId =  !empty($arrOtherRecipeId) ? call_user_func_array('array_merge', $arrOtherRecipeId) : [];
        
        $dataRecipeOther = $this->getMasterDataRecipe($arrRecipeId);
        if (!$dataRecipeOther->isEmpty()) {
            $dataRecipe = $dataRecipe->merge($dataRecipeOther);
        }
        
        $arrRecipePccId = $this->gePCCIDByRecipe($dataRecipe, $arrOtherRecipeId);
        
        $common_material_detail = $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                DB::raw("group_concat(" . PCC::getCol('total_available') . ") AS total_available"),
                DB::raw("group_concat(" . PCC::getCol('master_pcc_id') . ") AS master_pcc_ids"),
            ])
            ->from(MasterMaterialDetail::getTbl())
            ->leftJoin(PCC::getTbl(), PCC::getCol('material_detail_id'), MasterMaterialDetail::getCol('id'))
            ->groupBy(MasterMaterialDetail::getCol('id'))
            ->get()
            ->keyBy('id')
            ->toArray();
        
        foreach ($dataRecipe as $row) {
            $pccIds = $arrRecipePccId[$row->id] ?? [];
            
            foreach ($row->recipe_detail as $recipeDetailMaster) {
                if (!empty($recipeDetailMaster->other_recipe_id)) {
                    continue;
                }
    
                $item = [
                    'recipe_id'                => $row->id,
                    'recipe_code'              => $row->code,
                    'recipe_name'              => $row->name,
                    'recipe_qty'               => 0,
                    'recipe_usage'             => $row->usage,
                    'master_pcc_ids'           => $pccIds,
                    'recipe_uom_name'          => $row->uom->name ?? 'Error',
                    'recipe_detail_id'         => $recipeDetailMaster->id,
                    'recipe_detail_usage'      => $recipeDetailMaster->usage,
                    'material_id'              => $recipeDetailMaster->material->id ?? 0,
                    'material_name'            => $recipeDetailMaster->material->name ?? '',
                    'recipe_rate_uom_id'       => $recipeDetailMaster->material->recipe_rate_uom_id ?? 0,
                    'recipe_rate'              => $recipeDetailMaster->material->recipe_rate ?? 0,
                    'material_recipe_uom_name' => $recipeDetailMaster->material->recipe_uom->name ?? 'Error',
                    'potential_ending'         => $recipeDetailMaster->material->current_stock->quantity_recipe ?? 0,
                    'net_weight_name'          => $recipeDetailMaster->material->current_stock->net_weight_uom->name ?? 'Error',
                    'material_detail_id'       => 0,
                    'material_detail_qty'      => 0,
                    'material_details'         => [],
                    'total_ending'             => 0,
                ];
                
                $recipeDetail = $item;
                // Get conversion_rate_outlet, conversion_rate_smaller
                $recipeDetail['material_details'] = $this->appendMaterialDetail($recipeDetailMaster->material->material_detail, $common_material_detail);
                // Active value material_detail_qty, material_detail_id, recipe_qty
                $this->activeMasterDataRecipe($stockOpnameRecipeMapping, $recipeDetail);
                // Append material into recipe detail
                $result[] = $recipeDetail;

                /*if (!empty($recipeDetailMaster->other_recipe_id)) {
                    $arrMaterial = [];
                    $this->getAllMaterialDetail($arrMaterial, $row->recipe_detail);

                    foreach ($arrMaterial as $data => $material_detail) {
                        $recipeDetail                             = $item;
                        $material                                 = explode(self::SPLIT_KEY, $data);
                        $recipeDetail['material_id']              = $material[0];
                        $recipeDetail['material_name']            = $material[1];
                        $recipeDetail['material_recipe_uom_name'] = $material[2];
                        $recipeDetail['material_details']         = $this->appendMaterialDetail($material_detail, $common_material_detail);
                        $recipeDetail['recipe_detail_id']         = $material[3];
                        $recipeDetail['recipe_detail_usage']      = $material[4];
                        // Active value material_detail_qty, material_detail_id, recipe_qty
                        $this->activeMasterDataRecipe($stockOpnameRecipeMapping, $recipeDetail);
                        // Append material into recipe detail
                        $result[] = $recipeDetail;
                    }
                } */
                
            }
        }
        return $result;
    }

    protected function activeMasterDataRecipe($stockOpnameRecipe, &$recipe)
    {
        // Set recipe data value
        if ($stockOpnameRecipe && isset($stockOpnameRecipe[$recipe['recipe_id']][$recipe['recipe_detail_id']][$recipe['material_id']])) {
            $row = $stockOpnameRecipe[$recipe['recipe_id']][$recipe['recipe_detail_id']][$recipe['material_id']];

            $recipe['material_detail_id']  = $row['material_detail_id'];
            $recipe['material_detail_qty'] = $row['material_detail_qty'];
            $recipe['recipe_qty']          = $row['recipe_qty'];
        }
    }

    protected function appendMaterialDetail($data, $common_material_detail)
    {
        $result = [];
        foreach ($data as $materialDetail) {

            $arr_total_available_final = [];
            $arr_handle                = $common_material_detail[$materialDetail->id];
            $arr_pcc_id                = explode(',', $arr_handle['master_pcc_ids']);
            $arr_total_available       = explode(',', $arr_handle['total_available']);
            foreach ($arr_pcc_id as $index_pcc => $item_pcc) {
                $arr_total_available_final[$item_pcc] = $arr_total_available[$index_pcc] ?? 0;
            }

            // only get material detail which is active = 1
            if ($materialDetail->is_active == STATUS_ACTIVE) {
                // key to re-arrange material details, material detail satisfy these conditions will be on top
                // we show recommendation base on has_transaction = 1
                // a. material_detail with has_transaction = 1 if only have 1 material_detail
                // b. if has 2 material_detail with has_transaction = 1, set the latest material_detail on receive/transfer_in
                // if no  has_transaction = 1 do nothing.
                $key = $materialDetail->has_transaction.'-'.strtotime($materialDetail->updated_date);// when use krsort, it will sort by has transaction first then update time

                $result[$key] = [
                    'id'                        => $materialDetail->id,
                    'code'                      => $materialDetail->code,
                    'name'                      => $materialDetail->name,
                    'report_uom_id'             => $materialDetail->report_uom_id,
                    'recipe_uom_id'             => $materialDetail->recipe_uom_id,
                    'outlet_uom_id'             => $materialDetail->outlet_uom_id,
                    'smaller_uom_id'            => $materialDetail->smaller_uom_id,
                    'supplier_uom_id'           => $materialDetail->supplier_uom_id,
                    'recipe_uom_detail_id'      => $materialDetail->recipe_uom_detail_id,
                    'outlet_uom_detail_id'      => $materialDetail->outlet_uom_detail_id,
                    'smaller_uom_detail_id'     => $materialDetail->smaller_uom_detail_id,
                    'has_transaction'           => $materialDetail->has_transaction,
                    'report_uom_description'    => $materialDetail->report_uom->name ?? 'Error',
                    'arr_total_available_final' => $arr_total_available_final,
                ];
            }
        }

        // re-arrange base on has transaction and update time
        krsort($result);

        return $result;
    }

    protected function getAllMaterialDetail(&$arrMaterialDetail, $arrRecipeDetail)
    {
        foreach ($arrRecipeDetail as $recipeDetail) {
            if (!empty($recipeDetail->material_id)) {
                $recipeUOM = $recipeDetail->material->recipe_uom->name ?? 'Error';
                foreach ($recipeDetail->material->material_detail as $materialDetail) {
                    $key = $recipeDetail->material_id . self::SPLIT_KEY . $recipeDetail->material->name . self::SPLIT_KEY . $recipeUOM;
                    $arrMaterialDetail[$key][] = [
                        'id'                    => $materialDetail->id,
                        'code'                  => $materialDetail->code,
                        'name'                  => $materialDetail->name,
                        'report_uom_id'         => $materialDetail->report_uom_id,
                        'recipe_uom_id'         => $materialDetail->recipe_uom_id,
                        'outlet_uom_id'         => $materialDetail->outlet_uom_id,
                        'smaller_uom_id'        => $materialDetail->smaller_uom_id,
                        'supplier_uom_id'       => $materialDetail->supplier_uom_id,
                        'recipe_uom_detail_id'  => $materialDetail->recipe_uom_detail_id,
                        'outlet_uom_detail_id'  => $materialDetail->outlet_uom_detail_id,
                        'smaller_uom_detail_id' => $materialDetail->smaller_uom_detail_id
                    ];
                }
            } elseif (!empty($recipeDetail->other_recipe->recipe_detail)) {
                $this->getAllMaterialDetail($arrMaterialDetail, $recipeDetail->other_recipe->recipe_detail);
            }
        }
    }
    
    public function getMasterDataRecipe($arrRecipeId = [])
    {
        $query = $this->model
            ->select(
                MasterRecipe::getCol('*'),
                DB::raw("GROUP_CONCAT(DISTINCT(" . DailyBatch::getCol('master_pcc_id') . ")) AS master_pcc_ids")
            )
            ->join(DailyBatch::getTbl(), DailyBatch::getCol('recipe_id'), MasterRecipe::getCol('id'))
            ->where(MasterRecipe::getCol('is_active'), STATUS_ACTIVE);
        
        if ($arrRecipeId) {
            $query->whereIn(MasterRecipe::getCol('id'), $arrRecipeId);
        }
        
        $dataRecipe = $query->groupBy(MasterRecipe::getCol('id'))->get();
        
        return $dataRecipe;
    }
    
    protected function getAllOtherRecipeId(&$arrOtherRecipeId, $dataRecipe)
    {
        foreach ($dataRecipe as $row) {
            foreach ($row->recipe_detail as $recipeDetail) {
                if (!empty($recipeDetail->other_recipe_id)) {
                    $arrOtherRecipeId[$row->id][] = $recipeDetail->other_recipe_id;
                    $recipeMaster = [$recipeDetail->other_recipe];
                    $this->getAllOtherRecipeId($arrOtherRecipeId, $recipeMaster);
                }
            }
        }
    }
    
    protected function gePCCIDByRecipe($dataRecipe, $arrRecipeOtherId)
    {
        $arrPCCId = [];
        foreach ($dataRecipe as $row) {
            $arrPCCId[$row->id] = array_map('intval', explode(',', $row->master_pcc_ids));
        }
        
        // Mapping pccId parent for child
        foreach ($arrRecipeOtherId as $recipeIdParent => $arrRecipeIdChild){
            foreach ($arrRecipeIdChild as $recipeId) {
                $arrPCCId[$recipeId] = $arrPCCId[$recipeIdParent] ?? [];
            }
        }
        
        return $arrPCCId;
    }
}
