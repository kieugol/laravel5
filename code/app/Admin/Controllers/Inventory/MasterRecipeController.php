<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 2/11/2019
 * Time: 9:33 AM
 */

namespace App\Admin\Controllers\Inventory;

use App\Admin\Controllers\BaseController;
use App\Helpers\PosHelper;
use App\Model\Inventory\MasterRecipe;
use App\Repository\Inventory\{MasterMaterialRepository,
    MasterRecipeDetailRepository,
    MasterRecipeRepository,
    MasterUomRepository,
    RecipeLogRepository};
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Response;

class MasterRecipeController extends BaseController
{
    use ModelForm;

    private $masterRecipeRepository;
    private $masterRecipeDetailRepository;
    private $masterUomRepository;
    private $masterMaterialRepository;
    private $recipeLogRepository;

    public function __construct(
        MasterRecipeRepository $masterRecipeRepository,
        MasterRecipeDetailRepository $masterRecipeDetailRepository,
        MasterUomRepository $masterUomRepository,
        MasterMaterialRepository $masterMaterialRepository,
        RecipeLogRepository $recipeLogRepository
    )
    {
        parent::__construct();
        $this->masterRecipeRepository       = $masterRecipeRepository;
        $this->masterRecipeDetailRepository = $masterRecipeDetailRepository;
        $this->masterUomRepository          = $masterUomRepository;
        $this->masterMaterialRepository     = $masterMaterialRepository;
        $this->recipeLogRepository          = $recipeLogRepository;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Recipe Substitution');
            $content->description('List');
            $content->body($this->grid());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(MasterRecipe::class, function (Grid $grid) {
            $grid->disableRowSelector();
            $grid->disableExport();
            $grid->disableCreateButton();

            $grid->model()->orderBy("id", "desc");
            $grid->code('Code')->sortable();
            $grid->plucode('Plucode')->sortable();
            $grid->name('Name')->sortable();
            $grid->usage('Usage')->sortable();
            $grid->column('uom.name', 'Uom')->sortable();
            $grid->expired_in('Expired In')->sortable();
            $grid->price('Price')->sortable();
            $grid->updated_date('Updated Date')->sortable();

            $grid->actions(function ($actions) {
                $actions->disableView();
                $actions->disableDelete();
            });

            // Filter
            $grid->filter(function ($filter) {
                $filter->like('code', 'Code');
                $filter->like('name', 'Name');
                $filter->between('created_date', 'Create date')->date([
                    'format'      => 'YYYY-MM-DD',
                    'defaultDate' => date('Y-m-d')
                ]);
            });
        });
    }

    /**
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Master Recipe');
            $content->description('Edit');

            $content->body($this->formEdit($id));
        });
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function formEdit($id)
    {
        $recipe         = $this->masterRecipeRepository->find($id);
        $recipe_details = $this->masterRecipeDetailRepository->findByAttributes([
            'recipe_id' => $recipe->id
        ]);
        $uoms           = $this->masterUomRepository->findByAttributes([
            'is_active' => STATUS_ACTIVE
        ]);
        $materials      = $this->masterMaterialRepository->findByAttributes([
            'is_active' => STATUS_ACTIVE
        ]);
        $recipes        = $this->masterRecipeRepository->findByAttributes([
            'is_active' => STATUS_ACTIVE
        ]);
        $data           = [
            'action'         => ACTION_VIEW,
            'recipe'         => $recipe,
            'recipe_details' => $recipe_details,
            'uoms'           => $uoms,
            'materials'      => $materials,
            'recipes'        => $recipes
        ];

        return view("inventory.master_recipe.form", $data);
    }

    public function updateRecipeDetail()
    {
        $this->validate($this->request, [
            "recipe_detail_id" => 'required|numeric',
            "usage"            => 'required|numeric',
            "status_id"        => 'required|numeric',
        ]);
        $data = $this->request->all();
        // Write log if have any update
        $recipe_detail = $this->masterRecipeDetailRepository->find($data['recipe_detail_id']);
        if ($recipe_detail->material_id != $data['material_id'] || $recipe_detail->other_recipe_id != $data['other_recipe_id'] || $recipe_detail->usage != $data['usage']) {
            $this->recipeLogRepository->insert([
               'recipe_id'            => $data['recipe_id'],
               'material_from_id'     => $recipe_detail->material_id,
               'material_to_id'       => isset($data['material_id']) ? $data['material_id'] : null,
               'other_recipe_from_id' => $recipe_detail->other_recipe_id,
               'other_recipe_to_id'   => isset($data['other_recipe_id']) ? $data['other_recipe_id'] : null,
               'usage_from'           => $recipe_detail->usage,
               'usage_to'             => $data['usage'],
               'created_by'           => PosHelper::getCurrentUser('id'),
               'updated_by'           => PosHelper::getCurrentUser('id')
            ]);
        }
        try {
            $this->masterRecipeDetailRepository->update([
                "recipe_id"   => $data['recipe_id'],
                "material_id" => isset($data['material_id']) ? $data['material_id'] : null,
                "other_recipe_id" => isset($data['other_recipe_id']) ? $data['other_recipe_id'] : null,
                "usage"       => $data['usage'],
                "is_active"   => $data['status_id']
            ], $data['recipe_detail_id']);
        } catch (\Exception $exc) {
            return response()->json(['message' => $exc->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response([
            'status'  => true,
            'message' => "Updated recipe detail successfully.",
            'data'    => '',
        ], Response::HTTP_OK);
    }

    public function getRecipeDetailByRecipeId($recipe_id)
    {
        $recipe_details = $this->masterRecipeDetailRepository->findByAttributes([
            'recipe_id' => $recipe_id
        ]);
        
        foreach ($recipe_details as &$item) {
            $item->recipe_detail_code = empty($item->material_id) ? $item->other_recipe->code :  $item->material->code;
            $item->recipe_detail_name = empty($item->material_id) ? $item->other_recipe->name :  $item->material->name;
            $item->uom                = empty($item->material_id) ? $item->other_recipe->uom->name :  $item->material->recipe_uom->name;
            $item->price              = empty($item->material_id) ? $item->other_recipe->price :  $item->material->price;
            unset($item->material);
            unset($item->other_recipe);
        }
    
        return response([
            'status'  => true,
            'message' => "Get recipe detail successfully.",
            'data'    => $recipe_details
        ], Response::HTTP_OK);
    }

}
