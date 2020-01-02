<?php

namespace App\Admin\Controllers;


use App\Admin\Controllers\BaseController;
use App\Model\Sku;
use App\Model\Uom;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class SkuController extends BaseController {

    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index() {
        return Admin::content(function (Content $content) {

                    $content->header('SKU');
                    $content->description('List');

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

                    $content->header('SKU');
                    $content->description('Edit');

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

                    $content->header('SKU');
                    $content->description('Create');

                    $content->body($this->form());
                });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid() {
        return Admin::grid(Sku::class, function (Grid $grid) {

                    $grid->id('ID')->sortable();
                    $grid->skucode('Skucode');
                    $grid->shortname('Short Name')->sortable();
                    $grid->longname('Long Name')->sortable();
                    $grid->description('Description')->sortable();
                    $grid->current_price('Current Price')->sortable();
                    $grid->uom()->name();

                    $grid->created_at();
                    $grid->updated_at();

                    $grid->filter(function ($filter) {
                        // Sets the range query for the created_at field
                        $filter->like('shortname', 'Short Name');
                        $filter->like('longname', 'Long Name');
                        $filter->between('created_at', 'Created Time')->datetime();
                    });
                });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form() {
        return Admin::form(Sku::class, function (Form $form) {

                    $form->display('id', 'ID');

                    $form->text("skucode", "Skucode")->rules("required");
                    $form->text("shortname", "Short Name")->rules("required");
                    $form->text("longname", "Long Name")->rules("required");
                    $form->text("description", "Description")->rules("required");
                    $form->text("current_price", "Current Price")->rules("required");
                    $form->select("uom_id", "UOM")->options(Uom::all()->pluck('name', 'id'))->rules("required");
                    $form->datetime("last_count_date", "Last count Date")->rules("required");

                    $form->display('created_at', 'Created At');
                    $form->display('updated_at', 'Updated At');
                });
    }

}
