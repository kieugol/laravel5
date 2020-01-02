@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table table-condensed table-bordered pad1" style="width: 100%">
            <tbody>
                <?php foreach ($data as $date => $orders): ?>
                    <tr>
                        <td colspan="7"><h4 style="text-decoration: underline"><strong>Date {{ $date }}</strong></h4>
                        </td>
                    </tr>

                    <?php foreach ($orders as $i => $order): ?>
                        <tr <?php echo $i > 0 ? 'style="border-top:2px solid gray"' : "" ?>>
                            <td>Bill</td>
                            <td class="bold">{{ $order->number }}</td>
                            <td>Cashier</td>
                            <td class="bold">{{ $order->name }}</td>
                            <td>Void ID</td>
                            <td colspan="2">{{ $order->void_name }}</td>
                        </tr>
                        <tr>
                            <td>Cust.</td>
                            <td><?php echo $order->customer ? $order->customer->name : "" ?></td>
                            <td>Order Time</td>
                            <td>{{ $order->created_date }}</td>
                            <td>Reason</td>
                            <td colspan="2">{{ $order->void_reason }}</td>
                        </tr>
                        <tr>
                            <td>Telp.</td>
                            <td>{{ $order->phone }}</td>
                            <td>Void Time</td>
                            <td>{{ $order->void_time }}</td>
                            <td>Manage Allow</td>
                            <td>{{ $order->void_admin_name }}</td>
                        </tr>
                        <tr>
                            <td>Type</td>
                            <td>{{ strtoupper($order_types[$order->order_type_id]) }}</td>
                            <td></td>
                            <td></td>
                            <td style="text-decoration: underline">Qty</td>
                            <td style="text-decoration: underline">UnitPrice</td>
                            <td style="text-decoration: underline">Remark</td>
                        </tr>
                        <?php foreach ($order->details as $k => $detail): ?>
                            <tr style="<?php echo $k == 0 ? "border-top: 2px solid #d3d3d3 !important" : "" ?>">
                                <td></td>
                                <td></td>
                                <td >{{ isset($listsku[$detail->plucode]) ? $listsku[$detail->plucode] : "" }}</td>
                                <td>{{ $detail->short_name ? $detail->short_name : $detail->menu_name }}</td>
                                <td>{{ $detail->quantity }}</td>
                                <td>{{ number_format($detail->price) }}</td>
                                <td>{{ $detail->remark }}</td>
                            </tr>


                        <?php endforeach; ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="bold bg-info">{{ $order->sub_quantity }}</td>
                            <td class="bold bg-info">{{ number_format($order->sub_total) }}</td>
                            <td></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <tr class="bg-primary t16">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="bold">Grand Total</td>
                    <td class="bold">{{ number_format($grand['quantity']) }}</td>
                    <td class="bold">{{ number_format($grand['total']) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<style>@media print{@page {size: portrait} }</style>
@endsection