<?php

namespace App\Admin\Controllers;

use App\Model\SyncOrderDelivery;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\{Request, Response};
use App\Repository\{SyncOrderDeliveryRepository};
use App\Repository\Api\{LogJobRepository};
use Illuminate\Support\Facades\Log;
use App\Jobs\{SyncOrderIntoOnlineViaCallCenter};

class SyncOrderDeliveryController extends BaseController
{
    use ModelForm;

    private $syncOrderDeliveryRepo = null;
    private $logJobRep = null;

    public function __construct(SyncOrderDeliveryRepository $syncOrderDeliveryRepo, LogJobRepository $logJobRep)
    {
        parent::__construct();
        $this->syncOrderDeliveryRepo = $syncOrderDeliveryRepo;
        $this->logJobRep = $logJobRep;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Sync order delivery POS into Online');
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
        return Admin::form(SyncOrderDelivery::class, function (Form $form) {

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
        return Admin::grid(SyncOrderDelivery::class, function (Grid $grid) {

            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableExport();

            $grid->model()->orderBy("id", "desc");

            $grid->id();
            $grid->column('order_code', 'Order Code')->display(function($order_code) {
                $url_management_order =  url('/admin/order?order_type[number]=' . $order_code);
                return "<a href='{$url_management_order}' target='_blank'>{$order_code}</a>";
            });
            $grid->number_of_retry();
            $grid->created_date();
            $grid->updated_date();
            $grid->column('order_id', 'Sync to online')->display(function($order_id) {
                if ($this->is_sync) {
                    return "<span><i class='fa fa-check text-success'></i>";
                }
                $path  = "'" . route('re_sync_order_delivery') . "?order_id={$order_id}" . "'";
                return '<button class="btn btn-sm btn-warning" onclick="resync_order_delivery(this, '.$path.')"><i class="fa fa-undo" aria-hidden="true"></i></i>&nbsp;&nbsp;Resync</button>';
            });

            // Filter
            $grid->filter(function ($filter) {
                $filter->like('order_code', 'Order No');
                $filter->between('created_date', 'Create date')->date([
                    'format' => 'YYYY-MM-DD',
                    'defaultDate' => date('Y-m-d')
                ]);
            });
        });
    }

    public function resyncOrderDelivery(Request $request)
    {
        $orderId = $request->input('order_id', 0);
        $orderDeliverySync = $this->syncOrderDeliveryRepo->findByOrderId($orderId);

        $response = [
            'message' => 'Re-sync into Online already!',
            'status'  => true,
            'result'  => '',
        ];
        $http_code = Response::HTTP_OK;

        if ($orderDeliverySync) {
            Log::info('[Manual] Sync order delivery into Online via CallCenter', ['order_id' => $orderId]);
            SyncOrderIntoOnlineViaCallCenter::dispatch($orderId)->onQueue(QUEUE_SYNC_ORDER_FOR_ONLINE);
        }

        return response($response, $http_code);
    }
}
