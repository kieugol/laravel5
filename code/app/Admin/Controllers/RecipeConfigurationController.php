<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
use App\Model\RecipeConfiguration;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use App\Model\Sku;
use App\Model\Uom;

class RecipeConfigurationController extends BaseController {

    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index() {
        return Admin::content(function (Content $content) {

                    $content->header('header');
                    $content->description('description');

                    $content->body($this->grid());
                });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id) {
        return Admin::content(function (Content $content) use ($id) {

                    $content->header('header');
                    $content->description('description');

                    $content->body($this->form()->edit($id));
                });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create() {
        return Admin::content(function (Content $content) {

                    $content->header('header');
                    $content->description('description');

                    $content->body($this->form());
                });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid() {
        return Admin::grid(RecipeConfiguration::class, function (Grid $grid) {

                    $grid->id()->sortable();
                    $grid->plucode()->sortable();
                    $grid->skucode()->sortable();
                    $grid->qty()->sortable();
                    $grid->uom()->name();

                    $grid->created_at();
                    $grid->updated_at();
                });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form() {
        return Admin::form(RecipeConfiguration::class, function (Form $form) {

                    $form->display('id', 'ID');

                    $form->text("plucode", "plucode")->rules("required");
                    $form->text("qty", "qty")->rules("required|integer");
                    $form->select("skucode", "SKU")->options(Sku::all()->pluck('skucode', 'id'))->rules("required");
                    $form->select("uom_id", "UOM")->options(Uom::all()->pluck('name', 'id'))->rules("required");
//                    $form->datetime("last_count_date", "Last count Date")->rules("required");

                    $form->display('created_at', 'Created At');
                    $form->display('updated_at', 'Updated At');
                });
    }

}
