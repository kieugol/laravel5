@extends($layout)
@section('content')
@include('report.module_filter')
<section class="content">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div id="box-report" class="bg-white p20">
                <div class="row">
                    @include('report.header')
                </div>
                <table class="table no-border">
                    <thead>
                        <tr>
                            <th>PAYMENT</th>
                            <th class="text-center">BILL</th>
                            <th class="text-center">DATE</th>
                            <th class="text-center">CARD NO</th>
                            <th>APPROVAL CODE</th>
                            <th>CUSTOMER</th>
                            <th>CASHIER</th>
                            <th>REMARKS</th>
                            <th class="text-right">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $item): ?>
                            <?php if (!empty($item->data)): ?>
                                <?php foreach ($item->data as $key => $subitem): ?>
                                    <tr >
                                        <td><strong>{{ $key == 0 ? $subitem->payment_method_name : '' }}</strong></td>
                                        <td class="text-center">{{ $subitem->number }}</td>
                                        <td class="text-center">{{ $subitem->date }}</td>
                                        <td class="text-center">{{ $subitem->card_number }}</td>
                                        <td class="text-center">{{ $subitem->approval_code }}</td>
                                        <td>{{ $subitem->name }}</td>
                                        <td>{{ $subitem->cashier_name }}</td>
                                        <td>{{ $subitem->remark }}</td>
                                        <td class="text-right">{{ $subitem->value_format }}</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr style="border-bottom: 1px dashed gray !important">
                                <td><strong></strong></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="bg-gray" style="padding-top: 7px">SUB TOTAL</td>
                                <td class="bg-gray text-right" style="border-bottom: 1px dashed gray !important"><strong>{{ $item->sub_total_format }}</strong></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td colspan="9" style="padding:5px"></td>
                        </tr>
                        <tr class="bg-primary">
                            <td><strong></strong></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td style="padding: 10px 0px" class="text-white">GRAND TOTAL</td>
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