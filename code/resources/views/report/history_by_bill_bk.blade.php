@extends($layout)
@section('content')

@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')

        <div class="list-items">
            @if (count($data) >0)
            @foreach($data as $key => $item)
            <div class="sub-item mb30">
                @php
                $total_tax = array_get($item,'amount', 0);
                $total = before_tax($total_tax); //= subtotal + delivery (exclude tax)
                $delivery_cost_tax = array_get($item,'delivery_fee', 0);
                $delivery_cost = before_tax($delivery_cost_tax);
                $subtotal  = $item['is_oc'] == 1 ? $item['sub_total'] : $total - $delivery_cost;
                
                $order_details = array_get($item,'order_details', []);
                $order_payments = array_get($item,'order_payments', []);

                @endphp
                <div class="border-top-1 item ">
                    <div class="row">
                        <div class="col-xs-9 font-11">Bill# &nbsp;&nbsp; {{ array_get($item, 'number', '') }}</div>
                        <div class="col-xs-3 font-11 text-right ">{{ array_get($item, 'order_type.name', '') }}</div>
                    </div>
                </div>

                <div class="border-bottom-1 item">
                    <div class="row">
                        <div class="col-xs-5 font-10">Cashier&nbsp; {{ array_get($item, 'user_encash.name', '') }}</div>
                        <div class="col-xs-7 font-11 text-right ">Date&nbsp;&nbsp; {{ date("d/m/y H:i", strtotime(array_get($item, 'created_date', '')) ) }}</div>
                    </div>
                </div>
                @foreach($order_details as $_key => $detail)
                @php
                $style  = '';
                $style_children  = '';
                $children = array_get($detail,'children', []);
                $price = before_tax(array_get($detail, 'price',0));
                @endphp
                <div class="item">
                    <div class="row">
                        <div class="col-xs-7 font-10">{{ array_get($detail, 'menu_name') }}</div>
                        <div class="col-xs-2 font-10">{{ array_get($detail, 'quantity') }}</div>
                        <div class="col-xs-3 font-10  text-right">{{ number_format($price) }}</div>
                    </div>
                </div>
                {{--loop children--}}
                @foreach($children as $k => $v )
                @php
                $price = 0;//before_tax(array_get($v, 'price',0));
                @endphp
                <div class="item">
                    <div class="row">
                        <div class="col-xs-7 font-10">&nbsp;&nbsp;:-{{ array_get($v, 'menu_name') }}</div>
                        <div class="col-xs-2 font-10">{{ array_get($v, 'quantity') }}</div>
                        <div class="col-xs-3 font-10 text-right ">{{ number_format($price) }}</div>
                    </div>
                </div>
                @endforeach
                @endforeach

                <div class="item border-top-1">
                    <div class="row">
                        <div class="col-xs-8 font-11 uppercase">Sub Total</div>
                        <div class="col-xs-4 font-11 text-right ">{{ number_format($subtotal) }}</div>
                    </div>
                </div>
                <div class="item">
                    <div class="row">
                        <div class="col-xs-6 font-11 uppercase">Restaurant tax</div>
                        <div class="col-xs-6 font-11 text-right ">{{ number_format($total_tax - $total) }}</div>
                    </div>
                </div>
                @if ( $delivery_cost > 0)
                <div class="item">
                    <div class="row">
                        <div class="col-xs-6 font-11 uppercase">Delivery cost</div>
                        <div class="col-xs-6 font-11 text-right ">{{ number_format($delivery_cost) }}</div>
                    </div>
                </div>
                @endif

                @foreach($order_payments as $k => $v)
                <div class="item">
                    <div class="row">
                        <div class="col-xs-6 font-11 uppercase">{{ array_get($v, 'payment_method_name') }}</div>
                        <div class="col-xs-6 font-11 text-right ">{{ number_format(array_get($v, 'value', '0')) }}</div>
                    </div>
                </div>
                @endforeach

                <div class="item padding-bottom-5 border-top-2" style="margin-left:20%;">
                    <div class="row">
                        <div class="col-xs-8 font-11 text-center">Total Payment</div>
                        <div class="col-xs-4 font-11 text-right bold">{{ number_format($total_tax) }}</div>
                    </div>
                </div>
            </div>
            @endforeach
            @else
            <h2>No Data</h2>
            @endif
        </div>

        <?php echo view("report.footer"); ?>
    </div>
</section>

<style>@media print{@page {size: landscape }}</style>
<style  type="text/css" >
    /*    .row.page, .row.item {
            margin-left: -5px !important;
            margin-right: -5px !important;
        }
        .padding-bottom-5 {
            padding-bottom: 10px;
        }
        .uppercase {
            text-transform: uppercase;
        }
    */    .border-top-2 {
        border-top: 2px solid black;
    }
    .border-bottom-1 {
        border-bottom: 1px solid black;
    }
    .border-top-3 {
        border-top: 3px solid black;
    }
    .border-top-1 {
        border-top: 1px solid black;
    }/*
    .text-right {
        text-align: right;
    }
    .row .col-xs-2,
    .row .col-xs-4,
    .row .col-xs-6,
    .row .col-xs-3,
    .row .col-xs-5,
    .row .col-xs-7,
    .row .col-xs-8,
    .row .col-xs-9 {
        padding-right: 5px;
        padding-left: 5px;
    }*/
    .list-items {
        -webkit-column-count: 4;
        -moz-column-count: 4;
        column-count: 4;
        -webkit-column-gap:40px; /* Chrome, Safari, Opera */
        -moz-column-gap:40px; /* Firefox */
        column-gap:40px;
    }
</style>
@endsection