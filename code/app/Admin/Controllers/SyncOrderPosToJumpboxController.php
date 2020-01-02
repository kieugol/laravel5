<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 12/20/2018
 * Time: 2:58 PM
 */

namespace App\Admin\Controllers;


use App\Model\LogJob;
use App\Repository\LogJobsRepository;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class SyncOrderPosToJumpboxController extends BaseController
{
    use ModelForm;

    private $log_job_repository;

    public function __construct(LogJobsRepository $logJobsRepository)
    {
        $this->log_job_repository = $logJobsRepository;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Sync Order Pos To Jumpbox');
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

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(LogJob::class, function (Grid $grid) {
            $grid->model()->orderBy("id", "desc");
            $grid->model()->where("url", "like", API_JUMPBOX.'/sync/order%');

            $grid->id();
            $grid->order_id();
            $grid->created_date();
//            $grid->params();
            $grid->column("status", "Status")->display(function($status) {
                if ($status == SUCCESS) {
                    return "<span class='label label-success'>" . 'success' . "</span>";
                }
                return "<span class='label label-danger'>" . 'fail' . "</span>";
            });

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
                $data = $actions->row;

                //only display resync for sync order
                if ($data->status == FAIL) {
                    $params = "'". htmlspecialchars($data->params) . "'";
                    $function = "resync_order_pos_to_jumpbox(this, '{$data->id}', '{$data->url}', '{$data->method}', {$params})";
                    $html =  '<button class="btn btn-sm btn-warning" onclick="'.$function.'"><i class="fa fa-undo" aria-hidden="true"></i></i>&nbsp;&nbsp;Resync</button>';
                    $actions->append($html);
                }
            });

            $grid->disableCreateButton();
            $grid->disableRowSelector();

            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                $filter->like('order_id');
                $filter->equal('status')->select([
                    FAIL => 'Fail',
                    SUCCESS => 'Success'
                ]);
                $filter->between('created_date', 'Order Date')->datetime();
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

        });
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusLogJobs(Request $request)
    {
        $result = $this->log_job_repository->updateStatusLogJobs($request->id);
        if ($result) {
            return response()->json([
                "result" => "success"
            ]);
        }
        return response()->json([
            "result" => "fail"
        ]);
    }

}