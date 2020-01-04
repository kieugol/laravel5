@extends($layout)

@section('content')

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        @include('report.module_filter')
        <section class="content">
            <div id="box-report" class="bg-white p20">
                @include('report.header')
                <table class="table pad5 no-border">
                    <tbody>
                        <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                            <th class="text-center bold" colspan="2">DESCRIPTION</th>
                            <th class="text-right bold">DINE IN</th>
                            <th class="text-right bold">TAKE AWAY</th>
                            <th class="text-right bold">DELIVERY</th>
                            <th class="text-right bold">EAT IN</th>
                            <th class="text-right bold">TOTAL</th>
                        </tr>
                        <?php if ($data): ?>
                            <tr>
                                <td class="text-right" colspan="2">BILL</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['count']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['count']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['count']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['count']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">AVG/CHECKBILL</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['avg_amount']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['avg_amount']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['avg_amount']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['avg_amount']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">COVER</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['count']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['count']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['count']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['count']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">AVG CHECK/COVER</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['avg_amount']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['avg_amount']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['avg_amount']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['avg_amount']) }}</td>
                            </tr>
                            <tr>
                                <td class="bold" colspan="7">REVENUE</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">FOOD GROSS SALES</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['food_gross_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['food_gross_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['food_gross_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['food_gross_sales']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">BVRG GROSS SALES</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['bvrg_gross_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['bvrg_gross_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['bvrg_gross_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['bvrg_gross_sales']) }}</td>
                            </tr>
                            <tr>
                                <td class="bold" colspan="2">TOTAL F & B GROSS</td>
                                <td class="text-right bold">0</td>
                                <td class="text-right bold">{{ number_format($data['C']['food_gross_sales'] + $data['C']['bvrg_gross_sales']) }}</td>
                                <td class="text-right bold">{{ number_format($data['D']['food_gross_sales'] + $data['D']['bvrg_gross_sales']) }}</td>
                                <td class="text-right bold">{{ number_format($data['I']['food_gross_sales'] + $data['I']['bvrg_gross_sales']) }}</td>
                                <td class="text-right bold">{{ number_format($data['total']['food_gross_sales'] + $data['total']['bvrg_gross_sales']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">DISCOUNT FOOD</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['discount_food']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['discount_food']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['discount_food']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['discount_food']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">DISCOUNT BVRG</td>
                                <td class="text-right">0</td>
                                <td class="text-right">0</td>
                                <td class="text-right">0</td>
                                <td class="text-right">0</td>
                                <td class="text-right">0</td>
                            </tr>
                            <tr>
                                <td class="bold" colspan="2">TOTAL DISCOUNT</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['total_discount']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['total_discount']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['total_discount']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['total_discount']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">FOOD NET SALES</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['food_net_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['food_net_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['food_net_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['food_net_sales']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">BVRG NET SALES</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['bvrg_net_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['bvrg_net_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['bvrg_net_sales']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['bvrg_net_sales']) }}</td>
                            </tr>
                            <tr>
                                <td class="bold" colspan="2">TOTAL F & B NETT</td>
                                <td class="text-right bold">0</td>
                                <td class="text-right bold">{{ number_format($data['C']['food_net_sales'] + $data['C']['bvrg_net_sales']) }}</td>
                                <td class="text-right bold">{{ number_format($data['D']['food_net_sales'] + $data['D']['bvrg_net_sales']) }}</td>
                                <td class="text-right bold">{{ number_format($data['I']['food_net_sales'] + $data['I']['bvrg_net_sales'])  }}</td>
                                <td class="text-right bold">{{ number_format($data['total']['food_net_sales'] + $data['total']['bvrg_net_sales']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">DELIVERY COST</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['delivery_cost']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['delivery_cost']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['delivery_cost']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['delivery_cost']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">OTHERS</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['others']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['others']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['others']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['others']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">RESTAURANT TAX</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['restaurant_tax']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['restaurant_tax']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['restaurant_tax']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['restaurant_tax']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">TRANSFER OUT</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['transfer_out']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['transfer_out']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['transfer_out']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['transfer_out']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan="2">DOWN PAYMENT</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['down_payment']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['down_payment']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['down_payment']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['down_payment']) }}</td>
                            </tr>
                            <tr class="bg-gray">
                                <td class="text-right" colspan="2">DONASI</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['donasi']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['donasi']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['donasi']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['donasi']) }}</td>
                            </tr>
                            <tr class="bold t15">
                                <td colspan="2" style="color:red">GRAND TOTAL</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['total_f_b_net'] + $data['C']['delivery_cost'] + $data['C']['restaurant_tax'] + $data['C']['donasi']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['total_f_b_net'] + $data['D']['delivery_cost'] + $data['D']['restaurant_tax'] + $data['D']['donasi']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['total_f_b_net'] + $data['I']['delivery_cost'] + $data['I']['restaurant_tax'] + $data['I']['donasi']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['total_f_b_net'] + $data['total']['delivery_cost'] + $data['total']['restaurant_tax'] + $data['total']['donasi']) }}</td>
                            </tr>
                            <tr style="background:#f19c00; color: white">
                                <td class="bold" colspan="7">PAYMENT TYPE</td>
                            </tr>
                            @foreach( $data['payment_methods'] as $payment )
                            <tr>
                                <td class="text-right"></td>
                                <td class="text-right">{{ $payment->payment_method_name }}</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['payment_method'][$payment->payment_method_id]) }}</td>
                                <td class="text-right">{{ number_format($data['D']['payment_method'][$payment->payment_method_id]) }}</td>
                                <td class="text-right">{{ number_format($data['I']['payment_method'][$payment->payment_method_id]) }}</td>
                                <td class="text-right">{{ number_format($data['total']['payment_method'][$payment->payment_method_id]) }}</td>
                            </tr>
                            @endforeach
                            <tr class="bold">
                                <td class="text-left" colspan="2">Total Payment</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['total_payment']['payment_method']['C']) }}</td>
                                <td class="text-right">{{ number_format($data['total_payment']['payment_method']['D']) }}</td>
                                <td class="text-right">{{ number_format($data['total_payment']['payment_method']['I']) }}</td>
                                <td class="text-right">{{ number_format($data['total_payment']['payment_method']['total']) }}</td>
                            </tr>

                            <tr class="bold">
                                <td class="text-left" colspan="2">VOID ITEM & BILL</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ $data['C']['void']['count'] }}</td>
                                <td class="text-right">{{ $data['D']['void']['count'] }}</td>
                                <td class="text-right">{{ $data['I']['void']['count'] }}</td>
                                <td class="text-right">{{ $data['total']['void']['count'] }}</td>
                            </tr>

                            <tr class="bold">
                                <td class="text-left" colspan="2">Rp.</td>
                                <td class="text-right">0</td>
                                <td class="text-right">{{ number_format($data['C']['void']['value']) }}</td>
                                <td class="text-right">{{ number_format($data['D']['void']['value']) }}</td>
                                <td class="text-right">{{ number_format($data['I']['void']['value']) }}</td>
                                <td class="text-right">{{ number_format($data['total']['void']['value']) }}</td>
                            </tr>

                            <tr class="bold">
                                <td class="text-left" colspan="2">UPDATE PAYMENT TYPE</td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                            </tr>

                            <tr class="bg-info">
                                <td class="bold bg-primary" colspan="7">THIRD PARTY</td>
                            </tr>
                            @foreach( $data['third_party'] as $third_party )
                            <tr class="<?php echo $third_party['name'] == "" ? "bold" : "" ?>">
                                <td class="text-right"></td>
                                <td class="text-right">{{ $third_party['name'] }}</td>
                                <td class="text-right">{{ $third_party['count'] }}</td>
                                <td class="text-right">{{ number_format($third_party['C']) }}</td>
                                <td class="text-right">{{ number_format($third_party['D']) }}</td>
                                <td class="text-right">{{ number_format($third_party['I']) }}</td>
                                <td class="text-right">{{ number_format($third_party['total']) }}</td>
                            </tr>
                            @endforeach
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php echo view("report.footer"); ?>
            </div>
        </section>
    </div>
</div>
<style>@media print{@page {size: landscape} body{zoom: 70% !important} }</style>
@endsection
