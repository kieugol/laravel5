<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:11 PM
 */

namespace App\Http\Controllers\Api\Inventory;

use App\Admin\Controllers\BaseController;
use App\Libraries\Api;
use App\Repository\Inventory\DailyBatchRepository;
use App\Repository\Inventory\MasterRecipeRepository;

class DailyBatchController extends BaseController
{
    private $daily_batch_repository;
    private $master_recipe_repository;

    public function __construct(
        DailyBatchRepository $daily_batch_repository,
        MasterRecipeRepository $master_recipe_repository
    )
    {
        parent::__construct();
        $this->daily_batch_repository   = $daily_batch_repository;
        $this->master_recipe_repository = $master_recipe_repository;
    }

    public function getList()
    {
        $option  = $this->request->all();
        $filters = [];
        if (isset($option['is_active'])) {
            $filters['is_active'] = $option['is_active'];
        }
        if (isset($option['recipe_id'])) {
            $filters['recipe_id'] = $option['recipe_id'];
        }

        $period = [];
        if (isset($option['created_date'])) {
            $period['fromDate'] = "{$option['created_date']} 00:00:00";
            $period['toDate'] = "{$option['created_date']} 59:59:59";
        }

        $searches = [];
        if (!empty($option['search_key']) && !empty($option['search_value'])) {
            $arr_search_key = explode(',', $option['search_key']);
            foreach ($arr_search_key as $search_key) {
                $searches[$search_key] = $option['search_value'];
            }
        }

        $sort = [];
        if (!empty($option['field']) && !empty($option['type'])) {
            $sort[$option['field']] = $option['type'];
        }
        
        $limit = isset($option['limit']) ? $option['limit'] : 10;
        $offset = isset($option['offset']) ? $option['offset'] : 0;
        
        $data     = $this->daily_batch_repository->getDataTableApi($filters, $searches, $sort, $period, $limit, $offset);
        $daily_batch_uoms = [];
        $daily_batch_recipes = [];
        $daily_batchs  = $this->daily_batch_repository->getUomByRecipeId();
        foreach ($daily_batchs as $daily_batch) {
            $daily_batch_recipes[$daily_batch->daily_batch_id] = $daily_batch->recipe_name;
            $daily_batch_uoms[$daily_batch->daily_batch_id] = $daily_batch->uom_name;
        }
        
        foreach ($data['items'] as &$item) {
            $item->recipe_name = $daily_batch_recipes[$item->id];
            $item->uom_name    = $daily_batch_uoms[$item->id];
        }
        $response = [
            'message' => '',
            'data'    => $data,
        ];
        return Api::response($response);
    }

    public function getAll()
    {
        $data     = [
            'recipes' => $this->master_recipe_repository->getAllRecipeForDailyBatch(),
        ];
        $response = [
            'message' => '',
            'data'    => $data,
        ];

        return Api::response($response);
    }

    public function create()
    {
        $this->validate($this->request, [
            "recipe_id" => 'required|numeric|exists:inventory_master_recipe,id',
            "quantity"  => 'required|numeric',
            "is_active" => 'required'
        ]);

        $data       = $this->request->all();
        $created_by = 0;

        $daily_batch = [
            "recipe_id"  => $data["recipe_id"],
            "quantity"   => $data["quantity"],
            "is_active"  => $data["is_active"],
            'created_by' => $created_by
        ];

        $response = [
            'message' => 'Created Successfully.',
        ];

        try {
            $this->daily_batch_repository->create($daily_batch);
        } catch (\Exception $ex) {
            $response = [
                'message' => $ex->getMessage(),
            ];
        }

        return Api::response($response);
    }

    public function getDetail($id)
    {
        $daily_batch = $this->daily_batch_repository->find($id);
        $recipes     = $this->master_recipe_repository->all();
        $data        = [
            'id'          => $daily_batch->id,
            'recipe_id'   => $daily_batch->recipe_id,
            'recipe_name' => $daily_batch->recipe->name,
            'quantity'    => $daily_batch->quantity,
            'is_active'   => $daily_batch->is_active,
            'recipes'     => $recipes
        ];
        unset($daily_batch->recipe);
        $response = [
            'message' => '',
            'data'    => $data
        ];

        return Api::response($response);
    }

    public function update($id)
    {
        $this->validate($this->request, [
            "recipe_id" => 'required|numeric|exists:inventory_master_recipe,id',
            "quantity"  => 'required|numeric',
            "is_active" => 'required'
        ]);

        $data       = $this->request->all();
        $updated_by = 0;

        $daily_batch = [
            "recipe_id"  => $data["recipe_id"],
            "quantity"   => $data["quantity"],
            "is_active"  => $data["is_active"],
            'created_by' => $updated_by
        ];

        $response = [
            'message' => 'Updated Successfully.',
        ];

        try {
            $this->daily_batch_repository->update($daily_batch, $id);
        } catch (\Exception $ex) {
            $response = [
                'message' => $ex->getMessage() . $ex->getLine(),
            ];
        }

        return Api::response($response);
    }

    public function delete($id)
    {
        $response = [
            'message' => 'Deleted Successfully.',
        ];

        try {
            $this->daily_batch_repository->destroy($id);
        } catch (\Exception $ex) {
            $response = [
                'message' => $ex->getMessage() . $ex->getLine(),
            ];
        }

        return Api::response($response);
    }

}
