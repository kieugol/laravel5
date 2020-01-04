@extends($layout)
@section('content')
<div id="print-material-detail" class="bg-white p20" >
    <div id="box-report" class="bg-white p20">
        <div>
            <br>
            <h4 class="title-header-pcc">PT. SARIMELATI KENCANA TBK.</h4>
            <br>
            <h4 class="title-header-pcc">REPORT STOCK OPNAME</h4>
            <h4 class="title-header-pcc">Code Outlet:  {{$outlet_code}}</h4>
            <h4 class="title-header-pcc">Initial: {{$outlet_code}}</h4>
            <h4 class="title-header-pcc">Outlet: {{$outlet_information}}</h4>
            <h4 class="title-header-pcc">Restaurant Manager: {{$current_user}}</h4>
            <h4 class="title-header-pcc">Store Keeper: </h4>
            <h4 class="title-header-pcc">Period:  {{$month}} {{$year}}</h4>
            <br>
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
                                @foreach($locations as $location)
                                <th class="text-right">{{ $location->name }}</th>
                                @endforeach
                                <th class="text-right">Total</th>
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
                                        <?php $total = 0; ?>
                                        @foreach($row['locations'] as $item)
                                            <?php $total += $item['qty_report_uom']; ?>
                                            <td class="text-right">{{ $item['qty_report_uom'] }}</td>
                                        @endforeach
                                        <td class="text-right"><?php echo $total; ?></td>
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
    @if(empty($is_exported_file))
        <div class="scroll-down mt10 text-center">
            <a class="btn btn-sm btn-default form-history-back scroll-down"><i class="fa fa-arrow-left"></i>&nbsp;Back</a>
        </div>
    @endif()
    </div>
</div>
@endsection
