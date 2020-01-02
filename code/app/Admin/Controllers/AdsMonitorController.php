<?php

namespace App\Admin\Controllers;

use App\Model\AdsMonitor;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;

class AdsMonitorController extends BaseController
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

            $content->header('Advertise Takeaway Monitor');
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

            $content->header('Advertise Takeaway Monitor');
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

            $content->header('Advertise Takeaway Monitor');
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
        return Admin::grid(AdsMonitor::class, function (Grid $grid) {

            $grid->model()->orderBy("is_actived", "desc");
            $grid->model()->orderBy("sequence", "asc");
            $grid->model()->orderBy("id", "desc");
            $grid->id()->sortable();
            $grid->code();
            $grid->column('column', 'File Name')->display(function () {
                return $this->filename;
            });
            $grid->column('filename', 'Thumbnail')->display(function($filename){
                return view("module.adsmonitor.filename", array("filename" => $filename))->render();
            });
            $grid->sequence()->sortable();
            $grid->created_date();
            $grid->is_actived("Is Active")->switch()->editable();
            $grid->filter(function ($filter) {
                $filter->like('code', 'Code');
            });
            $grid->filter(function ($filter) {
                $filter->like('filename', 'File Name');
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
        return Admin::form(AdsMonitor::class, function (Form $form) {
            $form->file('filename')->move('ads_monitor');
            $form->hidden("code")->value('1234');
            $form->hidden("base_url")->default(env('APP_URL') . "/upload/");
            $form->switch("is_actived", "Is Active");
            $form->text("sequence");
        });
    }

}
