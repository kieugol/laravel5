<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 11/26/2018
 * Time: 11:47 AM
 */

namespace App\Admin\Controllers;


use App\Model\AppOrderTaker;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class AppOrderTakerController extends BaseController
{
    use ModelForm;

    public function __construct()
    {

    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Manage Order Taker Version');
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

            $content->header('Manage Order Taker');
            $content->description('Create');

            $content->body($this->form());
        });
    }

    /**
     * Edit interface.
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Manage Order Taker');
            $content->description('Edit');

            $content->body($this->form()->edit($id));
        });
    }

    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Manage Order Taker');
            $content->description('Show');

            $content->body($this->form()->view($id));
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(AppOrderTaker::class, function (Grid $grid) {
            $grid->disableRowSelector();
            $grid->disableExport();

            $grid->model()->orderBy("id", "desc");
            $grid->column('id', 'Id');
            $grid->column('order_taker_code', 'Order Taker Code');
            $grid->column('version', 'Version');
            $grid->column('base_url', 'Locate');
            $grid->column('file_name', 'Filename');
            $grid->column('description', 'Description');
            $grid->column('created_date', 'Created Date');

            // Filter
            $grid->filter(function ($filter) {
                $filter->like('order_taker_code', 'Order Taker Code');
                $filter->like('version', 'Version');
                $filter->between('created_date', 'Created date')->date([
                    'format' => 'YYYY-MM-DD',
                    'defaultDate' => date('Y-m-d')
                ]);
            });
        });
    }

    /**
     * @return Form
     */
    protected function form() {
        return Admin::form(AppOrderTaker::class, function (Form $form) {
            $form->text("order_taker_code", "Order Taker Code")->rules("required");
            $form->text("version", "Version")->rules("required");
            $form->hidden("base_url")->default(env('APP_URL') . "/upload/");
            $form->file("file_name")->move('order_taker');
            $form->text("description", "Description")->rules("required");
        });
    }

}