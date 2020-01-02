<div class="col-xs-6">
    <h1>{{ $name }}</h1>
    <h4>PIZZA HUT DELIVERY INDONESIA</h4>
    <h4>PT Sarimelati Kencana</h4>
</div>
<div class="col-xs-6">
    <table class="table table-condensed no-border" style="margin:10px; width: 400px; float: right">
        <tr>
            <td style="width:100px;">OUTLET</td>
            <td>{{ $outletInformation }}</td>
        </tr>
        @if($period)
            <tr>
                <td>PERIOD</td>
                <td>{{ $period }}</td>
            </tr>
        @endif
    </table>
</div>

<div class="clearfix"></div>
<br>
