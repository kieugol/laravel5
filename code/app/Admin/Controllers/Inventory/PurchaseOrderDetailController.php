<?php

namespace App\Admin\Controllers;

use App\Model\Inventory\PurchaseOrderDetail;
use App\Repository\Inventory\PurchaseOrderRepository;
use Encore\Admin\{Grid, Form};
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Log;

class PurchaseOrderDetailController extends BaseController
{
    use ModelForm;

    private $syncOrderDeliveryRepo = null;
    private $logJobRep = null;

    public function __construct()
    {
        parent::__construct();
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
        return Admin::form(PurchaseOrderDetail::class, function (Form $form) {

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
    }

}
