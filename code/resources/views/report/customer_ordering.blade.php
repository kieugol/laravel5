@extends($layout)

@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        @include('report.module_filter')
        <section class="content">
            <div id="box-report" class="bg-white p20">
                @include('report.header')
                <table class="table pad5 no-border">
                    <thead>
                        <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                            <th class="text-left w80 bold">Bill No.</th>
                            <th class="text-left w80 bold">Date</th>
                            <th class="text-left bold">Time</th>
                            <th class="text-left bold">Phone</th>
                            <th class="text-left w220 bold">Name</th>
                            <th class="text-left bold">Zone</th>
                            <th class="text-left bold">Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="" colspan="15"></td></tr>
                        <tr><td class="text-left bg-primary" colspan="15"><strong>DELIVERY</strong></td></tr>
                        @foreach($data['delivery_order'] as $row) 
                        <tr>
                            <td class="text-left">{{ $row['number']}}</td>
                            <td class="text-left">{{ $row['date']}}</td>
                            <td class="text-left">{{ $row['time']}}</td>
                            <td class="text-left">{{ $row['phone']}}</td>
                            <td class="text-left">{{ $row['name']}}</td>
                            <td class="text-left">{{ $row['do_zone']}}</td>
                            <td class="text-left">{{ $row['do_address']}}</td>
                        </tr>
                        @endforeach
                        <tr><td class="" colspan="15"></td></tr>
                        <tr><td class="text-left bg-primary" colspan="15"><strong>TAKE AWAY</strong></td></tr>
                        @foreach($data['take_away'] as $row) 
                        <tr>
                            <td class="text-left">{{ $row['number']}}</td>
                            <td class="text-left">{{ $row['date']}}</td>
                            <td class="text-left">{{ $row['time']}}</td>
                            <td class="text-left">{{ $row['phone']}}</td>
                            <td class="text-left">{{ $row['name']}}</td>
                            <td class="text-left"></td>
                            <td class="text-left"></td>
                        </tr>
                        @endforeach
                        <tr><td class="" colspan="15"></td></tr>
                        <tr>
                            <td class="text-left" colspan="2"><h5><b>Grand Total:</b></h5><hr class="under-line"/><hr class="under-line"/></td>
                            <td class="" colspan="13"><h5><b>{{$total}}</b></h5></td>
                        </tr>
                    </tbody>
                </table>

                <?php echo view("report.footer"); ?>
            </div>
        </section>
    </div>
</div>
@endsection