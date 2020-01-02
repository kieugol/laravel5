<?php

namespace App\Admin\Controllers;


use App\Admin\Controllers\BaseController;
use App\Model\Sku;
use App\Model\SkuQuantity;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class SkuQuantityController extends BaseController
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('SKU Quantity');
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
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('SKU Quantity');
            $content->description('Edit');

            $content->body($this->form()->edit($id));
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

            $content->header('SKU Quantity');
            $content->description('Create');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(SkuQuantity::class, function (Grid $grid) {

            $grid->skucode()->sortable();
            $grid->current_vendor()->sortable();
            $grid->current_qty()->sortable();
            $grid->qty_sold()->sortable();
            $grid->last_count_date()->sortable();
            $grid->last_count_time()->sortable();

//            $grid->created_at();
//            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(SkuQuantity::class, function (Form $form) {

            $form->select('skucode')->options(Sku::all()->pluck("skucode", "skucode"))->rules("required");
            $form->text('current_vendor', 'current_vendor')->rules("required");
            $form->text('current_qty', 'current_qty')->rules("required");
            $form->text('qty_sold', 'qty_sold')->rules("required");
            $form->dateRange('last_count_date', 'last_count_date')->rules("required");
            $form->datetime('last_count_time', 'last_count_time')->rules("required");

//            $form->display('created_at', 'Created At');
//            $form->display('updated_at', 'Updated At');
        });
    }
}
