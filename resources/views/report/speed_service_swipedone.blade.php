@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table pad5 no-border">
            <tbody>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class="text-center bold" style="border:1px solid lightgray; vertical-align: middle">Hours</th>
                    <th class="text-center bold" style="border:1px solid lightgray; vertical-align: middle">Driver tracker</th>
                    <th class="text-center bold" style="border:1px solid lightgray">Percentage</th>
                    <th class="text-center bold" style="border:1px solid lightgray">Without driver tracker</th>
                    <th class="text-center bold" style="border:1px solid lightgray">Percentage</th>
                    <th class="text-center bold" style="border:1px solid lightgray">< 30" driver tracker</th>
                    <th class="text-center bold" style="border:1px solid lightgray">Percentage</th>
                </tr>
                <?php foreach ($data as $hour => $row): ?>
                    <?php $hour = strlen($hour) == 1 ? "0$hour" : $hour; ?>
                    <tr>
                        <td style="width:90px" class="text-center"><?php echo "$hour:00 - $hour:59" ?></td>

                        <td class="text-center">{{ $row['driver_tracker'] }}</td>
                        <td class="text-center">{{ division($row['driver_tracker'], $row['total']) * 100 . "%" }}</td>

                        <td class="text-center">{{ $row['not_driver_tracker'] }}</td>
                        <td class="text-center">{{ division($row['not_driver_tracker'], $row['total']) * 100 . "%" }}</td>

                        <td class="text-center">{{ $row['under_30mins_driver_tracker'] }}</td>
                        <td class="text-center">{{ division($row['under_30mins_driver_tracker'], $row['total']) * 100 . "%" }}</td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td class="text-center">Total: {{ $total['total'] }}</td>

                    <td class="text-center">{{ $total['driver_tracker'] }}</td>
                    <td class="text-center">{{ division($total['driver_tracker'], $total['total']) * 100 . "%" }}</td>
                    <td class="text-center">{{ $total['not_driver_tracker'] }}</td>
                    <td class="text-center">{{ division($total['not_driver_tracker'], $total['total']) * 100 . "%" }}</td>
                    <td class="text-center">{{ $total['under_30mins_driver_tracker'] }}</td>
                    <td class="text-center">{{ division($total['under_30mins_driver_tracker'], $total['total']) * 100 . "%" }}</td>

                </tr>
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<style>@media print{@page {size: landscape} }</style>
@endsection
