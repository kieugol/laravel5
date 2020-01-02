<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
//use App\Admin\Extensions\Grid\CustomActions;
use App\Model\ApiAccessLogs;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ApiAccessLogsController extends BaseController
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

            $content->header('Log Access Api');
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

            $content->header('Log Access Api');
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
        redirect("home");
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
        return Admin::grid(ApiAccessLogs::class, function (Grid $grid) {
            $grid->model()->orderBy("id", "desc");

            $grid->id();
            $grid->uri();
            $grid->method();

            $grid->params();
            $grid->output();
            $grid->rtime();
            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->disableActions();

            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                $filter->like('uri');
                $filter->equal('method');
                $filter->like('params');
                $filter->like('output');
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
        return Admin::form(ApiAccessLogs::class, function (Form $form) {

//            $form->display("type");
//            $form->display("key");
//            $form->display("name");
//            $form->textarea("value");
//            $form->switch("is_active");

        });
    }

}
