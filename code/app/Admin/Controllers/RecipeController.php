<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
use App\Model\Recipe;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;

class RecipeController extends BaseController {

    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index() {
        return Admin::content(function (Content $content) {

                    $content->header('header');
                    $content->description('description');

                    $content->body($this->grid());
                });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id) {
        return Admin::content(function (Content $content) use ($id) {

                    $content->header('header');
                    $content->description('description');

                    $content->body($this->form()->edit($id));
                });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create() {
        return Admin::content(function (Content $content) {

                    $content->header('header');
                    $content->description('description');

                    $content->body($this->form());
                });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid() {
        return Admin::grid(Recipe::class, function (Grid $grid) {

                    $grid->id('ID')->sortable();

                    $grid->plucode('recipe code')->sortable();
                    $grid->shortname()->sortable();
                    $grid->longname()->sortable();
                    $grid->description()->sortable();

                    $grid->created_at();
                    $grid->updated_at();
                });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form() {
        return Admin::form(Recipe::class, function (Form $form) {
                    $form->display('id', 'ID');

                    $form->select('plucode', 'recipe code')->options($this->recipe_code())->rules("required");  //loading value from plucode & material_code
                    $form->text('shortname', 'shortname')->rules("required");
                    $form->text('longname', 'longname')->rules("required");
                    $form->text('description', 'description')->rules("required");

                    $form->display('created_at', 'Created At');
                    $form->display('updated_at', 'Updated At');
                });
    }


    /**
     * Retrieving data from material_master & plucode.
     *
     * @return Form
     */
    public function recipe_code()
    {
        /*plucode table data*/
        $collection1 = DB::table('plucode')->orderBy('id', 'ASC')->select('id', 'plucode')->get()->toArray();

        /*material_master table data*/
        $client = new \GuzzleHttp\Client();
        $request_mat_group = $client->get(config('admin.inventory_api_url') . '/material-master');
        $response_mat_group = $request_mat_group->getBody()->getContents();
        $data_mat_group = json_decode($response_mat_group);
        $collection2 = $data_mat_group->data;

        /*creating combined array*/
        $array = [];
        foreach($collection1 as $collec){
//            $array['plucode-'.$collec->id] = 'plucode-'.$collec->plucode;
            $array['PLUCODE--'.$collec->plucode] = 'PLUCODE--'.$collec->plucode;
        }
        foreach($collection2 as $collec2){
//            $array['matcode-'.$collec2->material_id] = 'matcode-'.$collec2->material_code;
            $array['MATCODE--'.$collec2->material_code] = 'MATCODE--'.$collec2->material_code;
        }

        return $array;
    }

}
