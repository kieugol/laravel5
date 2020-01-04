@extends($layout)

@section('content')

@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table pad5 table-net-sales">
            <thead>
                <tr>
                    <th class="text-center">Bill No.</th>
                    <th style="width:70px" class="text-center">Date</th>
                    <th style="width:70px" class="text-center">Time</th>
                    <th class="text-right">Amount Before Donation</th>
                    <th class="text-right">Donation</th>
                    <th class="text-right">Amount Payment</th>
                    <th class="text-right">Cashier</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td class="text-center">{{ $item->number }}</td>
                        <td class="text-center">{{ date("d/m/Y", strtotime($item->created_date)) }}</td>
                        <td class="text-center">{{ date("H:i:s", strtotime($item->created_date)) }}</td>
                        <td class="text-right">Rp. {{ number_format($item->before_amount) }}</td>
                        <td class="text-right">Rp. {{ number_format($item->donation) }}</td>
                        <td class="text-right">Rp. {{ number_format($item->amount) }}</td>
                        <td class="text-right">{{ $item->user_name }}</td>
                    </tr>
                <?php endforeach; ?>
                <tr class="t15 bold">
                    <td colspan="3"></td>
                    <td class="text-right bg-primary">Grand Total Donation</td>
                    <td class="text-right bg-primary">Rp. {{ number_format($total) }}</td>
                    <td class="text-right"></td>
                    <td class="text-right"></td>
                </tr>
            </tbody>
        </table>
        <?php echo view("report.footer"); ?>
    </div>
</section>

<style>
    @media print{@page {size: portrait}}
</style>
@endsection
