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
                    <th class="text-center">Customer Order Time</th>
                    <th class="text-center">Csr Send KDS</th>
                    <th class="text-center">Make Table Bump</th>
                    <th class="text-center">Cut Table Bump</th>
                    <th class="text-center">Dispatch</th>
                    <th class="text-center">Swipe Done</th>
                    <th class="text-center">Encashment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td class="text-center">{{ $item->number }}</td>
                        <td class="text-center">{{ $item->make_time }}</td>
                        <td class="text-center">{{ $item->kds_time }}</td>
                        <td class="text-center">{{ $item->cooked_time }}</td>
                        <td class="text-center">{{ $item->checkout_time }}</td>
                        <td class="text-center">{{ $item->delivering_time }}</td>
                        <td class="text-center">{{ $item->delivered_time }}</td>
                        <td class="text-center">{{ $item->finished_time }}</td>
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