<?php
/**
 * Created by PhpStorm.
 * User: Bhavya
 * Date: 17-09-2018
 * Time: 15:03
 */
namespace App\Admin\Controllers;
use App\Model\PaymentMethod;
use App\Model\Role;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use App\Repository\PaymentMethodRepository;
class PaymentMethodController extends BaseController
{
    use ModelForm;
    private $pm = null;
    public function __construct(PaymentMethodRepository $pm)
    {
        parent::__construct();
        $this->pm = $pm;
    }
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Payment Method');
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
            $content->header('Payment Method');
            $content->description('Edit');
            $content->body($this->formedit()->edit($id));
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
            $content->header('Payment Method');
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
        return Admin::grid(PaymentMethod::class, function (Grid $grid) {
            $grid->column('name', 'Name');
            $grid->column('code', 'Code');
            $grid->column('type', 'Type');
            $grid->column('bank', 'Bank');
            //$grid->switch("is_card_number", "Card Number")->switch()->editable();
            $grid->is_card_number()->switch()->editable();
            $grid->is_remark()->switch()->editable();
            $grid->is_approval_code()->switch()->editable();
            $grid->is_active()->switch()->editable();
            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                $filter->like('type', 'Type');
                $filter->like('bank', 'Bank');
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
        return Admin::form(PaymentMethod::class, function (Form $form) {
            $id = request()->input("id");
            if($id){
                return $this->formedit()->edit($id);
            }else{
                $form->text("name")->rules("required");
                $form->text("code")->rules("required");
                $form->text("type")->rules("required");
                $form->text("bank")->rules("required");
                $form->switch("is_card_number", "Card Number");
                $form->switch("is_remark", "Remark");
                $form->switch("is_approval_code", "Approval Code");
                $form->switch("is_active", "Is Active");
            }
        });
    }
    public function update($id)
    {
        return $this->formedit()->update($id);
    }
    protected function formedit()
    {
        return Admin::form(PaymentMethod::class, function (Form $form) {
            $form->text('name')->rules("required");
            $form->text('code', 'Code');
            $form->text('type', 'Type');
            $form->text('bank', 'Bank');
            $form->switch("is_card_number", "card number");
            $form->switch("is_remark", "Remark");
            $form->switch("is_approval_code", "Approval code");
            $form->switch("is_active", "Is Active");
        });
    }
}