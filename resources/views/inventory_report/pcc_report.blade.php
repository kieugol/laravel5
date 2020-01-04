@extends($layout)
@section('content')
    <section class="content">
        <div id="box-report" class="bg-white p20">
            <div>
                <br>
                <h4 class="title-header-pcc">SARIMELATI KENCANA, PT.</h4>
                <br>
                <h4 class="title-header-pcc">PERIODIC COST CALCULATION  {{$period}}</h4>
                <h4 class="title-header-pcc">Code Outlet:  {{$outlet_code}}</h4>
                <h4 class="title-header-pcc">Initial: {{$outlet_code}}</h4>
                <h4 class="title-header-pcc">Outlet: {{$outlet_information}}</h4>
                <h4 class="title-header-pcc">Restaurant Manager: {{$current_user}}</h4>
                <h4 class="title-header-pcc">Store Keeper: </h4>
                <h4 class="title-header-pcc">Period:  {{$month}} {{$year}}</h4>
                <br>
            </div>
            @php $classCommon = 'text-left vertical-align-middle bold'; $styleCommon = "border:1px solid lightgray"; @endphp
            <div class="freeze-table">
                <table class="table pad5 no-border">
                    <thead>
                        <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                            <th class="{{$classCommon}}" rowspan="2" style="{{$styleCommon}}">Code</th>
                            <th class="{{$classCommon}}" rowspan="2" style="{{$styleCommon}}">Product</th>
                            <th class="{{$classCommon}}" rowspan="2" style="{{$styleCommon}}">Unit</th>
                            <th class="{{$classCommon}}" colspan="3" style="{{$styleCommon}}">Beginning Inventory</th>
                            {{--Dynamic period--}}
                            @foreach($column_date as $date)
                                <th class="{{$classCommon}}" colspan="3" style="{{$styleCommon}}">Date {{$date}}</th>
                            @endforeach()
                            <th class="{{$classCommon}}" colspan="3" style="{{$styleCommon}}">Total In
                                Period {{$period}}</th>
                            <th class="{{$classCommon}} word-one-line" rowspan="2" style="{{$styleCommon}}">Total Avail</th>
                            <th class="{{$classCommon}}  word-one-line" rowspan="2" style="{{$styleCommon}}">End Inv</th>
                            <th class="{{$classCommon}}" rowspan="2" style="{{$styleCommon}}">Usage</th>
                            <th class="{{$classCommon}}" rowspan="2" style="{{$styleCommon}}">Price</th>
                            <th class="{{$classCommon}}  word-one-line" rowspan="2" style="{{$styleCommon}}">Cost of Sales</th>
                            <th class="{{$classCommon}}  word-one-line" rowspan="2" style="{{$styleCommon}}">Total Ending</th>
                            <th class="{{$classCommon}}  word-one-line" rowspan="2" style="{{$styleCommon}}">Price Variance</th>
                            <th class="{{$classCommon}}" rowspan="2" style="{{$styleCommon}}">DII</th>
                            <th class="{{$classCommon}}  word-one-line" rowspan="2" style="{{$styleCommon}}">Explanation</th>
                        </tr>
                        <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                            {{--Beginning Inventory--}}
                            <th class="{{$classCommon}}" style="{{$styleCommon}}">Qty</th>
                            <th class="{{$classCommon}}" style="{{$styleCommon}}">Price</th>
                            <th class="{{$classCommon}}" style="{{$styleCommon}}">Total</th>

                            {{--Dynamic period--}}
                            @foreach($column_date as $date)
                                <th class="{{$classCommon}}" style="{{$styleCommon}}">Qty</th>
                                <th class="{{$classCommon}}" style="{{$styleCommon}}">Price</th>
                                <th class="{{$classCommon}}" style="{{$styleCommon}}">Total</th>
                            @endforeach()
                            {{--Total In Period--}}
                            <th class="{{$classCommon}}" style="{{$styleCommon}}">Qty</th>
                            <th class="{{$classCommon}}" style="{{$styleCommon}}">Price</th>
                            <th class="{{$classCommon}}" style="{{$styleCommon}}">Total</th>

                        </tr>
                    </thead>
                    <tbody>
                    {{-- Detail Group and Account--}}
                @foreach($data_group as $groupId => $groupDetail)
                    @if ($groupId == $key_total_group_fb || !isset($data_account[$groupId]))
                        @php continue; @endphp
                    @endif
                    @foreach($data_account[$groupId] as $accountId => $accountDetail)
                        <tr>
                            <td></td>
                            <td class="bold">{{$master_account[$accountId]}}</td>
                            <td></td>
                            <td colspan="{{$total_group_col_date + 11}}"></td>
                        </tr>
                        {{-- Detail product--}}
                        @foreach($data_detail[$accountId] as $item)
                            <tr>
                                <td class="text-left">{{$item["material_detail_code"]}}</td>
                                <td class="text-left word-one-line">{{$item["product"]}}</td>
                                <td class="text-left">{{$item["unit"]}}</td>
                                <td class="text-right">{{format_excel_number($item["qty"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["price"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["total"], $character_format)}}</td>
                                {{--Dynamic period--}}
                                @for($key = 1; $key <= $total_date_range; $key++)
                                    <td class="text-right">{{format_excel_number($item["_qty_$key" ], $character_format)}}</td>
                                    <td class="text-right">{{format_excel_number($item["_price_$key" ], $character_format)}}</td>
                                    <td class="text-right">{{format_excel_number($item["_total_$key" ], $character_format)}}</td>
                                @endfor
                                <td class="text-right">{{format_excel_number($item["_qty_period"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["_price_period"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["_total_period"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["_total_avail"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["_end_inv"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["_usage"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["_end_price"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["_cost_of_sales"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["_total_ending"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["_price_variance"], $character_format)}}</td>
                                <td class="text-right">{{format_excel_number($item["dll"], $character_format)}}</td>
                                <td>{{$item["explanation"]}}</td>
                            </tr>
                        @endforeach()
                        {{-- Total by account--}}
                        <tr>
                            @php $accountNameTotal = "TOTAL ". strtoupper($master_account[$accountId]); @endphp
                            <td></td>
                            <td class="bold">{{$accountNameTotal}}</td>
                            <td></td>

                            <td class="text-right bold word-one-line" colspan="2" align="right">Total Beginning</td>
                            <td class="text-right bold">{{format_excel_number($accountDetail["total_beginning"], $character_format)}}</td>
                            @foreach($column_date as $date)
                                <td class="text-right bold word-one-line" colspan="2" align="right">Total Date {{$date}}</td>
                                <td class="text-right bold">{{format_excel_number($accountDetail["_total_$date" ], $character_format)}}</td>
                            @endforeach
                            <td class="text-right bold word-one-line" colspan="2" align="right">Total In Period {{$period}}</td>
                            <td class="text-right bold">{{format_excel_number($accountDetail["_total_period"], $character_format)}}</td>

                            <td class="text-right bold" colspan="4">{{$accountNameTotal}}</td>
                            <td class="text-right bold">{{format_excel_number($accountDetail["_total_cost_of_sales"], $character_format)}}</td>
                            <td class="text-right bold">{{format_excel_number($accountDetail["_total_ending"], $character_format)}}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach  {{--End foreach Account--}}
                    {{-- Total by group--}}
                    <tr>
                        @php $groupNameTotal = "TOTAL ". strtoupper($master_group[$groupId]); @endphp
                        <td></td>
                        <td class="bold"></td>
                        <td></td>

                        <td class="text-right bold word-one-line" colspan="2" align="right"></td>
                        <td class="text-right bold"></td>
                        @foreach($column_date as $date)
                            <td class="text-right bold word-one-line" colspan="2" align="right"></td>
                            <td class="text-right bold"></td>
                        @endforeach
                        <td class="text-right bold word-one-line" colspan="2" align="right"></td>
                        <td class="text-right bold"></td>

                        <td class="text-right bold" colspan="4">{{$groupNameTotal}}</td>
                        <td class="text-right bold">{{format_excel_number($groupDetail["_total_cost_of_sales"], $character_format)}}</td>
                        <td class="text-right bold">{{format_excel_number($groupDetail["_total_ending"], $character_format)}}</td>

                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="{{$total_group_col_date + 18}}"></td>
                    </tr>
                @endforeach {{--End foreach Group--}}
                    <tr>
                        <td colspan="3"></td>
                        <td colspan="{{$total_group_col_date + 15}}"></td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td colspan="{{$total_group_col_date + 15}}"></td>
                    </tr>
                    <tr>
                        <td class="bold" colspan="3">
                        <td colspan="3" class="text-left bold word-one-line">TOTAL COST OF SALES</td>
                        <td class="bold text-right word-one-line">ENDING INV.</td>
                        <td colspan="{{$total_group_col_date - 1}}"></td>
                        <td colspan="3" class="text-left bold word-one-line">TOTAL COST OF SALES</td>
                        <td class="bold text-right word-one-line">ENDING INV.</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    {{--Summary total type--}}
                    @foreach($data_type as $typeId => $typeDetail)
                        @php $typeNameTotal = "TOTAL ". strtoupper($master_type[$typeId]); @endphp
                        <tr>
                            <td colspan="3" class="bold">{{$typeNameTotal}}</td>
                            <td colspan="3" class="text-right bold">{{$typeDetail["_total_cost_of_sales"]}}</td>
                            <td class="text-right bold">{{$typeDetail["_total_ending"]}}</td>
                            <td colspan="{{$total_group_col_date - 1}}"></td>
                            <td colspan="3" class="text-right bold">{{$typeDetail["_total_cost_of_sales"]}}</td>
                            <td class="text-right bold">{{$typeDetail["_total_ending"]}}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach()
                    <tr>
                        <td colspan="3"></td>
                        <td colspan="{{$total_group_col_date + 15}}"></td>
                    </tr>
                    {{--Total group--}}
                    @foreach($data_group as $groupId => $groupDetail)
                        @php $typeNameGroup = "TOTAL ". strtoupper($master_group[$groupId]); @endphp
                        <tr>
                            <td colspan="3" class="bold">{{$typeNameGroup}}</td>
                            <td colspan="3" class="text-right bold">{{$groupDetail["_total_cost_of_sales"]}}</td>
                            <td class="text-right bold">{{$groupDetail["_total_ending"]}}</td>
                            <td colspan="{{$total_group_col_date -1}}"></td>
                            <td colspan="3" class="text-right bold">{{$groupDetail["_total_cost_of_sales"]}}</td>
                            <td class="text-right bold">{{$groupDetail["_total_ending"]}}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach()
                    <tr>
                        <td colspan="3"></td>
                        <td colspan="{{$total_group_col_date + 15}}"></td>
                    </tr>
                    {{--Total sale--}}
                    @foreach($data_sale as $groupId => $costSale)
                        @php
                            $saleGroupName = $groupId == $key_total_group_fb ? 'TOTAL SALES' : "SALES " . strtoupper($master_group[$groupId]);
                        @endphp
                        <tr>
                            <td colspan="3" class="bold">{{$saleGroupName}}</td>
                            <td colspan="3" class="text-right bold">{{$costSale}}</td>
                            <td class="text-right bold"></td>
                            <td colspan="{{$total_group_col_date - 1}}"></td>
                            <td colspan="3" class="text-right bold">{{$costSale}}</td>
                            <td class="text-right bold"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3"></td>
                        <td colspan="{{$total_group_col_date + 15}}"></td>
                    </tr>
                    {{--total PERCENTAGE--}}
                    @foreach($data_sale as $groupId => $costSale)
                        @php
                            $percentGroupName = "PERCENTAGE ". strtoupper($master_group[$groupId]);
                            $percentCostSale = division($data_group[$groupId]["_total_cost_of_sales"] * 100, $costSale, 2);
                        @endphp
                        <tr>
                            <td colspan="3" class="bold">{{$percentGroupName}}</td>
                            <td colspan="3" class="text-right bold">{{$percentCostSale}}</td>
                            <td class="text-right bold"></td>
                            <td colspan="{{$total_group_col_date - 1}}"></td>
                            <td colspan="3" class="text-right bold">{{$percentCostSale}}</td>
                            <td class="text-right bold"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3"></td>
                        <td colspan="{{$total_group_col_date + 15}}"></td>
                    </tr>
                    {{--STOCK TURN--}}
                    <tr>
                        <td class="bold" colspan="3">
                        <td colspan="3" class="text-left bold"></td>
                        <td class="bold word-one-line">PERIODIC TURN OVER</td>
                        <td colspan="{{$total_group_col_date - 1}}"></td>
                        <td colspan="3" class="text-left bold"></td>
                        <td class="bold word-one-line">PERIODIC TURN OVER</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @foreach($data_type as $typeId => $typeDetail)
                        @php
                            $stockTypeName = "STOCK TURN OVER ". strtoupper($master_type[$typeId]);
                            $stockTurnOverType = division($typeDetail["_total_ending"] * $periodic_turn_over, $typeDetail["_total_cost_of_sales"], 2);
                        @endphp
                        <tr>
                            <td colspan="3" class="bold">{{$stockTypeName}}</td>
                            <td colspan="3" class="text-right bold">{{$stockTurnOverType}}</td>
                            <td class="text-right bold">{{$periodic_turn_over}}</td>
                            <td colspan="{{$total_group_col_date - 1}}"></td>
                            <td colspan="3" class="text-right bold">{{$stockTurnOverType}}</td>
                            <td class="text-right bold">{{$periodic_turn_over}}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach()
                    <tr>
                        <td colspan="3"></td>
                        <td colspan="{{$total_group_col_date + 15}}"></td>
                    </tr>
                    @foreach($data_group as $groupId => $groupDetail)
                        @php
                            $stockNameGroup = "STOCK TURN OVER ". strtoupper($master_group[$groupId]);
                            $stockTurnOverGroup = division($groupDetail["_total_ending"] * $periodic_turn_over, $groupDetail["_total_cost_of_sales"], 2);
                        @endphp
                        <tr>
                            <td colspan="3" class="bold">{{$stockNameGroup}}</td>
                            <td colspan="3" class="text-right bold">{{$stockTurnOverGroup}}</td>
                            <td class="text-right bold">{{$periodic_turn_over}}</td>
                            <td colspan="{{$total_group_col_date - 1}}"></td>
                            <td colspan="3" class="text-right bold">{{$stockTurnOverGroup}}</td>
                            <td class="text-right bold">{{$periodic_turn_over}}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if(empty($is_exported_file))
                <div class="scroll-down mt10 text-center">
                    <a class="btn btn-sm btn-default form-history-back scroll-down"><i class="fa fa-arrow-left"></i>&nbsp;Back</a>
                </div>
            @endif()
        </div>
    </section>
    <style>
        .title-header-pcc {
            font-size: 14px !important;
            font-weight: bold;
        }
        .scroll-down {

        }
        .word-one-line {
            white-space: nowrap;
        }
    </style>

    <script type="text/javascript" src="{{admin_asset('js/freeze-table.js')}}"></script>
    <script>
      $(document).ready(function() {
        $('.freeze-table').freezeTable({
          freezeHead: true,
          columnNum: 3,
          scrollBar: true
        });
      });
    </script>
@endsection
