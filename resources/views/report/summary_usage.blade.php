@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table table-bordered">
            <tbody>
                <tr style="">
                    <th class="text-center" style="">Material Code</th>
                    <th class="text-center" style="">Material Name</th>
                    <th class="text-center" style="">Usage</th>
                    <th class="text-center" style="">Unit</th>
                </tr>
                <?php foreach ($data as $code => $row): ?>
                    <tr>
                        <td style="" class="text-center">{{ $code }}</td>
                        <td class="text-left">{{ $row['name'] }}</td>

                        <td class="text-center">{{ number_format($row['usage'], 2) }}</td>
                        <td class="text-center">{{ $row['unit'] }}</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<style>@media print{@page {size: landscape} }</style>
@endsection
