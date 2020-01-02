<div class="box box-info return-order">
    <div class="box-header with-border">
        <h3 class="box-title">{{ isset($return)? 'Edit' : 'Create' }}</h3>
        <div class="box-tools">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="{!! url('admin/inventory/return') !!}" class="btn btn-sm btn-default"><i
                            class="fa fa-list"></i>&nbsp;List</a>
            </div>

            <div class="btn-group pull-right" style="margin-right: 10px">
                <a class="btn btn-sm btn-default form-history-back"><i class="fa fa-arrow-left"></i>&nbsp;Back</a>
            </div>
        </div>
    </div>

    <div class="box-body">
        <form class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-2 control-label">Receive Invoice Number</label>
                <div class="col-sm-3">
                    <select class="form-control col-sm-8" id="receive" @if($action == ACTION_VIEW) disabled @endif>
                        <option value="">Select</option>
                        @foreach($receives as $receive)
                            @if(isset($return) && ($return->receive_id == $receive->id))
                                <option value="{{ $receive->id }}"
                                        selected> {{ $receive->invoice_number }} - {{ $receive->supplier->name }}</option>
                            @else
                                <option value="{{ $receive->id }}"> {{ $receive->invoice_number }} - {{ $receive->supplier->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Return Invoice Number</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="invoice_number"
                           value="{{ isset($return) ? $return->invoice_number : '' }}"
                           @if($action == ACTION_VIEW) disabled @endif>
                </div>
            </div>
            {{--<div class="form-group">
                <label class="col-sm-2 control-label">Return Date</label>
                <div class="col-sm-3">
                    <input type="text" @if($action == ACTION_VIEW) disabled @endif value="{{ isset($return) ? $return->transaction_date : '' }}" id="return_date" class="form-control"/>
                </div>
            </div>--}}
            @if($action == ACTION_CREATE)
                <div class="form-group">
                    <label class="col-sm-2 control-label">Import</label>
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
                        <input type="text" readonly value="{{TRANSACTION_ORDER_STATUS[$return['status_id']]}}"
                               class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Created By</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$return['user_admin']['username']}}"
                               class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Created Date</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$return['created_date']}}" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Updated By</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$return['user_admin_updated']['username']}}"
                               class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Update Date</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$return['updated_date']}}" class="form-control"/>
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
                    <th>Sub total</th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($return_details))
                    @foreach($return_details as $key=>$return_detail)
                        <tr>
                            <input type="hidden" id="material_detail_id"
                                   value="{{ $return_detail->material_detail_id }}">
                            <td>{{ $return_detail->material_detail_code }}</td>
                            <td>{{ $return_detail->material_detail_name }}</td>
                            <td>{{ $return_detail->smaller_uom_detail_name }}</td>
                            <td>{{ $return_detail->uom_name }}</td>
                            <td>{{ $return_detail->quantity }}</td>
                            <td>{{ number_format($return_detail->price, 2) }}</td>
                            <td>{{ number_format($return_detail->total, 2) }}</td>
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
                        <th>Sub Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(!empty($return_accounts))
                        @foreach($return_accounts as $key=>$return_account)
                            <tr>
                                <input type="hidden" id="account_id" value="{{ $return_account['account_id'] }}">
                                <td>{{ $return_account['code'] }}</td>
                                <td>{{ $return_account['name'] }}</td>
                                <td>{{ number_format($return_account['total'], 2) }}</td>
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
                            <input type="text" disabled="true" class="form-control" id="grand-total"
                                   value="{{ isset($return) ? number_format($return->total, 2) : '' }}">
                        </div>
                    </div>
                </div>
            </div>
            <br>

            @if($action == ACTION_CREATE)
                <div class="text-center">
                    <button type="submit" onclick="submitForm()" class="btn btn-primary">Submit</button>
                </div>
            @endif
        </div>

    </div>

    <input type="hidden" id="supplier_code">
</div>
<script src="{{admin_asset("/js/inventory/return_order.js?v=" . time())}}"></script>
<script>
    $(function () {
        $('#return_date').datetimepicker({
            "format": "YYYY-MM-DD"
        });
    });
</script>
