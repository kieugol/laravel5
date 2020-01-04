@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table pad5 no-border">
            <thead>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class=" bold">Group O.C</th>
                    <th class=" bold">Date</th>
                    <th class=" bold">No. Bill</th>
                    <th class="text-right bold">Amount</th>
                    <th class=" bold">Cashier</th>
                    <th class=" bold">Manager</th>
                    <th class=" bold">Remark</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($data as $group => $item): ?>
                    <tr>
                        <td colspan="7"><strong>{{ $group }}</strong></td>
                    </tr>
                    <?php foreach ($item['items'] as $payment): ?>
                        <tr>
                            <td></td>
                            <td>{{ date("d/m/Y", strtotime($payment->created_date)) }}</td>
                            <td>{{ $payment->number }}</td>
                            <td class="text-right">{{ number_format($payment->value) }}</td>
                            <td>{{ isset($users[$payment->user_id]) ? $users[$payment->user_id] : "" }}</td>
                            <td>{{ isset($users[$payment->manager_id]) ? $users[$payment->manager_id] : "" }}</td>
                            <td>{{ $payment->remark }}</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td>Sub Total</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">{{ number_format($item['total']) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bg-primary text-white bold">
                    <td>Grand Total</td>
                    <td></td>
                    <td></td>
                    <td class="text-right">{{ number_format($grand_total) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<style>@media print{@page {size: landscape} }</style>
@endsection