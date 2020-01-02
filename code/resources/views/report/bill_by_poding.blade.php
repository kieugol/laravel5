@extends($layout)

@section('content')

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        @include('report.module_filter')
        <section class="content">
            <div id="box-report" class="bg-white p20">
                @include('report.header')
                <table class="table table-bordered" style="width:400px">
                    <thead>
                        <tr>
                            <th class="text-center w100">POD</th>
                            <th class="text-center">BILL</th>
                            <th class="text-right">AMOUNT PAYMENT</th>
                            <th class="text-right">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $item): ?>
                            <tr>
                                <td class="text-center">{{ $item->zone }}</td>
                                <td class="text-center">{{ number_format($item->total_bill) }}</td>
                                <td class="text-right">{{ number_format($item->total_amount) }}</td>
                                <td class="text-right">{{ $item->percent }}</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="t15 bold bg-primary">
                            <td class="text-center">Total</td>
                            <td class="text-center">{{ number_format($grand['total_bill']) }}</td>
                            <td class="text-right">{{ number_format($grand['total_amount']) }}</td>
                            <td class="text-right">{{ $grand['percent'] }}</td>
                        </tr>

                    </tbody>
                </table>

                <?php echo view("report.footer"); ?>
            </div>
        </section>
    </div>
</div>
<style>@media print{@page {size: portrait} }</style>

@endsection
