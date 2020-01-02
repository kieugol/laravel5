<?php

namespace App\Admin\Controllers\Inventory;


use App\Admin\Controllers\BaseController;
use App\Helpers\PosHelper;
use App\Model\Inventory\DailyBatch;
use App\Model\Inventory\MasterPCC;
use App\Model\Inventory\WastedMaterial;
use App\Repository\Inventory\DailyBatchRepository;
use App\Repository\Inventory\MasterMaterialDetailRepository;
use App\Repository\Inventory\MasterPCCRepository;
use App\Repository\Inventory\MasterRecipeRepository;
use App\Repository\Inventory\MasterUomRepository;
use App\Repository\Inventory\WastedMaterialRepository;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WastedMaterialController extends BaseController
{
    use ModelForm;

    protected $masterMaterialDetailRep;
    protected $wastedMaterialRep;
    protected $uomRep;

    protected $masterPCC;

    public function __construct(
        MasterMaterialDetailRepository $masterMaterialDetailRep,
        MasterPCCRepository $masterPCC,
        WastedMaterialRepository $wastedMaterialRep,
        MasterUomRepository $uomRep
    )
    {
        parent::__construct();
        $this->masterMaterialDetailRep  = $masterMaterialDetailRep;
        $this->masterPCC                = $masterPCC;
        $this->wastedMaterialRep        = $wastedMaterialRep;
        $this->uomRep                   = $uomRep;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Wasted Material');
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
            $content->header('Wasted Material');
            $content->description('Create');
            $material_details = $this->masterMaterialDetailRep->getAllMaterialDetail();

            $data    = [
                'material_details' => $material_details
            ];
            $view    = view("inventory.wasted_material.form", $data);
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
        $uomRep = $this->uomRep;
        return Admin::grid(WastedMaterial::class, function (Grid $grid) use ($uomRep){
            $grid->disableRowSelector();
            $grid->disableExport();
            $grid->disableActions();
            $grid->model()->orderBy('id', 'DESC');
            $grid->id('id')->sortable();
            $grid->column('material_detail.code', 'Material Code')->sortable();
            $grid->column('material_detail.name', 'Material Name')->sortable();
            $grid->quantity('Quantity')->sortable();
            $grid->column('UOM')->display(function () use ($uomRep){
                $uom_id = $this->material_detail['report_uom_id'];
                $uom    = $uomRep->getById($uom_id);

                return isset($uom->name) ? $uom->name : '';
            });
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

        $this->wastedMaterialRep->create([
            'material_detail_id'    => $data['material'],
            'master_pcc_id'         => $pccDetail->id ?? 0,
            'quantity'              => $data['quantity'],
            'created_by'            => PosHelper::getCurrentUser('id'),
            'updated_by'            => PosHelper::getCurrentUser('id')
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
