<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
//use App\Admin\Extensions\Grid\CustomActions;
use App\Model\OrderLogStatus;
use App\Model\OrderStatus;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class OrderLogStatusController extends BaseController
{

    use ModelForm;

//    public function __construct()
//    {
//        parent::__construct();
//
//        $actions = new CustomActions();
//        $actions::$allowEdit = false;
//    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Order Status History Log');
            $content->description('List');
            $content->body("<style>.grid-row-delete{display:none !important}</style>");
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

            $content->header('Order Status History Log');
            $content->description('Edit');

            $content->body($this->form()->edit($id));
        });
    }

    public function destroy($id)
    {
        return response()->json([
            'status'  => false,
            'message' => "Can not delete config",
        ]);
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        redirect("config");
//        return Admin::content(function (Content $content) {
//
//            $content->header('Menu Auto Cook');
//            $content->description('Create');
//
//            $content->body($this->form());
//        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(OrderLogStatus::class, function (Grid $grid) {
            $list_status = OrderStatus::all()->pluck("id", "name");

            $grid->model()->orderBy("id", "desc");

            $grid->id();
            $grid->order_id();

            $grid->status_old()->display(function($status){
                $list_status = OrderStatus::getList();
                return isset($list_status[$status]) ? $list_status[$status] : $status;
            });

            $grid->status_new()->display(function($status){
                $list_status = OrderStatus::getList();
                return isset($list_status[$status]) ? $list_status[$status] : $status;
            });

            $grid->reason();
            $grid->device();
            $grid->job_status();
            $grid->created_date();

            $grid->disableCreateButton();
            $grid->disableRowSelector();

            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                $filter->equal('order_id');
                $filter->equal('status_old');
                $filter->equal('status_new');
                $filter->equal('device');
                $filter->equal('job_status');
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
        return Admin::form(OrderLogStatus::class, function (Form $form) {

//            $form->display("type");
//            $form->display("key");
//            $form->display("name");
//            $form->textarea("value");
//            $form->switch("is_active");

        });
    }

}
