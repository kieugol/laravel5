@extends($layout)
@section('content')

@include('report.module_filter')
<section class="content">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div id="box-report" class="bg-white p20">
                @include('report.header')
                <table class="table no-border">
                    <tbody>
                        <tr>
                            <th class="text-center">PAYMENT</th>
                            <th class="text-right">TOTAL BILL</th>
                            <th class="text-right">AMOUNT</th>
                        </tr>
                        <?php foreach ($cashiers as $key => $cashier): ?>
                            <tr >
                                <td><strong>{{ $cashier['name'] }}</strong></td>
                            </tr>
                            <?php foreach ($cashier['payment_methods'] as $payment_method): ?>
                                <tr >
                                    <td class="text-center">{{ $payment_method['name'] }}</td>
                                    <td class="text-right">{{ $payment_method['count'] }}</td>
                                    <td class="text-right">{{ $payment_method['sub_total_format'] }}</td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="border-bottom: 1px dashed gray !important">
                                <td class="text-center"></td>
                                <td class="bg-gray text-right" style="padding-top: 7px">SUB TOTAL</td>
                                <td class="bg-gray text-right" style="border-bottom: 1px dashed gray !important"><strong>{{ $cashier['sub_total_format'] }}</strong></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td colspan="4" style="padding:5px"></td>
                        </tr>
                        <tr class="bg-primary">
                            <td class="text-center"></td>
                            <td style="padding: 10px 0px" class="text-white text-right">TOTAL</td>
                            <td class="text-right text-white"><strong>{{ $grand_total }}</strong></td>
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