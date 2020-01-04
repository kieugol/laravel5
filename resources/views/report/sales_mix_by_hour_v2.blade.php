@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table pad5 no-border">
            <thead>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class="text-center bold" rowspan="2" style="border:1px solid lightgray">Time</th>
                    <th class="text-center bold" colspan="2" style="border:1px solid lightgray">Eat In</th>
                    <th class="text-center bold" colspan="2" style="border:1px solid lightgray">Takeaway</th>
                    <th class="text-center bold" colspan="2" style="border:1px solid lightgray">Delivery</th>
                    <th class="text-center bold" colspan="2" style="border:1px solid lightgray">Total</th>
                </tr>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class="text-right bold" style="border:1px solid lightgray">Quantity</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Sales</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Quantity</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Sales</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Quantity</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Sales</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Quantity</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $key => $row): ?>
                    <tr>
                        <td class="text-center"><?php echo $key == "09" ? "00:00 - 09:59" : "$key:00 - $key:59" ?></td>

                        <td class="text-right">{{ $row['I']['qty'] }}</td>
                        <td class="text-right">{{ number_format($row['I']['sales']) }}</td>
                        <td class="text-right">{{ $row['C']['qty'] }}</td>
                        <td class="text-right">{{ number_format($row['C']['sales']) }}</td>
                        <td class="text-right">{{ $row['D']['qty'] }}</td>
                        <td class="text-right">{{ number_format($row['D']['sales']) }}</td>
                        <td class="text-right">{{ $row['total']['qty'] }}</td>
                        <td class="text-right">{{ number_format($row['total']['sales']) }}</td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bold">
                    <td class="text-center">Total</td>

                    <td class="text-right">{{ $total['I']['qty'] }}</td>
                    <td class="text-right">{{ number_format($total['I']['sales']) }}</td>
                    <td class="text-right">{{ $total['C']['qty'] }}</td>
                    <td class="text-right">{{ number_format($total['C']['sales']) }}</td>
                    <td class="text-right">{{ $total['D']['qty'] }}</td>
                    <td class="text-right">{{ number_format($total['D']['sales']) }}</td>
                    <td class="text-right">{{ $total['total']['qty'] }}</td>
                    <td class="text-right">{{ number_format($total['total']['sales']) }}</td>
                </tr>
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<style>@media print{@page {size: landscape}}</style>
@endsection