<div class="box box-info stock-opname">
    <div class="box-header with-border">
        <h3 class="box-title">{{ isset($stock_opname)? 'Edit' : 'Create' }}</h3>
        <input type="hidden" id="is_lock" value="{{ isset($is_lock)? $is_lock : 0 }}">

        <div class="box-tools">
            <div class="btn-group pull-right mr10">
                <a class="btn btn-sm btn-default" onclick="print_element('#print-material-detail')"><i class="fa fa-print"></i>&nbsp;Print</a>
            </div>
            <div class="btn-group pull-right mr10">
                <a href="{!! url('admin/inventory/stock-opname') !!}" class="btn btn-sm btn-default btn-list"><i class="fa fa-list"></i>&nbsp;List</a>
            </div>

            <div class="btn-group pull-right mr10">
                <a class="btn btn-sm btn-default" href="{!! url('admin/inventory/stock-opname') !!}"><i class="fa fa-arrow-left"></i>&nbsp;Back</a>
            </div>
        </div>
    </div>

    <div class="box-body">
        <form class="form-horizontal">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-sm-1 control-label">Month</label>
                        <div class="col-sm-2">
                            <input name="month" id="month" class="form-control" value="{{$month}}" @if($action == ACTION_UPDATE) disabled @endif/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label  class="col-sm-1 control-label">Year</label>
                        <div class="col-sm-2">
                            <input name="year" id="year" class="form-control" value="{{$year}}" @if($action == ACTION_UPDATE) disabled @endif/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-1 control-label" for="type_id">PCC</label>
                        <div class="col-sm-2">
                            <select class="form-control" id="pcc"  @if($action == ACTION_UPDATE) disabled @endif >
                                @foreach($pccs as $pcc)
                                    <option value="{{ $pcc->id }}" @if(isset($stock_opname) && ($stock_opname->pcc_id == $pcc->id)) selected @endif> {{ $pcc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @if($action == ACTION_UPDATED)
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Status</label>
                            <div class="col-sm-2">
                                <input type="text" readonly value="{{STOCK_OPNAME_STATUS[$stock_opname->status_id]}}" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Created By</label>
                            <div class="col-sm-2">
                                <input type="text" readonly value="{{$stock_opname->user_admin->username ?? ''}}" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Created Date</label>
                            <div class="col-sm-2">
                                <input type="text" readonly value="{{$stock_opname->created_date}}" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Updated By</label>
                            <div class="col-sm-2">
                                <input type="text" readonly value="{{$stock_opname->user_admin_updated->username ?? ''}}" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label">Updated Date</label>
                            <div class="col-sm-2">
                                <input type="text" readonly value="{{$stock_opname->updated_date}}" class="form-control"/>
                            </div>
                        </div>
                @endif
                </div>
            </div>
        </form>

        <br/>
        <form class="form-inline">
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#material" aria-controls="material" role="tab" data-toggle="tab">Material</a></li>
                        <li role="presentation"><a href="#recipe" aria-controls="recipe" role="tab" data-toggle="tab">Recipe</a></li>
                        <li role="presentation"><a href="#summary" aria-controls="summary" role="tab" data-toggle="tab">Summary</a></li>
                    </ul>

                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade in active" id="material">
                            <table  class="table table-hover" id="stock_op_name">
                                <thead>
                                <tr class="text-center">
                                    <th rowspan="2" width="120" class="vertical-align-middle">Material Code</th>
                                    <th rowspan="2" class="vertical-align-middle">Material<br/>Name</th>
                                    @foreach($locations as $location)
                                        @if($location->is_display)
                                            <th colspan="4" class="text-center vertical-align-middle {{$location->color_class}}">{{ $location->name }}</th>
                                        @endif
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach($locations as $location)
                                        @if($location->is_display)
                                            <th class="text-center vertical-align-middle">Qty</th>
                                            <th class="text-center vertical-align-middle">Outlet uom</th>
                                            <th class="text-center vertical-align-middle">Qty</th>
                                            <th class="text-center vertical-align-middle">Supplier uom</th>
                                        @endif
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @if(!empty($master_material_details))
                                    @foreach($master_material_details as $row)
                                        <tr>
                                            <td>{{ $row['code'] }}</td>
                                            <td>{{ $row['name'] }}</td>
                                            @foreach($row['locations'] as $location)
                                                @if($location['is_display'])
                                                    <td class="text-right">
                                                        <input class="w50 quantity_outlet_uom input-number" {{$disable_edit_form}} type="number" min="0" value="{{$location['quantity_outlet_uom']}}" onchange="updateQtyOutletUOM({{$row['material_detail_id']}},{{$location['id']}},this.value)"/>
                                                    </td>
                                                    <td class="text-left"><div class="w40">{{$row['report_uom_description']}}</div></td>
                                                    <td class="text-right">
                                                        <input class="w50 quantity_outlet_uom input-number" {{$disable_edit_form}} type="number" min="0" value="{{$location['quantity_supplier_uom']}}" onchange="updateQtySupplierUOM({{$row['material_detail_id']}},{{$location['id']}},this.value)"/>
                                                    </td>
                                                    <td class="text-left"><div class="w40">{{$row['supplier_uom_description']}}</div></td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="recipe">
                            <table class="table table-hover" id="stock_op_name_recipe">
                                <thead>
                                <tr class="text-center">
                                    <th width="100" class="vertical-align-middle">Recipe Code</th>
                                    <th width="250" class="vertical-align-middle">Recipe Name</th>
                                    <th width="100" class="vertical-align-middle">Recipe Qty</th>
                                    <th width="100" class="vertical-align-middle">UOM</th>
                                    <th class="vertical-align-middle">Recipe Detail</th>
                                    <th width="300" class="vertical-align-middle">Material Detail</th>
                                    <th width="150" class="vertical-align-middle text-right">Material Detail Qty</th>
                                    <th width="100" class="vertical-align-middle">Recipe UOM</th>
                                </tr>
                                </thead>
                                <tbody>

                                {{--@php--}}
                                    {{--$code = '';--}}
                                {{--@endphp--}}
                                {{--@foreach($recipe_data as $row)--}}
                                    {{--@if($row['master_pcc_id'] == $pcc_id)--}}
                                        {{--<tr>--}}
                                        {{--@if($code != $row['recipe_code'])--}}
                                            {{--<td>{{$row['recipe_code']}}</td>--}}
                                            {{--<td>{{$row['recipe_name']}}</td>--}}
                                            {{--<td>--}}
                                                {{--<input class="w100 quantity_recipe" {{$disable_edit_form}} type="number" min="0" value="{{$row['recipe_qty']}}" onchange="updateQtyRecipe(this, this.value, {{$row['recipe_detail_id']}})"/>--}}
                                            {{--</td>--}}
                                            {{--<td>{{ $row['recipe_uom_name'] }}</td>--}}
                                            {{--@php $code = $row['recipe_code']; @endphp--}}
                                        {{--@else--}}
                                            {{--<td></td>--}}
                                            {{--<td></td>--}}
                                            {{--<td></td>--}}
                                            {{--<td></td>--}}
                                        {{--@endif--}}
                                        {{--<td>{{ $row['material_name'] }}</td>--}}
                                        {{--<td>--}}
                                            {{--<select class="form-control w300 select-recipe-material" {{$disable_edit_form}} old-data="" recipe-detail-id="{{$row['recipe_detail_id']}}">--}}
                                                {{--<option value="0">Select Material Detail</option>--}}
                                                {{--@foreach($row['material_details'] as $materialDetail)--}}
                                                    {{--<option @if($materialDetail['id'] == $row['material_detail_id']) selected @endif value="{{$materialDetail['id']}}">{{$materialDetail['code']}} - {{$materialDetail['name']}}</option>--}}
                                                {{--@endforeach--}}
                                            {{--</select>--}}
                                        {{--</td>--}}
                                        {{--<td class="text-right" id="material-detail-{{$row['recipe_detail_id']}}">{{ $row['material_detail_qty'] }}</td>--}}
                                        {{--<td>{{ $row['material_recipe_uom_name'] }}</td>--}}
                                    {{--</tr>--}}
                                    {{--@endif--}}
                                {{--@endforeach--}}
                                </tbody>
                            </table>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="summary">
                            <table class="table" id="stock_op_name_summary">
                                <thead>
                                <tr class="text-center">
                                    <th width="100" class="vertical-align-middle">Material Code</th>
                                    <th class="vertical-align-middle">Material Name</th>
                                    <th width="100" class="vertical-align-middle">UOM</th>
                                    <th width="100" class="vertical-align-middle">Material</th>
                                    <th width="100" class="vertical-align-middle">Recipe</th>
                                    <th width="150" class="vertical-align-middle">Ending Inventory</th>
                                    <th width="150" class="vertical-align-middle">Total Ending</th>
                                    <th width="150" class="vertical-align-middle">Potential Ending</th>
                                    <th width="150" class="vertical-align-middle">Stock Variance</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div> <!--tab content-->
                </div>
            </div>
        </form>
    </div>
    <hr>
    <div class="box-footer">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="row">
            <div class="col-sm-12 text-center">
                <div class="btn-group">
                    @if($action == ACTION_CREATE || ($action == ACTION_UPDATE && $has_perm_edit))
                        <button type="button" id="btn-confirm" action-submit="1" class="btn hidden btn-primary pull-right btn-save">Confirm</button>
                        <button type="button" id="btn-save-daft" action-submit="0" class="btn btn btn-secondary pull-right btn-save">Save draft</button>
                    @endif
                    <input type="hidden" id="stock_opname_id" value="{{ $stock_opname->id ?? '' }}">
                    <input type="hidden" id="data_material" value="{{$material_data_json}}">
                    <input type="hidden" id="data_recipe" value="{{$recipe_data_json}}">
                    <input type="hidden" id="data_summary" value="{{$stock_opname_summary_json}}">
                    <input type="hidden" id="data_total_usage" value="{{$data_total_usage}}">
                    <input type="hidden" id="action-form" value="{{$action}}">
                    <input type="hidden" id="disable-edit-form" value="{{$disable_edit_form}}">
                </div>
            </div>
        </div>
    </div>
</div>
<section class="print-material-detail hidden">
    <div id="print-material-detail" class="bg-white p20" >
        <h4>PT. SARIMELATI KENCANA TBK.</h4>
        <br>
        <div class="row text-center">
            <h3>REPORT STOCK OPNAME BLANK</h3>
        </div>
        <br>
        <div class="col-xs-6">
            <div class="row">
                <label class="col-xs-3">Code Outlet</label>
                <label class="col-xs-5">{{ $info_print['outlet_code'] }}</label>
            </div>
            <div class="row">
                <label class="col-xs-3">Initial</label>
                <label class="col-xs-5">{{ $info_print['initial'] }}</label>
            </div>
            <div class="row">
                <label class="col-xs-3">Outlet</label>
                <label class="col-xs-5">{{ $info_print['outlet_name'] }}</label>
            </div>
            <div class="row">
                <label class="col-xs-3">Restaurant Manager</label>
                <label class="col-xs-5">{{ $info_print['manager'] }}</label>
            </div>
            <div class="row">
                <label class="col-xs-3">Store Keeper</label>
                <label class="col-xs-5">{{ $info_print['store_keeper'] }}</label>
            </div>
            <div class="row">
                <label class="col-xs-3">Period</label>
                <label class="col-xs-5" id="period">{{ $info_print['pcc_default'] }}, {{ $month }} {{$year}}</label>
            </div>
        </div>
        <div class="clearfix"></div>
        <br>
        <form class="form-inline">
            <div class="row">
                <div class="col-md-12">
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade in active" id="material">
                            <table class="table table-hover" id="material-detail-table">
                                <thead>
                                <tr>
                                    <th class="text-left">Code</th>
                                    <th class="text-left">Product</th>
                                    <th class="text-left">Contains</th>
                                    <th class="text-left">Unit</th>
                                    <th class="text-left">Store Room</th>
                                    <th class="text-left">Chiller</th>
                                    <th class="text-left">Bar</th>
                                    <th class="text-left">Kitchen</th>
                                    <th class="text-left">Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(!empty($master_material_details))
                                    @foreach($master_material_details as $row)
                                    <tr>
                                        <td>{{ $row['code'] }}</td>
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['contains'] }}</td>
                                        <td>{{ $row['report_uom_description'] }}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
<style>
    #stock_op_name_filter input {
        width: 300px;
        margin-bottom: 5px;
        margin-left: 0;
    }
    #material-detail-table tr {
        border-bottom: 3px solid black;
    }
</style>

<script src="<?php echo admin_asset("/js/inventory/stock_opname.js?v=" . time()); ?>"></script>


