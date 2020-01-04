<div class="box box-info receive-order">
    <div class="box-header with-border">
        <h3 class="box-title">{{ isset($receive)? 'Edit' : 'Create' }}</h3>
        <div class="box-tools">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="{!! url('admin/inventory/purchase-order') !!}" class="btn btn-sm btn-default btn-reload"><i class="fa fa-list"></i>&nbsp;List</a>
            </div>

            <div class="btn-group pull-right" style="margin-right: 10px">
                <a class="btn btn-sm btn-default form-history-back"><i class="fa fa-arrow-left"></i>&nbsp;Back</a>
            </div>
        </div>
    </div>

    <div class="box-body">
        <form class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-2 control-label">Purchase Order Code</label>
                <div class="col-sm-3">
                    <select class="form-control col-sm-8" id="purchase" @if($action == ACTION_VIEW) disabled @endif>
                        <option value="0">Select</option>
                        @foreach($purchase_list as $purchase)
                            <option value="{{ $purchase->id }}" @if($purchase_id == $purchase->id) selected  @endif> {{ $purchase->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Supplier</label>
                <div class="col-sm-3">
                    <input type="text" disabled="true" class="form-control" id="supplier"
                           value="{{ isset($receive) ? $receive->supplier->name : '' }}"
                           supplier-code="{{ isset($receive) ? $receive->supplier->code : '' }}"
                           data-id="{{ isset($receive) ? $receive->supplier->id : '' }}">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Invoice number</label>
                <div class="col-sm-3">
                        <input type="text" class="form-control"
                               id="invoice_number"
                               @if(isset($receive['status_id']) && $receive['status_id'] == TRANSACTION_ORDER_STATUS_APPROVED)
                                readonly
                               @endif
                               value="{{ isset($receive) ? $receive->invoice_number : '' }}">
                </div>
            </div>
            {{--<div class="form-group">
                <label class="col-sm-2 control-label">Receive Date</label>
                <div class="col-sm-3">
                    <input type="text" @if($action == ACTION_VIEW) disabled @endif value="{{ isset($receive) ? $receive->transaction_date : $delivery_date }}" id="receive_date" class="form-control"/>
                </div>
            </div>--}}
            @if($action == ACTION_CREATE)
                {{--<div class="form-group">--}}
                    {{--<label class="col-sm-2 control-label">Returnable</label>--}}
                    {{--<div class="col-sm-3">--}}
                        {{--@if(isset($receive) && $receive->is_returnable == 0)--}}
                            {{--<input id="is_returnable" class="bootstrap-switch" type="checkbox">--}}
                        {{--@else--}}
                            {{--<input id="is_returnable" class="bootstrap-switch" type="checkbox" checked>--}}
                        {{--@endif--}}
                    {{--</div>--}}
                {{--</div>--}}
                <div class="form-group import-do">
                    <label class="col-sm-2 control-label">Import DO Online</label>
                    <div class="col-sm-3">
                        <label class="btn btn-primary">
                            Browse <input @if($action == ACTION_VIEW) disabled @endif name="file" type="file" id="file"
                                          hidden accept=".csv">
                        </label>
                    </div>
                </div>
            @endif
            @if($action == ACTION_VIEW)
                <div class="form-group">
                    <label class="col-sm-2 control-label">Status</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{TRANSACTION_ORDER_STATUS[$receive['status_id']]}}"
                               class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Created By</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$receive['user_admin']['username']}}"
                               class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Created Date</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$receive['created_date']}}" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Updated By</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$receive['user_admin_updated']['username']}}"
                               class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Update Date</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$receive['updated_date']}}" class="form-control"/>
                    </div>
                </div>
            @endif
        </form>
        <br>

        <div class="box-body table-responsive no-padding">
            <table class="table table-hover" id="tbl-material-detail">
                <thead>
                <tr>
                    <th>Material Detail Code</th>
                    <th>Material Detail Name</th>
                    <th>Smaller UOM Detail</th>
                    <th>UOM</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($receive_details))
                    @foreach($receive_details as $key => $receive_detail)
                        <tr class="material-details">
                            <input type="hidden" id="material_detail_id"
                                   value="{{ $receive_detail->material_detail_id }}">
                            <input type="hidden" id="material_id" value="{{ $receive->id }}">
                            <input type="hidden" id="account_id" value="{{ $receive_detail->account_id }}">
                            <td>{{ $receive_detail->material_detail_code }}</td>
                            <td>{{ $receive_detail->material_detail_name }}</td>
                            <td>{{ $receive_detail->smaller_uom_detail_name }}</td>
                            <td>{{ $receive_detail->uom_name }}</td>
                            @if($receive['status_id'] == TRANSACTION_ORDER_STATUS_APPROVED || $action == ACTION_VIEW)
                                <td>{{ $receive_detail->quantity }}</td>
                                <td>{{ number_format($receive_detail->price, 2) }}</td>
                            @else
                                <td width="100"><input type="number" class="text-right quantity" value="{{ $receive_detail->quantity }}"></td>
                                <td width="100"><input type="number" class="text-right price" value="{{ $receive_detail->price }}"></td>
                            @endif
                            <td class="total text-right">{{ number_format($receive_detail->total, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
            <br>
            <br>
            <br>

            <div class="w400">
                <table class="table table-hover" id="tbl-account">
                    <thead>
                    <tr>
                        <th>Account</th>
                        <th>Desc</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(!empty($accounts))
                        @foreach($accounts as $account)
                            <tr>
                                <input type="hidden" id="account_id" value="{{ $account['account_id'] }}">
                                <td class="account_code">{{ $account['account_code'] }}</td>
                                <td class="account_name">{{ $account['account_name'] }}</td>
                                <td width="100" class="total"> {{ number_format($account['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                <br>
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Grand Total</label>
                        <div class="col-sm-8">
                            <input type="text" disabled="true" class="form-control text-right" id="grand-total"
                                   value="{{ isset($total) ? number_format($total, 2) : '' }}">
                        </div>
                    </div>
                </div>
            </div>

            <br>
            <br>
            <div class="text-center">
                @if($action == ACTION_CREATE)
                    <button type="submit" onclick="goPurchaseList()" class="btn btn-default">Cancel</button>
                    <button type="submit" onclick="createForm()" class="btn btn-primary btn_save_do">Save DO Online</button>
                    <button type="submit" onclick="createForm()" class="btn btn-primary btn_save_approve">Approve Receive</button>
                @elseif($action == ACTION_VIEW)
                    @if($receive['status_id'] == TRANSACTION_ORDER_STATUS_PENDING)
                        <button type="submit" onclick="goPurchaseList()" class="btn btn-default">Cancel</button>
                        <button type="button" id="approve-receive-btn" onclick="confirmAction({{$receive['id']}}, {{TRANSACTION_ORDER_STATUS_APPROVED}})" class="btn btn-primary">Approve Receive
                        </button>
                    @endif
                @endif
                <input type="hidden" id="action_form" value="{{$action}}">
                <input type="hidden" id="purchase_id" value="{{$purchase_id}}">
            </div>
        </div>

    </div>

    <input type="hidden" id="action_create" value="{{ACTION_CREATE}}">
    <input type="hidden" id="status_approve" value="{{TRANSACTION_ORDER_STATUS_APPROVED}}">
    <input type="hidden" id="status_reject" value="{{TRANSACTION_ORDER_STATUS_REJECTED}}">
</div>
<script src="{{admin_asset("/js/inventory/receive_order.js?v=" . time())}}"></script>
<script>
    $(function () {
        $('#receive_date').datetimepicker({
            "format": "YYYY-MM-DD"
        });
    });
</script>
<style type="text/css">
    .approve-receive-popup {
        width: 800px !important;
        margin-left: -25%;
    }
</style>