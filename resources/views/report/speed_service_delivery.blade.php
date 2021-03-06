@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table pad5 no-border">
            <tbody>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class="text-center bold" rowspan="2" style="border:1px solid lightgray; vertical-align: middle">Time</th>
                    <th class="text-center bold" rowspan="2" style="border:1px solid lightgray; vertical-align: middle">Bill</th>
                    <th class="text-center bold" colspan="4" style="border:1px solid lightgray">Make Time</th>
                    <th class="text-center bold" colspan="4" style="border:1px solid lightgray">Production Time</th>
                    <th class="text-center bold" colspan="4" style="border:1px solid lightgray">In Store Time</th>
                    <th class="text-center bold" colspan="8" style="border:1px solid lightgray">Delivery Time</th>
                </tr>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    {{--<th class="text-center bold" style="border:1px solid lightgray">&lt; 1 "</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>--}}
                    <th class="text-center bold" style="border:1px solid lightgray">&lt; 3"</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>
                    <th class="text-center bold" style="border:1px solid lightgray">&gt; 3"</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>

                    <th class="text-center bold" style="border:1px solid lightgray">&lt; 10"</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>
                    <th class="text-center bold" style="border:1px solid lightgray">&gt; 10"</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>


                    <th class="text-center bold" style="border:1px solid lightgray">&lt; 10"</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>
                    <th class="text-center bold" style="border:1px solid lightgray">&gt; 10"</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>


                    <th class="text-center bold" style="border:1px solid lightgray">&lt; 30"</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>
                    <th class="text-center bold" style="border:1px solid lightgray">30 - 45"</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>
                    <th class="text-center bold" style="border:1px solid lightgray">&gt; 45"</th>
                    <th class="text-center bold" style="border:1px solid lightgray">%</th>
                </tr>
                <?php foreach ($data as $hour => $row): ?>
                    <?php $hour = strlen($hour) == 1 ? "0$hour" : $hour; ?>
                    <tr>
                        <td style="width:90px" class="text-center"><?php echo "$hour:00 - $hour:59" ?></td>
                        <td class="text-center">{{ $row['total'] }}</td>

                        {{--<td class="text-center">{{ $row['maketime']['1'] }}</td>
                        <td class="text-center">{{ division($row['maketime']['1'], $row['total']) * 100 . "%" }}</td>--}}
                        <td class="text-center">{{ $row['maketime']['2'] }}</td>
                        <td class="text-center">{{ division($row['maketime']['2'], $row['total']) * 100 . "%" }}</td>
                        <td class="text-center">{{ $row['maketime']['3'] }}</td>
                        <td class="text-center">{{ division($row['maketime']['3'], $row['total']) * 100 . "%" }}</td>

                        <td class="text-center">{{ $row['productiontime']['1'] }}</td>
                        <td class="text-center">{{ division($row['productiontime']['1'], $row['total']) * 100 . "%" }}</td>
                        <td class="text-center">{{ $row['productiontime']['2'] }}</td>
                        <td class="text-center">{{ division($row['productiontime']['2'], $row['total']) * 100 . "%" }}</td>

                        <td class="text-center">{{ $row['instoretime']['1'] }}</td>
                        <td class="text-center">{{ division($row['instoretime']['1'], $row['total']) * 100 . "%" }}</td>
                        <td class="text-center">{{ $row['instoretime']['2'] }}</td>
                        <td class="text-center">{{ division($row['instoretime']['2'], $row['total']) * 100 . "%" }}</td>

                        <td class="text-center">{{ $row['deliverytime']['1'] }}</td>
                        <td class="text-center">{{ division($row['deliverytime']['1'], $row['total']) * 100 . "%" }}</td>
                        <td class="text-center">{{ $row['deliverytime']['2'] }}</td>
                        <td class="text-center">{{ division($row['deliverytime']['2'], $row['total']) * 100 . "%" }}</td>
                        <td class="text-center">{{ $row['deliverytime']['3'] }}</td>
                        <td class="text-center">{{ division($row['deliverytime']['3'], $row['total']) * 100 . "%" }}</td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="text-center">Total</td>
                    <td class="text-center">{{ $total['total'] }}</td>

                    {{--<td class="text-center">{{ $total['maketime']['1'] }}</td>
                    <td class="text-center">{{ division($total['maketime']['1'], $total['total']) * 100 . "%" }}</td>--}}
                    <td class="text-center">{{ $total['maketime']['2'] }}</td>
                    <td class="text-center">{{ division($total['maketime']['2'], $total['total']) * 100 . "%" }}</td>
                    <td class="text-center">{{ $total['maketime']['3'] }}</td>
                    <td class="text-center">{{ division($total['maketime']['3'], $total['total']) * 100 . "%" }}</td>

                    <td class="text-center">{{ $total['productiontime']['1'] }}</td>
                    <td class="text-center">{{ division($total['productiontime']['1'], $total['total']) * 100 . "%" }}</td>
                    <td class="text-center">{{ $total['productiontime']['2'] }}</td>
                    <td class="text-center">{{ division($total['productiontime']['2'], $total['total']) * 100 . "%" }}</td>

                    <td class="text-center">{{ $total['instoretime']['1'] }}</td>
                    <td class="text-center">{{ division($total['instoretime']['1'], $total['total']) * 100 . "%" }}</td>
                    <td class="text-center">{{ $total['instoretime']['2'] }}</td>
                    <td class="text-center">{{ division($total['instoretime']['2'], $total['total']) * 100 . "%" }}</td>

                    <td class="text-center">{{ $total['deliverytime']['1'] }}</td>
                    <td class="text-center">{{ division($total['deliverytime']['1'], $total['total']) * 100 . "%" }}</td>
                    <td class="text-center">{{ $total['deliverytime']['2'] }}</td>
                    <td class="text-center">{{ division($total['deliverytime']['2'], $total['total']) * 100 . "%" }}</td>
                    <td class="text-center">{{ $total['deliverytime']['3'] }}</td>
                    <td class="text-center">{{ division($total['deliverytime']['3'], $total['total']) * 100 . "%" }}</td>
                </tr>
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<style>@media print{@page {size: landscape} }</style>
@endsection
