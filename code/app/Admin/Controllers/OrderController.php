<?php
/**
 * Created by PhpStorm.
 * User: Bhavya
 * Date: 17-09-2018
 * Time: 15:03
 */

namespace App\Admin\Controllers;

use App\Model\OrderDetail;
use App\Model\OrderPayment;
use App\Model\OrderStatus;
use App\Model\Order;
use App\Model\OrderType;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use App\Repository\{OrderRepository, PaymentMethodRepository};
use Illuminate\Http\Request;
use App\Helpers\PosHelper;

class OrderController extends BaseController
{
    use ModelForm;

    const PAYMENT_LABEL = [
        PAYMENT_METHOD_TYPE_CASH => 'label-success',
        PAYMENT_METHOD_TYPE_CARD => 'label-warning',
        PAYMENT_METHOD_TYPE_VOUCHER => 'label-default',
        PAYMENT_METHOD_OC => 'label-primary',
        PAYMENT_METHOD_OUTLET_MEAL => 'label-primary',
        PAYMENT_METHOD_TYPE_ECASH => 'label-info',
    ];

    private $order_repository;
    private $paymentMethodRep;

    public function __construct(OrderRepository $order_repository, PaymentMethodRepository $paymentMethodRep)
    {
        parent::__construct();
        $this->order_repository = $order_repository;
        $this->paymentMethodRep = $paymentMethodRep;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Manage Orders');
            $content->description('List');
            $content->body($this->grid());

        });

    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $paymentTypeMaster = $this->getMasterPaymentMethodByType();
        $orderRep = $this->order_repository;

        return Admin::grid(Order::class, function (Grid $grid) use($paymentTypeMaster, $orderRep) {
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('number', 'Order Code');
            $grid->column('collection_time', 'Quote time');
            $grid->column("order_type_id", "Order Type")->display(function($order_type_id) {
                $labelColor = $order_type_id == ORDER_TYPE_DELIVERY ? 'label-danger' : 'label-success';
                return "<span class='label $labelColor'>" . ORDER_TYPE[$order_type_id] . "</span>";
            });;
            $grid->column("order_status.name", "Order Status");
            $grid->column('amount', 'Sale Amount')->display(function ($amount) {
                return PosHelper::format_amount($amount);
            });
            $grid->column('sub_total', 'Sub Total')->display(function ($sub_total) {
                return PosHelper::format_amount($sub_total);
            });
            $grid->column('sub_delivery_fee', 'Sub Delivery Fee')->display(function ($sub_delivery_fee) {
                return PosHelper::format_amount($sub_delivery_fee);
            });
            $grid->column('tax_value', 'Tax Value')->display(function ($tax_value) {
                return PosHelper::format_amount($tax_value);
            });
            $grid->column('donation', 'Donation')->display(function ($donation) {
                return PosHelper::format_amount($donation);
            });
            $grid->column('Payment Method')->display(function () use ($paymentTypeMaster) {
                $paymentMethods = [];
                foreach ($this->order_payments as $row) {
                    $type = $paymentTypeMaster[$row['payment_method_id']];
                    $labelColor = self::PAYMENT_LABEL[$type] ?? 'label-danger';
                    $paymentMethods[] = "<span class='label $labelColor'>" . $row['payment_method_name'] . "</span>";
                }
                return implode(" ", $paymentMethods);
            });
            $grid->column('Payment Amount')->display(function ()  {
                return PosHelper::format_amount(array_sum(array_column($this->order_payments->toArray(), 'total_payment')));
            });
            $grid->column('Variance Status')->display(function () {
                if ($this->order_status_id != ORDER_STATUS_FINISHED) {
                    return '';
                }

                $error = [];
                $total_payment = array_sum(array_column($this->order_payments->toArray(), 'total_payment'));
                if ($total_payment != $this->amount) {
                    $error[] = '<span class="label label-danger">' . 'Variance Payment' . '</span>';
                }

                return count($error) ? implode(" ", $error) : '<span class="label label-success">' . 'Passed' .  '</span>';
            });
            $grid->actions(function($actions){
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();

                $id = $actions->row->id;
                $actions->append(
                    '<div class="btn-group pull-right ml10">
                        <a class="btn btn-sm  btn-twitter"><i class="fa fa-cogs"></i> Actions</a>
                        <button type="button" class="btn btn-twitter btn-sm  dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="edit-order/'.$id.'">View Order</button></a></li>
                            <li><a href="payment-detail-view/'.$id.'">View Payment</a></li>
                            <li><a href="order-detail-view/'.$id.'">View Order Detail</a></li>
                            <li><a href="delivery-detail-view/'.$id.'">View Delivery</a></li>
                            <li><a href="log-detail-view/'.$id.'">View Log Status</a></li>
                        </ul>
                    </div>'
                );
            });

            $grid->model()->where('order_status_id', '!=', ORDER_STATUS_CANCELED);
            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                $filter->like('order_type.number', 'Order Code');
                $filter->like('order_type_id', 'Order Type')->select(OrderType::all()->pluck('name', 'id'));
                $filter->like('order_status.id', 'Order Status')->select(OrderStatus::all()->pluck('name', 'id'));
                $filter->between('created_date', 'Order Date')->datetime();
            });
        });
    }

    /**
     * Show order details
     */

    public function orderDetailView(Request $request, $id)
    {
        $result = $this->order_repository->getOrderDetails($id);
        $data = array();
        $data['detail'] = $result;
        $data['PosHelper'] = PosHelper::class;
        return view('order.order_details',$data);
    }

    public function paymentDetailView(Request $request, $id)
    {
        $data = [];
        $result = $this->order_repository->getPaymentDetails($id);

        $data['detail'] = $result;
        $data['PosHelper'] = PosHelper::class;
        $data['hasPermissionEdit'] = $this->hasPermissionEditPayment($result);

        return view('order.payment_details',$data);
    }

    public function deliveryDetailView(Request $request, $id)
    {
        $result = $this->order_repository->getDeliveryDetails($id);
        $data = array();
        $data['detail'] = $result;
        return view('order.delivery_details',$data);
    }

    public function logDetailView(Request $request, $id){
        $result = $this->order_repository->getlogDetails($id);
        $data = array();
        $data['detail'] = $result;
        return view('order.log_details',$data);
    }

    public function logPrintView(Request $request, $id){
        $result = $this->order_repository->getlogprintDetails($id);
        $data = array();
        $data['detail'] = $result;
        return view('order.log_print',$data);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Content
     */
    public function edit(Request $request, $id)
    {
        return Admin::content(function (Content $content) use ($id, $request) {
            $content->header('View Order');
            $content->description('View');
            $content->body($this->formEditOrder($id));
        });
    }

    /**
     * @param Request $request
     * @param $id
     * @return Form
     */
    public function formEditOrder($id)
    {
        return Admin::form(Order::class, function (Form $form) use ($id) {
            $data = $this->order_repository->getOrder($id);
            $form->setTitle('View detail');
            $form->text('Order Code')->value($data->number)->readonly();
            $form->text('Quote time')->value($data->collection_time)->readonly();
            $form->text('Order Type')->value($data->order_type_name)->readOnly();
            $form->text('Status')->value($data->order_status_name)->readOnly();
            $form->text('Customer')->value($data->name)->readOnly();
            $form->text('Phone')->value($data->phone)->readOnly();
            $form->text('Amount')->value($data->amount)->readOnly();
            $form->text('Sub Total')->value($data->sub_total)->readOnly();
            $form->text('Sub Delivery Fee')->value($data->sub_delivery_fee)->readOnly();
            $form->text('Tax Value')->value($data->tax_value)->readOnly();
            $form->text('Donation')->value($data->donation)->readOnly();
            $form->disableSubmit();
        });
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update_order(Request $request, $id)
    {
        $url = $request->getSchemeAndHttpHost(). "/admin/order";
        return redirect($url);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Content
     */
    public function editOrderDetail(Request $request, $id)
    {
        return Admin::content(function (Content $content) use ($id, $request) {
            $content->header('View Order Detail');
            $content->description('View');
            $content->body($this->editOrderDetailForm($id));
        });
    }

    /**
     * @param Request $request
     * @param $id
     * @return Form
     */
    public function editOrderDetailForm($order_detail_id)
    {
        return Admin::form(OrderDetail::class, function (Form $form) use ($order_detail_id) {
            $data = $this->order_repository->getOrderDetailByID($order_detail_id);
            $form->setTitle('View Order Detail');
            $form->setTitle('View Order Detail');
            $form->text("PluCode")->value($data->plucode)->readOnly();
            $form->text('Price')->value($data->price)->readOnly();
            $form->text('Sub Price')->value($data->sub_price)->readOnly();
            $form->text('Short Name')->value($data->short_name)->readOnly();
            $form->text('Menu Name')->value($data->menu_name)->readOnly();
            $form->text('Menu Price')->value($data->menu_price)->readOnly();
            $form->text('Variant Name')->value($data->variant_name)->readOnly();
            $form->text('Add-on Price')->value($data->addon_price)->readOnly();
            $form->text('Add-on Name')->value($data->addon_name)->readOnly();
            $form->disableSubmit();
        });
    }

    /**
     * @param Request $request
     * @param $order_detail_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateOrderDetail(Request $request, $order_detail_id)
    {
        $url = $request->getSchemeAndHttpHost(). "/admin/order";
        /*$data['plucode'] = isset($request->PluCode) ? $request->PluCode : '';
        $data['price'] = isset($request->Price) ? $request->Price : 0;
        $data['sub_price'] = isset($request->Sub_Price) ? $request->Sub_Price : 0;
        $result = $this->order_repository->updateOrderDetailByID($order_detail_id, $data);

        if ($result == true) {
            admin_toastr(trans('admin.update_succeeded'));
        }*/
        return redirect($url);
    }

    /**
     * @param Request $request
     * @param $order_id
     * @return Content
     */
    public function editOrderPayment(Request $request, $order_id)
    {
        return Admin::content(function (Content $content) use ($order_id, $request) {
            $content->header('Edit Order Payment');
            $content->description('Edit');
            $content->body($this->editOrderPaymentForm($request, $order_id));
        });
    }

    /**
     * @param Request $request
     * @param $order_id
     * @return Form
     */
    public function editOrderPaymentForm(Request $request, $order_id)
    {
        return Admin::form(OrderPayment::class, function (Form $form) use ($order_id, $request) {
            $url = $request->getSchemeAndHttpHost(). "/admin/update-order-payment/". $order_id;
            $data = $this->order_repository->getOrderPaymentByOrderID($order_id);
            $form->setTitle('Edit Order Payment');
            $form->text("Payment Method")->value($data->payment_method_name)->readOnly();
            $form->text('Value')->value($data->value)->readOnly();
            $form->text('Change')->value($data->change)->readOnly();
            $form->text('Total Payment')->value($data->total_payment);
            $form->setAction($url);
        });
    }

    /**
     * @param Request $request
     * @param $order_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateOrderPayment(Request $request, $order_id)
    {
        $url = $request->getSchemeAndHttpHost(). "/admin/payment-detail-view/" . $order_id;
        $data['total_payment'] = isset($request->Total_Payment) ? $request->Total_Payment : 0;
        $resultPayment = $this->order_repository->getPaymentDetails($order_id);

        $hasPermissionEdit = $this->hasPermissionEditPayment($resultPayment);
        if ($hasPermissionEdit) {
            $this->order_repository->updateOrderPaymentByOrderID($order_id, $data);
        }
        return redirect($url);
    }

    protected function getMasterPaymentMethodByType()
    {
        $result = [];
        $data = $this->paymentMethodRep->all();

        foreach ($data as $row) {
            $result[$row->id] = $row->type;
        }

        return $result;
    }

    protected function hasPermissionEditPayment($result)
    {
        if (!empty($result)) {
            $amount= 0;
            $totalPayment =0;
            foreach ($result as $row) {
                $amount = $row->amount;
                $totalPayment += $row->total_payment;
            }
            if ($amount != $totalPayment) {
                // Have been got variance
                return true;
            }
        }
        return false;
    }
}
