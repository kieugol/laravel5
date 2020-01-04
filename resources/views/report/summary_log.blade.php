@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center w100">Bill No</th>
                    <th class="text-center">POD</th>
                    <th class="text-center">Phone</th>
                    <th class="text-center">Name</th>
                    <th class="text-right">Bill Value</th>
                    <th class="text-center">Driver</th>
                    <th class="text-center">Order Taken</th>
                    <th class="text-center">Make Time</th>
                    <th class="text-center">Production Time</th>
                    <th class="text-center">Dispatch Time</th>
                    <th class="text-center">Driver Out</th>
                    <th class="text-center">Cash Out</th>
                    <th class="text-center">In Store Time</th>
                    <th class="text-center">Driver Time</th>
                    <th class="text-center">Delivery Time</th>
                </tr>
            </thead>
            <tbody> 
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td class="text-center">{{ $item->number }}</td>
                        <td class="text-center">{{ $item->zone }}</td>
                        <td class="text-center">{{ $item->phone }}</td>
                        <td class="text-center">{{ $item->name }}</td>
                        <td class="text-right">{{ number_format($item->amount) }}</td>
                        <td class="text-center">{{ $item->driver_name }}</td>
                        <td class="text-center">{{ $item->ordertaken }}</td>
                        <td class="text-center">{{ $item->maketime }}</td>
                        <td class="text-center">{{ $item->productiontime }}</td>
                        <td class="text-center">{{ $item->dispatchtime }}</td>
                        <td class="text-center">{{ $item->driverouttime }}</td>
                        <td class="text-center">{{ $item->cashouttime }}</td>
                        <td class="text-center">{{ $item->instoretime }}</td>
                        <td class="text-center">{{ $item->drivertime }}</td>
                        <td class="text-center">{{ $item->deliverytime }}</td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<script>
    $(document).ready(function () {
        $(".select2").select2();
    });
</script>
<style>@media print{@page {size: landscape}}</style>


@endsection