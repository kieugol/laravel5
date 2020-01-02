<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">

                <div class="box-header with-border">
                    <h3 class="box-title">{{$title}}</h3>
                    <div class="box-tools">
                        <div class="btn-group pull-right" style="margin-right: 10px">
                            <a href="<?= route('inventory-purchase-order-index') ?>" class="btn btn-sm btn-default" id="btn-home"><i class="fa fa-list"></i>&nbsp;List</a>
                        </div>

                        <div class="btn-group pull-right mr10">
                            <a href="<?= route('inventory-purchase-order-index') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i>&nbsp;Back</a>
                        </div>
                    </div>
                </div>

                <div class="box-body">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Purchase Order Code</label>
                            <div class="col-sm-3">
                                <input type="text" value="{{$code}}" disabled class="form-control" >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Delivery Date</label>
                            <div class="col-sm-3">
                                <input type="text" @if($action == ACTION_VIEW) disabled @endif value="{{$delivery_date}}" id="delivery-date" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Supplier</label>
                            <div class="col-sm-3">
                                <select class="form-control" @if($action == ACTION_VIEW) disabled @endif id="supplier" onchange="getListMaterialDetail(this)">
                                    <option value="0">--Choose Supplier--</option>
                                    <option value="{{ ALL }}">All</option>
                                    @foreach($supplier_list as $item)
                                    <option @if($action == ACTION_VIEW && $po_detail['supplier_id'] == $item->id) selected @endif value="{{$item->id}}">{{$item->name}}({{$item->code}})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @if($action == ACTION_VIEW)
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Status</label>
                                    <div class="col-sm-3">
                                        <input type="text" readonly value="{{TRANSACTION_ORDER_STATUS[$po_detail['status_id']]}}" class="form-control"/>
                                    </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Created By</label>
                                <div class="col-sm-3">
                                    <input type="text" readonly value="{{$po_detail['user_admin']['username']}}" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Created Date</label>
                                <div class="col-sm-3">
                                    <input type="text" readonly value="{{$po_detail['created_date']}}" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Updated By</label>
                                <div class="col-sm-3">
                                    <input type="text" readonly value="{{$po_detail['user_admin_updated']['username']}}" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Update Date</label>
                                <div class="col-sm-3">
                                    <input type="text" readonly value="{{$po_detail['updated_date']}}" class="form-control"/>
                                </div>
                            </div>
                        @endif
                    </div>
                    <br>

                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover" id="tbl-detail">
                            <thead>
                                <tr>
                                    <th>Material Detail Code</th>
                                    <th>Material Detail Name</th>
                                    <th width="200">Smaller UOM Detail</th>
                                    <th>UOM</th>
                                    <th>Qty</th>
                                    <th>Lastest Price</th>
                                    <th>Sub total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <br>
                        <br>
                        <br>

                        <div class="w400">
                            <table class="table table-hover" id="tbl-summary">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th>Desc</th>
                                        <th>Sub Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <br>
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Grand Total</label>
                                    <div class="col-sm-8">
                                        <input type="text" value="0" disabled class="form-control grand_total" >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>
                        <br>
                        <div class="text-center">
                        @if($action == ACTION_CREATE)
                                <button type="button" onclick="confirmAction(this)" class="btn btn-primary">Save</button>
                        @elseif($action == ACTION_VIEW)
                                @if($po_detail['status_id'] == TRANSACTION_ORDER_STATUS_PENDING)
                                    <a href="<?= route('inventory-purchase-order-index') ?>"  id="btn-home" class="btn btn-default mr5">Cancel</a>
                                    <button type="button" onclick="showAllPoDetail()" class="btn btn-info btn-edit mr5">Edit</button>
                                    <button type="button" onclick="approveAction(this, {{$po_detail['id']}}, 0)" class="btn btn-primary btn-approve">Approve</button>
                                    <button type="button" onclick="approveAction(this, {{$po_detail['id']}}, 1)" class="btn btn-primary btn-save" hidden="true">Save</button>
                                @endif
                                <input type="hidden" id="action_form" value="{{$action}}">
                                <input type="hidden" id="po-detail" value="{{$po_detail_json}}">
                        @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

<script src="{{admin_asset("/js/inventory/purchase_order.js?v=" . time())}}"></script>
<script>
  $(function () {
    $('#delivery-date').datetimepicker({
      "format": "YYYY-MM-DD"
    });
    $('#supplier').select2({
        width: '280px',
        allowClear: true,
        placeholder :'Select Supplier...'
    });
  });
</script>

