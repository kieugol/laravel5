@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Bill No</th>
                    <th class="text-center">Production Time</th>
                    <th class="text-center">In Store Time</th>
                    <th class="text-center">Delivery Time</th>
                    <th class="text-center">Service Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td class="text-center">{{ $item->number }}</td>
                        <td class="text-center">{{ $item->productiontime }}</td>
                        <td class="text-center">{{ $item->instoretime }}</td>
                        <td class="text-center">{{ $item->deliverytime }}</td>
                        <td class="text-center">{{ $item->servicetime }}</td>
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
<style>@media print{@page {size: landscape} }</style>
@endsection