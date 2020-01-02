@extends($layout)

@section('content')

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        @include('report.module_filter')
        <section class="content">
            <div id="box-report" class="bg-white p20">
                @include('report.header')
                <table class="table table-bordered" style="width:400px">
                    <tbody>
                        <tr>
                            <th class="text-center w100">Date</th>
                            <th class="text-right">Amount</th>
                        </tr>
                        <?php foreach ($data as $item): ?>
                            <tr>
                                <td class="text-center">{{ $item->date }}</td>
                                <td class="text-right">Rp. {{ number_format($item->amount) }}</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="t15 bold bg-primary">
                            <td class="text-center">Grand Total</td>
                            <td class="text-right">Rp. {{ number_format($total) }}</td>
                        </tr>

                    </tbody>
                </table>

                <?php echo view("report.footer"); ?>
            </div>
        </section>
    </div>
</div>
<style>@media print{@page {size: portrait} }</style>

@endsection
