<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
use App\Helpers\PosHelper;
use App\Model\Order;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use App\Repository\OrderRepository;
use phpDocumentor\Reflection\Types\ContextFactory;


class OrderRevertController extends BaseController
{
    use ModelForm;

    private $orderRepo = null;

    public function __construct(OrderRepository $orderRepo) 
    {
        parent::__construct();
        $this->orderRepo = $this->orderRepo;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Revert Order');
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
        return Admin::form(Order::class, function (Form $form) {

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
        return Admin::grid(Order::class, function (Grid $grid) {

            $grid->actions(function ($actions) {
                $actions->disableDelete();
            });

            $grid->model()->orderBy("id", "desc");
            $grid->model()->orderBy("name", "desc");
            $grid->model()->orderBy("amount", "desc");
            $grid->id();
            $grid->number();
            $grid->name();
            $grid->email();
            $grid->phone();
            $grid->address();
            $grid->column('amount', 'Amount')->display(function($amount){
                return PosHelper::format_amount($amount);
            });
            $grid->date();

            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableExport();

            $grid->filter(function ($filter) {
                $filter->like('number', 'Number');
                $filter->like('name', 'Name');
                $filter->like('phone', 'Phone');
                $filter->between('date', 'Create date')->datetime([
                    'format' => 'YYYY-MM-DD',
                    'defaultDate' => date('Y-m-d')
                ]);
            });
            
            // Defaut for date time 
            if (!isset($_GET['date']['start']) && !isset($_GET['date']['end'])) {
                $grid->model()->where('date', '=', date('Y-m-d'));
            }

            $grid->model()->where(function($sub){
                $sub->whereIn('order_status_id', [
                    ORDER_STATUS_ORDERED,
                    ORDER_STATUS_COOKING,
                    ORDER_STATUS_COOKED,
                    ORDER_STATUS_EDITING,
                    ORDER_STATUS_CHECKOUT
                ]);
                $sub->where('is_paid', 1);
            });
            $grid->model()->orWhere(function($sub){
                $sub->whereIn('order_status_id', [
                    ORDER_STATUS_COOKING,
                    ORDER_STATUS_COOKED,
                    ORDER_STATUS_EDITING,
                    ORDER_STATUS_CHECKOUT
                ]);
                $sub->where('is_paid', 0);
            });

            $grid->model()->where('number', 'like', '000-%');
        });
    }

}