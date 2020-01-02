<?php

namespace App\Admin\Controllers;


use App\Admin\Controllers\BaseController;
use App\Model\ShiftTransaction;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ShiftTransactionController extends BaseController
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

            $content->header('Shift Transaction');
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

            $content->header('Shift Transaction');
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

            $content->header('Shift Transaction');
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
        return Admin::grid(ShiftTransaction::class, function (Grid $grid) {
            $grid->model()->orderBy("id", "desc");

            $grid->shift_id();
            $grid->user()->name("User");
            $grid->petty_cash();
            $grid->sale_cash();
            $grid->real_cash();
            $grid->variant_cash();
            $grid->is_open()->display(function($is_open){
                return $is_open ? "<span class='label label-success'>open</span>" : "<span class='label label-default'>closed</span>";
            });

            $grid->start_time();
            $grid->end_time();

            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->disableActions();

            $grid->filter(function ($filter) {
                $filter->equal('shift_id');
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
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
