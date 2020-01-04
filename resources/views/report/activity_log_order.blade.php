@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table pad5 no-border">
            <thead>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class="text-center bold" style="border:1px solid lightgray">Action</th>
                    <th class="bold text-center" style="border:1px solid lightgray; vertical-align: middle">Order Code</th>
                    <th class="bold text-center" style="border:1px solid lightgray; vertical-align: middle">Sku</th>
                    <th class="text-center bold" style="border:1px solid lightgray">Plucode</th>
                    <th class="text-center bold" style="border:1px solid lightgray">Menu name</th>
                    <th class="text-center bold" style="border:1px solid lightgray">Old Qty</th>
                    <th class="text-center bold" style="border:1px solid lightgray">New Qty</th>
                    <th class="text-center bold" style="border:1px solid lightgray">Updated by</th>
                    <th class="text-center bold" style="border:1px solid lightgray">Updated date</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data as $row)
                <tr>
                    <td class="text-left">{{ $row['action_name']}}</td>
                    <td class="text-left">{{ $row['number']}}</td>
                    <td class="text-left">{{ $row['sku']}}</td>
                    <td class="text-left">{{ $row['plucode']}}</td>
                    <td class="text-left {{ $row['class_bold']}}">{{ $row['menu_name']}}</td>
                    <td class="text-right">{{ $row['old_quantity']}}</td>
                    <td class="text-right">{{ $row['quantity']}}</td>
                    <td class="text-center">{{ $row['user_name']}}</td>
                    <td class="text-center">{{ $row['updated_date']}}</td>
                </tr>
                @if(!empty($row['menu_child']))
                    @foreach($row['menu_child'] as $menuchild)
                        <tr>
                            <td class="text-left"></td>
                            <td class="text-center"></td>
                            <td class="text-left">{{ $menuchild['sku']}}</td>
                            <td class="text-left">{{ $menuchild['plucode']}}</td>
                            <td class="text-left pl15">:-{{ $menuchild['menu_name']}}</td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<style>@media print{@page {size: landscape}}</style>
@endsection
