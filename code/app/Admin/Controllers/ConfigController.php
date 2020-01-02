<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
//use App\Admin\Extensions\Grid\CustomActions;
use App\Model\Config;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;

class ConfigController extends BaseController
{

    use ModelForm;
    private $_request;

    public function __construct(Request $request)
    {
        parent::__construct();
        $this->_request = $request;
//        $actions = new CustomActions();
//        $actions::$allowEdit = false;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Config');
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

            $content->header('Config');
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
        return Admin::grid(Config::class, function (Grid $grid) {

            $grid->actions(function ($actions) {
                $actions->disableDelete();
//                $actions->disableEdit();
            });

//            $grid->dis
            $grid->type();
            $grid->key();
            $grid->name();
            $grid->value();
            $grid->is_active();

            $grid->disableCreateButton();
            $grid->disableRowSelector();

            $grid->filter(function ($filter) {

                // Sets the range query for the created_at field
                $filter->equal('type')->select(Config::groupBy("type")->get()->pluck("type", "type"));
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
        if ($this->_request->method() == METHOD_PUT) {
            exec(EXEC_CLEAR_CACHE_POS_API);
        }
        return Admin::form(Config::class, function (Form $form) {

            $form->display("type");
            $form->display("key");
            $form->display("name");
            $form->textarea("value");
            $form->switch("is_active");

        });
    }
}
