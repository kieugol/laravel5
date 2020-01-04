<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Admin\Controllers\BaseController;
use App\Helpers\ConfigHelp;
use App\Libraries\Api;
use App\Repository\Inventory\MasterMaterialRepository;
use App\Repository\Inventory\MasterRecipeDetailRepository;
use App\Repository\Inventory\MasterRecipeRepository;
use App\Repository\Inventory\MasterUomDetailRepository;
use App\Repository\Inventory\MasterUomRepository;
use App\Repository\Inventory\StockOpnameRecipeRepository;
use App\Repository\Inventory\MasterMaterialUsageRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MasterRecipeController extends BaseController
{
    private $recipeRep;
    private $recipeDetailRep;
    private $stockOpnameRecipeRep;
    private $masterUomDetailRep;
    private $masterUomRep;
    private $master_material_usage_rep;
    private $masterMaterialRep;
    
    public function __construct(
        MasterRecipeRepository $masterRecipeRepository,
        MasterRecipeDetailRepository $masterRecipeDetailRepository,
        StockOpnameRecipeRepository $stockOpnameRecipeRep,
        MasterUomDetailRepository $masterUomDetailRep,
        MasterUomRepository $masterUomRep,
        MasterMaterialUsageRepository $master_materia_usage_rep,
        MasterMaterialRepository $masterMaterialRepository
    )
    {
        parent::__construct();
        $this->recipeRep                 = $masterRecipeRepository;
        $this->recipeDetailRep           = $masterRecipeDetailRepository;
        $this->stockOpnameRecipeRep      = $stockOpnameRecipeRep;
        $this->masterUomRep              = $masterUomRep;
        $this->masterUomDetailRep        = $masterUomDetailRep;
        $this->master_material_usage_rep = $master_materia_usage_rep;
        $this->masterMaterialRep         = $masterMaterialRepository;
    }
    
    public function getForCreateStockOpname()
    {
        $uom_detail_query = $this->masterUomDetailRep->all();
        $uom_detail       = object_key_column($uom_detail_query, 'id');
        // Get what recipe already created with material detail id before
        $stock_opname_recipe = $this->stockOpnameRecipeRep->getMappingRecipeDataByStockOpnameId(0);
        $recipe_data         = $this->recipeRep->getForCreateStockOpname($stock_opname_recipe);
        $arrayMaterialId     = array_unique(array_column($recipe_data, 'material_id'));
        $dataUsageMaterial   = $this->master_material_usage_rep->getTotalUsageByMaterialIds($arrayMaterialId);
        
        foreach ($recipe_data as &$row) {
            foreach ($row['material_details'] as &$row_material_detail) {
                // Conversion to report uom
                $final_conversion_rate = 1;
                $arr_uoms              = [
                    [
                        'key'   => $row_material_detail['outlet_uom_id'],
                        'value' => $row_material_detail['recipe_uom_detail_id']
                    ],
                    [
                        'key'   => $row_material_detail['smaller_uom_id'],
                        'value' => $row_material_detail['outlet_uom_detail_id']
                    ],
                    [
                        'key'   => $row_material_detail['supplier_uom_id'],
                        'value' => $row_material_detail['smaller_uom_detail_id']
                    ],
                ];
                
                $condition_uom_id = $row_material_detail['report_uom_id'];
                foreach ($arr_uoms as $arr_uom) {
                    $final_conversion_rate *= $uom_detail[$arr_uom['value']]->conversion_rate;
                    if ($condition_uom_id == $arr_uom['key']) {
                        break;
                    }
                    
                }
                $row_material_detail['final_conversion_rate'] = $final_conversion_rate;
            }
            $row['total_usage_material'] = $dataUsageMaterial[$row['material_id']] ?? 0;
        }
        
        $response['data'] = $recipe_data;
        return Api::response($response);
    }
    
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        $option  = $this->request->all();
        $filters = [];
        $period  = [];
        
        $arr_filters = ConfigHelp::checkIsset(['code', 'name', 'plucode', 'sku', 'created_date'], $option);
        if (!empty($arr_filters)) {
            foreach ($arr_filters as $key) {
                if ($key == 'created_date') {
                    $period = $option[$key];
                } else {
                    $filters[$key] = $option[$key];
                }
            }
        }
        
        $searches = [];
        if (!empty($option['search_key']) && !empty($option['search_value'])) {
            $arr_search_key   = explode(',', $option['search_key']);
            $arr_search_value = explode(',', $option['search_value']);
            foreach ($arr_search_key as $key=>$value) {
                $searches[$value] = $arr_search_value[$key];
            }
        }
        
        $sort = [];
        if (!empty($option['field']) && !empty($option['code'])) {
            $sort[$option['field']] = $option['code'];
        }
        
        $data     = $this->recipeRep->getDataTableApi($filters, $searches, $sort, $period, $option['limit'], $option['offset']);
        foreach ($data['items'] as &$item) {
            $item->uom_name = $item->uom->name;
            unset($item->uom);
        }
        $response = [
            'message' => 'success',
            'data'    => $data,
        ];
        
        return Api::response($response);
    }
    
    public function getDetail($id)
    {
        $recipe           = $this->recipeRep->find($id);
        $recipe->uom_name = $recipe->uom->name;
        unset($recipe->uom);
        $recipe_details = $this->recipeDetailRep->findByAttributes([
            'recipe_id' => $recipe->id
        ]);
        foreach ($recipe_details as &$item) {
            $item->code     = empty($item->material_id) ? $item->other_recipe->code : $item->material->code;
            $item->name     = empty($item->material_id) ? $item->other_recipe->name : $item->material->name;
            $item->uom_name = empty($item->material_id) ? $item->other_recipe->uom->name : $item->material->recipe_uom->name;
            $item->price    = empty($item->material_id) ? $item->other_recipe->price : $item->material->price;
            unset($item->material);
            unset($item->other_recipe);
        }
        $uoms      = $this->masterUomRep->findByAttributes([
            'is_active' => STATUS_ACTIVE
        ]);
        $materials = $this->masterMaterialRep->findByAttributes([
            'is_active' => STATUS_ACTIVE
        ]);
        $recipes   = $this->recipeRep->findByAttributes([
            'is_active' => STATUS_ACTIVE
        ]);
        
        $data = [
            'recipe'         => $recipe,
            'recipe_details' => $recipe_details,
            'uoms'           => $uoms,
            'materials'      => $materials,
            'recipes'        => $recipes
        ];
        
        $response = [
            'message' => 'success',
            'data'    => $data
        ];
        return Api::response($response);
    }
    
    public function updateRecipeDetail()
    {
        $this->validate($this->request, [
            "material_id"      => 'required|numeric',
            "recipe_detail_id" => 'required|numeric',
            "other_recipe_id"  => 'required|numeric',
            "usage"            => 'required|numeric',
            "status_id"        => 'required|numeric',
        ]);
        $data = $this->request->all();
        DB::beginTransaction();
        $this->recipeDetailRep->update([
            "material_id"     => $data['material_id'] ?? null,
            "other_recipe_id" => $data['other_recipe_id'] ?? null,
            "usage"           => $data['usage'],
            "is_active"       => $data['status_id']
        ], $data['recipe_detail_id']);
        DB::commit();
        return response([
            'status'  => true,
            'message' => "Updated recipe detail successfully.",
            'data'    => '',
        ], Response::HTTP_OK);
    }
}
