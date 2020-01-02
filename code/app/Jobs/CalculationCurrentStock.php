<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repository\Inventory\CurrentStockRepository;
use App\Repository\Inventory\MasterRecipeRepository;
use App\Repository\Inventory\MasterMaterialDetailUsageRepository;

class CalculationCurrentStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $params;
    private $current_stock_repository;
    private $recipe_repository;
    private $material_detail_usage_repository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        CurrentStockRepository $current_stock_repository,
        MasterRecipeRepository $recipe_repository,
        MasterMaterialDetailUsageRepository $material_detail_usage_repository
    )
    {
        $this->current_stock_repository         = $current_stock_repository;
        $this->recipe_repository                = $recipe_repository;
        $this->material_detail_usage_repository = $material_detail_usage_repository;

        $params   = $this->params;
        $order_id = $params['order_id'];
        $sku      = $params['sku'];

        // Get master recipe
        $recipe           = $this->recipe_repository->getBySku($sku);
        $material_details = [];
        $this->getAllMaterialDetail($material_details, $recipe->recipe_detail);
        $material_detail_ids = array_keys($material_details);

        // Get current stock by material detail ids
        $current_stocks = $this->current_stock_repository->getByMaterialDetailIds($material_detail_ids);
        foreach ($current_stocks as $current_stock) {
            $material_detail = $material_details[$current_stock->material_detail_id];
            $usage           = $material_detail->usage;

            $data = [
                'quantity_store' => $current_stock->quantity_store - $usage
            ];
            $this->current_stock_repository->update($data, $current_stock->id);

            $data_insert = [
                'order_id'           => $order_id,
                'material_detail_id' => $material_detail->material_detail_id,
                'usage'              => $material_detail->usage,
                'price'              => $material_detail->material_detail->price,
                'total'              => $material_detail->usage * $material_detail->material_detail->price
            ];
            $this->material_detail_usage_repository->insert($data_insert);
        }

    }

    public function getAllMaterialDetail(&$material_details, $recipe_details)
    {
        foreach ($recipe_details as $recipe_detail) {
            if (!empty($recipe_detail->material_detail_id)) {
                $material_details[$recipe_detail->material_detail_id] = $recipe_detail;
            } elseif (!empty($recipe_detail->other_recipe->recipe_detail)) {
                $this->getAllMaterialDetail($material_details, $recipe_detail->other_recipe->recipe_detail);
            }
        }
    }
}
