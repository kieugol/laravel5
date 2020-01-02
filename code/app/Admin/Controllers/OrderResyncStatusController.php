<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
use App\Helpers\PosHelper;
use App\Model\OrderResyncStatus;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use App\Repository\OrderResyncStatusRepository;


class OrderResyncStatusController extends BaseController 
{
    use ModelForm;

    private $orderResyncStatusRepo = null;

    public function __construct(OrderResyncStatusRepository $orderResyncStatusRepo) 
    {
        parent::__construct();
        $this->orderResyncStatusRepo = $orderResyncStatusRepo;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Resync order status online');
            $content->description('List');
            $content->body("<style>.grid-row-delete{display:none !important}</style>");
            $content->body($this->grid());
        });
    }

    public function update($id)
    {
        return $this->form()->update($id);
    }
    
    
    protected function form()
    {
        return Admin::form(OrderResyncStatus::class, function (Form $form) {

            $form->display("name", "Customer");
            $form->display('number', 'Order code');
            $form->display('amount', 'Amount');
            $form->select("is_paid", 'Revert pay method')->options(['' => '', 0 => 'Pay later'])->rules("required");
            $form->select("order_status_id", 'Revert order status')->options(['' => '', ORDER_STATUS_ORDERED => 'Ordered'])->rules("required");

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

            $content->header('Revert order');
            $content->description('Edit');

            $content->body($this->form()->edit($id));
        });
    }

    public function destroy($id)
    {
        return response()->json([
            'status'  => false,
            'message' => "Can not allow!",
        ]);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(OrderResyncStatus::class, function (Grid $grid) {

            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableExport();

            $grid->model()->orderBy("id", "desc");
            $grid->id();
            $grid->column('order.number', 'Order No');
            $grid->column('order.order_type_id', 'Type')->display(function($order_type_id){
                if ($order_type_id == ORDER_TYPE_DELIVERY) {
                    return '<span class="label label-danger">Delivery</span>';
                }
                return '<span class="label label-success">Take away</span>';
            });
            $grid->column('order.created_date', 'Created date');
            $grid->column('order.name', 'Name');
            $grid->column('order.phone', 'Phone No');
            $grid->column('order.address', 'Address');
            $grid->column('order.amount', 'Amount')->display(function($amount){
                return PosHelper::format_amount($amount);
            });
            $grid->column('order_id', 'Action')->display(function($order_id){
                $path  = "'" . route('resync_order_status') . "?order_id={$order_id}" . "'";
                return '<button class="btn btn-sm btn-warning" onclick="resync_order_status_online(this, '.$path.')"><i class="fa fa-undo" aria-hidden="true"></i></i>&nbsp;&nbsp;Resync</button>';
            });
            // Geting order was resynced is greater than 5 times
            $grid->model()->where('number_of_retry', '>=', LIMIT_NUMBER_SYNC_ORDER_STT);
            $grid->model()->where('status', STATUS_NOT_DONE);
            $grid->model()->groupBy('order_id');
            // Filter
            $grid->filter(function ($filter) {
                $filter->like('order.number', 'Order No');
                $filter->like('order.name', 'Name');
                $filter->like('order.phone', 'Phone No');
                $filter->between('order.date', 'Create date')->date([
                    'format' => 'YYYY-MM-DD',
                    'defaultDate' => date('Y-m-d')
                ]);
            });
        });
    }

    public function resyncOrderStatus(Request $request)
    {
        $orderId = $request->input('order_id', 0);
        $result = $this->orderResyncStatusRepo->resyncOrderStatus($orderId);
        return response($result, $result['code']);
    }
}