@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table pad5 no-border">
            <thead>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class="bold">Bill No.</th>
                    <th class="bold">Date</th>
                    <th class="bold">Time</th>
                    <th class="bold text-right">Amount</th>
                    <th class="bold">Cashier</th>
                    <th class="bold">Name</th>
                    <th class="bold">Partner code</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7"><h3># By Gojek</h3></td>
                </tr>
                <?php foreach ($gojeks as $item): ?>
                    <tr>
                        <td style="width:160px">{{ $item->number }}</td>
                        <td style="width:85px">{{ $item->date }}</td>
                        <td style="width:60px">{{ $item->time }}</td>
                        <td class="text-right">{{ $item->amount_format }}</td>
                        <td>{{ $item->cashier_name }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->partner_code }}</td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="7"><h3># By Grab</h3></td>
                </tr>
                <?php foreach ($grabs as $item): ?>
                <tr>
                    <td style="width:160px">{{ $item->number }}</td>
                    <td style="width:85px">{{ $item->date }}</td>
                    <td style="width:60px">{{ $item->time }}</td>
                    <td class="text-right">{{ $item->amount_format }}</td>
                    <td>{{ $item->cashier_name }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->partner_code }}</td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="7"><h3># By Others</h3></td>
                </tr>
                <?php foreach ($others as $item): ?>
                <tr>
                    <td style="width:160px">{{ $item->number }}</td>
                    <td style="width:85px">{{ $item->date }}</td>
                    <td style="width:60px">{{ $item->time }}</td>
                    <td class="text-right">{{ $item->amount_format }}</td>
                    <td>{{ $item->cashier_name }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->partner_code }}</td>
                </tr>
                <?php endforeach; ?>

                <tr class="bg-primary">
                    <td class="bold text-white" style="border-bottom: 1px solid black">Grand Total</td>
                    <td class="bold" colspan="2"></td>
                    <td class="bold text-right text-white"
                        style="border-bottom: 1px solid black">{{ $grand_total_format }}</td>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <td class="bold" colspan="3">Total Count: {{ $count }}</td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
@endsection