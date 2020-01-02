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
                    <th class="text-center">Order Time</th>
                    <th class="text-center">Make Time</th>
                    <th class="text-center">Cut Time</th>
                    <th class="text-center">Dispatch Time</th>
                    <th class="text-center">Driver Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td class="text-center">{{ $item->number }}</td>
                        <td class="text-center">{{ $item->ordertime }}</td>
                        <td class="text-center">{{ $item->maketime }}</td>
                        <td class="text-center">{{ $item->cuttime }}</td>
                        <td class="text-center">{{ $item->dispatchtime }}</td>
                        <td class="text-center">{{ $item->drivertime }}</td>
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