@extends($layout)
@section('content')
@include('report.module_filter')
<section class="content">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div id="box-report" class="bg-white p20">
                @include('report.header')
                <table class="table no-border">
                    <thead>
                        <tr>
                            <th class="text-center">BILL</th>
                            <th class="text-center">DATE</th>
                            <th class="text-center">TIME</th>
                            <th class="text-center">CARD NO</th>
                            <th class="text-right">AMOUNT</th>
                            <th class="text-center">CASHIER</th>
                            <th class="text-center">REMARK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payment_methods as $key => $payment_method): ?>
                            <tr >
                                <td><strong>{{ $payment_method['name'] }}</strong></td>
                            </tr>
                            <?php foreach ($orders[$key] as $order): ?>
                                <tr >
                                    <td class="text-center">{{ $order->number }}</td>
                                    <td class="text-center">{{ $order->date }}</td>
                                    <td class="text-center">{{ $order->time }}</td>
                                    <td class="text-center">{{ $order->card_number != "" ? "********" . $order->card_number : "" }}</td>
                                    <td class="text-right">{{ $order->amount }}</td>
                                    <td class="text-center">{{ $order->cashier_name }}</td>
                                    <td class="text-right">{{ $order->remark }}</td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="border-bottom: 1px dashed gray !important">
                                <td><strong></strong></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="bg-gray" style="padding-top: 7px">SUB TOTAL</td>
                                <td class="bg-gray text-right" style="border-bottom: 1px dashed gray !important"><strong>{{ $payment_method['sub_total_format'] }}</strong></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td colspan="7" style="padding:5px"></td>
                        </tr>
                        <tr class="bg-primary">
                            <td><strong></strong></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td style="padding: 10px 0px" class="text-white">GRAND TOTAL</td>
                            <td class="text-right text-white"><strong>{{ $grand_total }}</strong></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>
                        <tr>
                            <td><strong></strong></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td style="padding: 10px 0px">NON SALES TOTAL</td>
                            <td class="text-right"><strong>0</strong></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>
                        <tr>
                            <td><strong></strong></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td style="padding: 10px 0px">NETT SALES TOTAL</td>
                            <td class="text-right"><strong>{{ $grand_total }}</strong></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>
                    </tbody>
                </table>


                <?php echo view("report.footer"); ?>
            </div>
        </div>
    </div>
</section>

<style>@media print{@page {size: portrait }}</style>
@endsection