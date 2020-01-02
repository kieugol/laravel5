<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
//use App\Admin\Extensions\Grid\CustomActions;
use App\Model\LogCurl;
use App\Repository\LogCurlRepository;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class LogCurlController extends BaseController
{

    use ModelForm;
    private $log_curl_repository;

    public function __construct(LogCurlRepository $logCurlRepository)
    {
        $this->log_curl_repository = $logCurlRepository;
//        parent::__construct();
//
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

            $content->header('Log Curl');
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

            $content->header('Menu Auto Cook');
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
        return Admin::grid(LogCurl::class, function (Grid $grid) {
            $grid->model()->orderBy("id", "desc");

            $grid->id();
            $grid->url();
            $grid->method();

            $grid->params();
            $grid->column('status', 'Status')->display(function($status){
                return ($status == SUCCESS) ? 'success' : 'fail';
            });

            $grid->error();
            $grid->created_date();
            $grid->number_of_retry();

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
                $data = $actions->row;
                //only display resync for sync order
                if ($data->url == route('sync_order') && $data->status == FAIL) {
                    $params = "'". htmlspecialchars($data->params) . "'";
                    $function = "call_ajax(this, '{$data->id}' ,'{$data->url}', '{$data->method}', {$params})";
                    $html =  '<button class="btn btn-sm btn-warning" onclick="'. $function . '"><i class="fa fa-undo" aria-hidden="true"></i></i>&nbsp;&nbsp;Resync</button>';
                    $actions->append($html);
                }
            });

            $grid->disableCreateButton();
            $grid->disableRowSelector();

            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                $filter->like('url');
                $filter->equal('method');
                $filter->like('params');
                $filter->like('response');
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
        return Admin::form(LogCurl::class, function (Form $form) {

//            $form->display("type");
//            $form->display("key");
//            $form->display("name");
//            $form->textarea("value");
//            $form->switch("is_active");

        });
    }

    public function update_status_success($log_curl_id)
    {
        $this->log_curl_repository->update_status_success($log_curl_id);
        return response()->json([
            "message" => "Resync Success!"
        ]);
    }

}
