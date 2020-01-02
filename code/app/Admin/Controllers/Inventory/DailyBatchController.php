<?php

namespace App\Admin\Controllers\Inventory;


use App\Admin\Controllers\BaseController;
use App\Model\Inventory\DailyBatch;
use App\Model\Inventory\MasterPCC;
use App\Repository\Inventory\DailyBatchRepository;
use App\Repository\Inventory\MasterPCCRepository;
use App\Repository\Inventory\MasterRecipeRepository;
use App\Repository\Inventory\MasterUomRepository;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DailyBatchController extends BaseController
{
    use ModelForm;

    protected $masterRecipeRep;
    protected $masterPCC;
    protected $daily_batchRep;
    protected $masterUomRep;

    public function __construct(
        MasterRecipeRepository $masterRecipeRep,
        DailyBatchRepository $dailyBatchRep,
        MasterPCCRepository $masterPCC,
        MasterUomRepository $masterUomRep
    )
    {
        parent::__construct();
        $this->masterRecipeRep = $masterRecipeRep;
        $this->daily_batchRep  = $dailyBatchRep;
        $this->masterPCC       = $masterPCC;
        $this->masterUomRep    = $masterUomRep;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Daily Batch');
            $content->description('List');

            $content->body($this->grid());
        });
    }


    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('Daily Prep');
            $content->description('Create');
            $recipes = $this->masterRecipeRep->getAllRecipeForDailyBatch();
            $data    = [
                'recipes' => $recipes
            ];
            $view    = view("inventory.daily_batch.form", $data);
            $content->body($view);
        });

    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $daily_batch_uoms = [];
        $daily_batchs  = $this->daily_batchRep->getUomByRecipeId();
        foreach ($daily_batchs as $daily_batch) {
            $daily_batch_uoms[$daily_batch->daily_batch_id] = $daily_batch->uom_name;
        }
        return Admin::grid(DailyBatch::class, function (Grid $grid) use ($daily_batch_uoms) {
            $grid->disableRowSelector();
            $grid->disableExport();
            $grid->disableActions();
            $grid->model()->orderBy('id', 'DESC');
            $grid->id('ID')->sortable();
            $grid->column('recipe.name', 'Recipe')->sortable();
            $grid->quantity('Quantity')->sortable();
            $grid->column('quantity_result', 'Result')->display(function () use ($daily_batch_uoms){
                return $this->quantity*$this->quantity_result;
            })->sortable();
            $grid->column('recipe_id', 'Uom')->display(function () use ($daily_batch_uoms){
                return $daily_batch_uoms[$this->id];
            })->sortable();

            $grid->created_date()->sortable();

            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field

                $filter->between('created_date', 'Created Time')->datetime();
            });
        });
    }

    public function save(Request $request)
    {
        $data      = $request->all();
        $pccDetail = $this->masterPCC->getByCurrentDate();
        
        $this->daily_batchRep->create([
            'recipe_id'     => $data['recipe'],
            'master_pcc_id' => $pccDetail->id ?? 0,
            'quantity'      => $data['quantity'],
            'is_active'     => STATUS_ACTIVE
        ]);

        return response([
            'status'  => true,
            'message' => trans('message.created_successfully'),
            'data'    => '',
        ], Response::HTTP_OK);
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(DailyBatch::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->select("recipe_id", "Recipe")->options($this->masterRecipeRep->getAllRecipeForDailyBatch()->pluck('name', 'id'))->rules("required");
            $form->number("quantity", "Quantity");
            $form->hidden("is_active")->default(STATUS_ACTIVE);
        });
    }
}
