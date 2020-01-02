<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 11/9/2018
 * Time: 9:49 AM
 */

namespace App\Admin\Controllers;

use App\Model\AddressOthers;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class AddressOthersController extends BaseController
{
    use ModelForm;
    public function index(){
        return Admin::content(function (Content $content) {

            $content->header('Address Others');
            $content->description('Show address others');

            $content->body($this->grid());
        });
    }

    protected function grid() {
        return Admin::grid(AddressOthers::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->house_number('House Number')->sortable();
            $grid->building_name('Building')->sortable();
            $grid->street('Street')->sortable();
            $grid->address_full('Address')->sortable();
            $grid->created_at('Created date')->sortable();
            $grid->updated_at('Updated date')->sortable();

            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                $filter->like('house_number', 'House Number');
                $filter->like('building_name', 'Building');
                $filter->like('street', 'Street');
            });
        });
    }

    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Address Others');
            $content->description('Show');

            $content->body($this->form()->view($id));
        });
    }

    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('Address Others');
            $content->description('Create');

            $content->body($this->formcreate());
        });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Address Others');
            $content->description('Edit');

            $content->body($this->formedit()->edit($id));
        });
    }

    protected function form()
    {
         return $this->formcreate();
    }

    protected function formcreate()
    {
        return Admin::form(AddressOthers::class, function (Form $form) {

            $form->text("house_number", "House Number")->rules("required");
            $form->text("building_name", "Building")->rules("required");
            $form->text("street", "Street")->rules("required");
            $form->hidden("address_full");
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            // callback before save
            $form->saving(function (Form $form) {
                $form->address_full = $form->house_number . ', ' . $form->building_name . ', ' . $form->street;
            });

        });
    }

    protected function formedit() {
        return Admin::form(AddressOthers::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text("house_number", "House Number")->rules("required");
            $form->text("building_name", "Building")->rules("required");
            $form->text("street", "Street")->rules("required");
            $form->hidden("address_full");
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            // callback before save
            $form->saving(function (Form $form) {
                $form->address_full = $form->house_number . ', ' . $form->building_name . ', ' . $form->street;
            });
        });
    }


}
