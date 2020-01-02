<?php

namespace App\Admin\Controllers;


use App\Admin\Controllers\BaseController;
use App\Model\Menu;
use App\Model\MenuCategory;
use App\Model\MenuAutoCook;
use App\Repository\MenuRepository;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class MenuAutoCookController extends BaseController
{

    use ModelForm;
    private $mmenu;

    public function __construct(MenuRepository $menu)
    {
        $this->mmenu = $menu;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Menu Auto Cook');
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

            $content->header('Menu Auto Cook');
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

            $content->header('Menu Auto Cook');
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
        return Admin::grid(MenuAutoCook::class, function (Grid $grid) {
            $grid->menu()->category_id()->display(function($category_id){
                $category = MenuCategory::find($category_id);
                return $category ? $category->name : "";
            });
            $grid->menu()->name()->sortable();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(MenuAutoCook::class, function (Form $form) {

            $form->select("menu_id", "Menu")->options($this->mmenu->getListMenuForAutoCook())->rules("required");

        });
    }

}
