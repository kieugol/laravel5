@extends($layout)

@section('content')
@include('report.module_filter')
<div class="row">
    <div class="col-md-10 col-md-offset-1">

        <section class="content">
            <div id="box-report" class="bg-white p20">
                @include('report.header')
                <table class="table pad5 table-net-sales">
                    <tbody>
                        <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                            <th class="text-center bold" style="border:1px solid lightgray">Time</th>
                            <th class="text-right bold" style="border:1px solid lightgray">Eat In</th>
                            <th class="text-right bold" style="border:1px solid lightgray">Takeaway</th>
                            <th class="text-right bold" style="border:1px solid lightgray">Delivery</th>
                            <th class="text-right bold" style="border:1px solid lightgray">Total</th>
                            <th class="text-right bold" style="border:1px solid lightgray">%</th>
                        </tr>
                        <?php foreach ($group['lunch']['items'] as $key => $row): ?>
                            <tr>
                                <td class="text-center"><?php echo "$key:00 - $key:59" ?></td>

                                <td class="text-right">{{ number_format($row['I']['sales']) }}</td>
                                <td class="text-right">{{ number_format($row['C']['sales']) }}</td>
                                <td class="text-right">{{ number_format($row['D']['sales']) }}</td>
                                <td class="text-right">{{ number_format($row['total']['sales']) }}</td>
                                <td class="text-right">{{ $row['total']['percent_sales'] }}</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bold bg-info">
                            <td class="text-center"><?php echo "LUNCH" ?></td>

                            <td class="text-right">{{ number_format($group['lunch']['I']['sales']) }}</td>
                            <td class="text-right">{{ number_format($group['lunch']['C']['sales']) }}</td>
                            <td class="text-right">{{ number_format($group['lunch']['D']['sales']) }}</td>
                            <td class="text-right">{{ number_format($group['lunch']['total']['sales']) }}</td>
                            <td class="text-right">{{ $group['lunch']['total']['percent_sales'] }}</td>
                        </tr>

                        <?php foreach ($group['dinner']['items'] as $key => $newrow): ?>
                            <tr>
                                <td class="text-center"><?php echo "$key:00 - $key:59" ?></td>
                                <td class="text-right">{{ number_format($newrow['I']['sales']) }}</td>
                                <td class="text-right">{{ number_format($newrow['C']['sales']) }}</td>
                                <td class="text-right">{{ number_format($newrow['D']['sales']) }}</td>
                                <td class="text-right">{{ number_format($newrow['total']['sales']) }}</td>
                                <td class="text-right">{{ $newrow['total']['percent_sales'] }}</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bold bg-info">
                            <td class="text-center"><?php echo "DINNER" ?></td>

                            <td class="text-right">{{ number_format($group['dinner']['I']['sales']) }}</td>
                            <td class="text-right">{{ number_format($group['dinner']['C']['sales']) }}</td>
                            <td class="text-right">{{ number_format($group['dinner']['D']['sales']) }}</td>
                            <td class="text-right">{{ number_format($group['dinner']['total']['sales']) }}</td>
                            <td class="text-right">{{ $group['dinner']['total']['percent_sales'] }}</td>
                        </tr>

                        <tr class="bold bg-primary">
                            <td class="text-center"><?php echo "Grand Total" ?></td>

                            <td class="text-right">{{ number_format($group['total']['I']['sales']) }}</td>
                            <td class="text-right">{{ number_format($group['total']['C']['sales']) }}</td>
                            <td class="text-right">{{ number_format($group['total']['D']['sales']) }}</td>
                            <td class="text-right">{{ number_format($group['total']['total']['sales']) }}</td>
                            <td class="text-right">{{ $group['total']['total']['percent_sales'] }}</td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <table class="table pad5 table-net-sales">
                    <tbody>
                        <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                            <th class="text-center bold" style="border:1px solid lightgray">Time</th>
                            <th class="text-right bold" style="border:1px solid lightgray">Eat In</th>
                            <th class="text-right bold" style="border:1px solid lightgray">Takeaway</th>
                            <th class="text-right bold" style="border:1px solid lightgray">Delivery</th>
                            <th class="text-right bold" style="border:1px solid lightgray">Total</th>
                            <th class="text-right bold" style="border:1px solid lightgray">%</th>
                        </tr>
                        <?php foreach ($group['lunch']['items'] as $key => $row): ?>
                            <tr>
                                <td class="text-center"><?php echo "$key:00 - $key:59" ?></td>

                                <td class="text-right">{{ number_format($row['I']['qty']) }}</td>
                                <td class="text-right">{{ number_format($row['C']['qty']) }}</td>
                                <td class="text-right">{{ number_format($row['D']['qty']) }}</td>
                                <td class="text-right">{{ number_format($row['total']['qty']) }}</td>
                                <td class="text-right">{{ $row['total']['percent_qty'] }}</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bold bg-info">
                            <td class="text-center"><?php echo "LUNCH" ?></td>

                            <td class="text-right">{{ number_format($group['lunch']['I']['qty']) }}</td>
                            <td class="text-right">{{ number_format($group['lunch']['C']['qty']) }}</td>
                            <td class="text-right">{{ number_format($group['lunch']['D']['qty']) }}</td>
                            <td class="text-right">{{ number_format($group['lunch']['total']['qty']) }}</td>
                            <td class="text-right">{{ $group['lunch']['total']['percent_qty'] }}</td>
                        </tr>

                        <?php foreach ($group['dinner']['items'] as $key => $newrow): ?>
                            <tr>
                                <td class="text-center"><?php echo "$key:00 - $key:59" ?></td>
                                <td class="text-right">{{ number_format($newrow['I']['qty']) }}</td>
                                <td class="text-right">{{ number_format($newrow['C']['qty']) }}</td>
                                <td class="text-right">{{ number_format($newrow['D']['qty']) }}</td>
                                <td class="text-right">{{ number_format($newrow['total']['qty']) }}</td>
                                <td class="text-right">{{ $newrow['total']['percent_qty'] }}</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bold bg-info">
                            <td class="text-center"><?php echo "DINNER" ?></td>

                            <td class="text-right">{{ number_format($group['dinner']['I']['qty']) }}</td>
                            <td class="text-right">{{ number_format($group['dinner']['C']['qty']) }}</td>
                            <td class="text-right">{{ number_format($group['dinner']['D']['qty']) }}</td>
                            <td class="text-right">{{ number_format($group['dinner']['total']['qty']) }}</td>
                            <td class="text-right">{{ $group['dinner']['total']['percent_qty'] }}</td>
                        </tr>

                        <tr class="bold bg-primary">
                            <td class="text-center"><?php echo "Grand Total" ?></td>

                            <td class="text-right">{{ number_format($group['total']['I']['qty']) }}</td>
                            <td class="text-right">{{ number_format($group['total']['C']['qty']) }}</td>
                            <td class="text-right">{{ number_format($group['total']['D']['qty']) }}</td>
                            <td class="text-right">{{ number_format($group['total']['total']['qty']) }}</td>
                            <td class="text-right">{{ $group['total']['total']['percent_qty'] }}</td>
                        </tr>
                    </tbody>
                </table>

                <?php echo view("report.footer"); ?>
            </div>
        </section>
    </div>
</div>
<style>
    table.table-net-sales td{padding: 2px !important; width:16.6666% !important}
    @media print{@page {size: portrait}}
</style>
@endsection