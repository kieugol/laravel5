@extends($layout)

@section('content')
    @include('report.module_filter')
    <section class="content">
        <div id="box-report" class="bg-white p20">
            @include('report.header')
            <div class="tbl-sale-mix-menu">
                <table class="table pad5 table-bordered">
                    <thead>
                    <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                        <th class="bold text-center" rowspan="3" style="border:1px solid lightgray; vertical-align: middle">Item</th>
                        <th class="bold text-center" rowspan="3" style="border:1px solid lightgray; vertical-align: middle">Type</th>
                        <th class="bold text-center" rowspan="3" style="border:1px solid lightgray; vertical-align: middle">Description</th>
                        <th class="text-center bold" colspan="51" style="border:1px solid lightgray">Quantity</th>
                        <th class="bold text-nowrap" rowspan="3" style="border:1px solid lightgray; vertical-align: middle">Total Qty</th>
                    </tr>

                    <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                        @foreach($time_range as $time)
                            <th class="text-center bold text-nowrap" colspan="3" style="border:1px solid lightgray">{{$time}}</th>
                        @endforeach
                    </tr>

                    <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                        @for($i = 0; $i < count($time_range); $i++)
                            <th class="text-center bold " style="border:1px solid lightgray">EL</th>
                            <th class="text-center bold" style="border:1px solid lightgray">TA</th>
                            <th class="text-center bold" style="border:1px solid lightgray">DEL</th>
                        @endfor
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item => $menuCrust)
                        <tr>
                            <td class="text-left vertical-align-top text-nowrap size" rowspan="{{ ($total_menu_by_size[$item] + 1) }}">{{ $item }}</td>
                        </tr>
                        @foreach($menuCrust as $type => $menuSale)
                            <tr>
                                <td class="text-left text-nowrap crust" rowspan="{{ (count($menuSale) + 1) }}">{{$type}}</td>
                            </tr>

                            @foreach($menuSale as $menuName => $saleHours)
                                <tr>
                                    <td class="text-left text-nowrap">{{$menuName}}</td>
                                    @foreach($saleHours['hours'] as $h => $sale)
                                        <td class="text-center EI {{$h}}">{{$sale['I']}}</td>
                                        <td class="text-center TA {{$h}}">{{$sale['C']}}</td>
                                        <td class="text-center DL {{$h}}">{{$sale['D']}}</td>
                                    @endforeach
                                    <td class="text-center TOTAL">{{$saleHours['total']}}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                    </tbody>
                </table>

                <?php echo view("report.footer"); ?>
            </div>
        </div>
    </section>
    <style>
        @media print {
            @page {
                size: landscape
            }
        }
    </style>
    <script>
      $(document).ready(function () {
        $('.tbl-sale-mix-menu').freezeTable({
          freezeHead: true,
          columnNum: 3,
          scrollBar: true
        });
      });
    </script>
@endsection
