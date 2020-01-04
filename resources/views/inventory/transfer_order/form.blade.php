<div class="box box-info transfer-order">
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
                <label class="col-sm-2 control-label">Transfer Type</label>
                <div class="col-sm-3">
                    <select class="form-control col-sm-8" id="type" @if($action == ACTION_VIEW) disabled @endif>
                        <option value="">Select</option>
                        @foreach(TRANSFER_TYPE as $index => $type)
                            <option @if(isset($transfer) && ($transfer->type == $index)) selected @endif value="{{ $index }}"> {{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Outlet From</label>
                <div class="col-sm-3">
                    <select class="form-control col-sm-8" id="from_outlet_id" @if($action == ACTION_VIEW) disabled @endif>
                        <option value="0">Select</option>
                        @foreach($outlets as $outlet)
                            <option @if(isset($transfer) && ($transfer->from_outlet_id == $outlet->id)) selected @endif data-code="{{$outlet->code}}" value="{{ $outlet->id }}"> {{ $outlet->code . ' - ' . $outlet->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label outlet_to">Outlet To</label>
                <div class="col-sm-3">
                    <select class="form-control col-sm-8" id="to_outlet_id" @if($action == ACTION_VIEW) disabled @endif>
                        <option value="0">Select</option>
                        @foreach($outlets as $outlet)
                            <option @if(isset($transfer) && ($transfer->to_outlet_id == $outlet->id)) selected @endif data-code="{{$outlet->code}}" value="{{ $outlet->id }}"> {{ $outlet->code . ' - ' . $outlet->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Transfer No</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="invoice_number"
                           value="{{ isset($transfer) ? $transfer->invoice_number : '' }}"
                           @if($action == ACTION_VIEW) disabled @endif>
                </div>
            </div>
            {{--<div class="form-group">
                <label class="col-sm-2 control-label">Transfer Date</label>
                <div class="col-sm-3">
                    <input type="text" @if($action == ACTION_VIEW) disabled @endif value="{{ isset($transfer) ? $transfer->transaction_date : '' }}" id="transfer_date" class="form-control"/>
                </div>
            </div>--}}
            @if($action == ACTION_CREATE)
                <div class="form-group" id="transfer-in">
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
                    <label class="col-sm-2 control-label">Created By</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$transfer['user_admin']['username']}}"
                               class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Created Date</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$transfer['created_date']}}" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Updated By</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$transfer['user_admin_updated']['username']}}"
                               class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Update Date</label>
                    <div class="col-sm-3">
                        <input type="text" readonly value="{{$transfer['updated_date']}}" class="form-control"/>
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
                @if(!empty($transfer_details))
                    @foreach($transfer_details as $key => $transfer_detail)
                        <tr>
                            <td>{{ $transfer_detail->material_detail_code }}</td>
                            <td>{{ $transfer_detail->material_detail_name }}</td>
                            <td>{{ $transfer_detail->smaller_uom_detail_name }}</td>
                            <td>{{ $transfer_detail->uom_name }}</td>
                            <td>{{ $transfer_detail->quantity }}</td>
                            <td>{{ number_format($transfer_detail->price, 2) }}</td>
                            <td>{{ number_format($transfer_detail->total, 2) }}</td>
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
                    @if(!empty($transfer_accounts))
                        @foreach($transfer_accounts as $key => $transfer_account)
                            <tr>
                                <td>{{ $transfer_account['code'] }}</td>
                                <td>{{ $transfer_account['name'] }}</td>
                                <td>{{ number_format($transfer_account['total'], 2) }}</td>
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
                                   value="{{ isset($transfer) ? number_format($transfer->total, 2) : '' }}">
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

    <input type="hidden" id="store_code" value="{{$store_code}}">
    <input type="hidden" id="transfer_type_in" value="{{TRANSFER_TYPE_IN}}">
    <input type="hidden" id="transfer_type_out" value="{{TRANSFER_TYPE_OUT}}">
</div>
<script src="{{admin_asset("/js/inventory/transfer_order.js?v=" . time())}}"></script>
<script>
    $(function () {
        $('#transfer_date').datetimepicker({
            "format": "YYYY-MM-DD"
        });
    });
    $('#from_outlet_id, #to_outlet_id').select2({
        width: '100%',
        allowClear: true,
        placeholder :'Select'
    });
</script>
