<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
use App\Model\UserDriver;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class UserDriverController extends BaseController
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

            $content->header('Driver');
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

            $content->header('Driver');
            $content->description('edit');

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

            $content->header('Driver');
            $content->description('create');

            $content->body($this->form());
        });
    }

//    public function update(){
//        //print_r($_POST); exit;
//        parent::update();
//    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        Admin::script(view("module.userdriver.js")->render());

        return Admin::grid(UserDriver::class, function (Grid $grid) {
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disableFilter();
            $grid->disableCreateButton();
            $grid->disableExport();

            $grid->id('ID')->sortable();

            $grid->user()->name();

            $grid->code()->sortable();

//            $grid->status("Status")->display(function(){
//                //$model = $grid->model();
//                return $model->status == 0 ? "<span class='text-success'>Available</span>" : "<span class='text-danger'>En Route</span>";
//            });
            $grid->status("Status")->editable('select', [0 => 'Available', 1 => 'En Route'])->sortable();
//            $grid->bo
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(UserDriver::class, function (Form $form) {
            $form->display('id', 'ID');

                    $form->text('status');
//                    $form->text('shortname', 'shortname')->rules("required");
//                    $form->text('longname', 'longname')->rules("required");
//                    $form->text('description', 'description')->rules("required");

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

}
