@extends($layout)

@section('content')
    @include('report.module_filter')
    <section class="content">
        <div id="box-report" class="bg-white p20">
            @include('report.header')
            <h4>RECEIVING</h4>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="text-left">Supplier Code</th>
                    <th class="text-left">Supplier Name</th>
                    <th class="text-left">Invoice Number #</th>
                    <th class="text-right">Total</th>
                    <th class="text-left">Account Code</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($data['daily_receiving'] as $item)
                    <tr>
                        <td class="text-left">{{$item->supplier_code}}</td>
                        <td class="text-left">{{$item->supplier_name}}</td>
                        <td class="text-left">{{$item->invoice_number}}</td>
                        <td class="text-right">{{number_format($item->total, 2)}}</td>
                        <td class="text-left">{{$item->account_code}}</td>
                    </tr>
                @endforeach

                </tbody>
            </table>
            <br>
            <h4>RETURN</h4>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="text-left">Supplier Code</th>
                    <th class="text-left">Supplier Name</th>
                    <th class="text-left">Invoice Number #</th>
                    <th class="text-right">Total</th>
                    <th class="text-left">Account Code</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($data['daily_return'] as $item)
                    <tr>
                        <td class="text-left">{{$item->supplier_code}}</td>
                        <td class="text-left">{{$item->supplier_name}}</td>
                        <td class="text-left">{{$item->invoice_number}}</td>
                        <td class="text-right">-{{number_format($item->total, 2)}}</td>
                        <td class="text-left">{{$item->account_code}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <br>
            <h4>TRANSFER</h4>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="text-left">Outlet From</th>
                    <th class="text-left">Outlet To</th>
                    <th class="text-left">Transfer #</th>
                    <th class="text-right">Total</th>
                    <th class="text-left">Account Code</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($data['transfer'] as $item)
                    <tr>
                        <td class="text-left">{{$item->transfer->from_outlet->code . "." . $item->transfer->from_outlet->name}}</td>
                        <td class="text-left">{{$item->transfer->to_outlet->code . "." . $item->transfer->to_outlet->name}}</td>
                        <td class="text-left">{{$item->transfer->invoice_number}}</td>
                        <td class="text-right">@if($item->transfer->type == TRANSFER_TYPE_OUT)-@endif{{$item->total_transfer}}</td>
                        <td class="text-left">{{$item->account->code}}</td>
                    </tr>
                @endforeach

                </tbody>
            </table>
            <br>
            <h4>SUMMARY</h4>
            <table class="table table-bordered w400">
                <thead>
                <tr>
                    <th class="text-left">Account Code</th>
                    <th class="text-right">Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($data['daily_receiving_summary'] as $item)
                    <tr>
                        <td class="text-left">{{$item->account_code}}</td>
                        <td class="text-right">{{number_format($item->total, 2)}}</td>
                    </tr>
                @endforeach

                </tbody>
            </table>

            <h4>Potential Report</h4>
            <table class="table table-bordered w400">
                <thead>
                <tr>
                    <th class="text-center">Potential Price</th>
                    <th class="text-center">Sale</th>
                    <th class="text-center">Food and Beverage Cost</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-center">{{number_format($data['potential_price'], 2)}}</td>
                    <td class="text-center">{{number_format($data['sales'], 2)}}</td>
                    <td class="text-center">{{number_format($data['f_b_cost'], 2)}}</td>
                </tr>
                </tbody>
            </table>

            <div class="text-right">
                <button class="btn btn-info" onclick="download_report('{{$base_url_report_transaction}}', 'rar')">
                    <i class="fa fa-download"></i> Report transaction
                </button>
            </div>

            <?php echo view("report.footer"); ?>
        </div>
    </section>
    <script>
        $(document).ready(function () {
            $(".select2").select2();
        });
    </script>
    <style>@media print {
            @page {
                size: landscape
            }
        }</style>


@endsection
