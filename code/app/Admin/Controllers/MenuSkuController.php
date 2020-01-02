<?php

namespace App\Admin\Controllers;

use App\Model\MenuSku;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;


class MenuSkuController extends BaseController
{
    use ModelForm;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Sku');
            $content->description('List');
            $content->body($this->grid());
        });
    }

    /**
     * @return Form
     */
    protected function form()
    {
        return Admin::form(MenuSku::class, function (Form $form) {
            $form->text('plucode')->rules("required|unique:menu_sku,plucode", [
                'required' => 'Plucode can not empty.',
                'unique'   => 'Plucode has existed.',
            ]);
            $form->text('sku')->rules("required|unique:menu_sku,sku", [
                'required' => 'Sku can not empty.',
                'unique'   => 'Sku has existed.',
            ]);
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

            $content->header('Sku');
            $content->description('Create');
            $content->body($this->formcreate());
        });
    }

    /**
     * @return Form
     */
    protected function formcreate()
    {
        return Admin::form(MenuSku::class, function (Form $form) {
            $form->text('plucode')->rules("required|unique:menu_sku,plucode", [
                'required' => 'Plucode can not empty.',
                'unique'   => 'Code can not be less than 10 characters',
            ]);
            $form->text('sku')->rules("required");
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
            $content->header('Sku');
            $content->description('Edit');

            $content->body($this->formedit()->edit($id));
        });
    }

    /**
     * @return Form
     */
    protected function formedit()
    {
        return Admin::form(MenuSku::class, function (Form $form) {
            $form->text('plucode')->rules("required", [
                'required' => 'Plucode can not empty.',
            ]);
            $form->text('sku')->rules("required", [
                'required' => 'Sku can not empty.',
            ]);
        });
    }

    public function update($id)
    {
        return $this->formedit()->update($id);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(MenuSku::class, function (Grid $grid) {

            $grid->disableExport();

            $grid->id()->sortable();
            $grid->column('plucode', 'PluCode');
            $grid->column('sku', 'Sku');

            // Filter
            $grid->filter(function ($filter) {
                $filter->like('sku', 'Sku');
                $filter->like('plucode', 'PluCode');
            });
        });
    }
}
