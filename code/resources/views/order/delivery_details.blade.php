<section class="content-header">
    <h1 class="inline-block">
        Delivery Details
        <small>List</small>
    </h1>
    <div class="btn-group pull-right btn-back">
        <a href="{{ url('/admin/order') }}" class="btn btn-sm btn-default">
            <i class="fa fa-arrow-circle-left"></i>&nbsp;&nbsp;Back
        </a>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body pad table-responsive">
                    <table class="table pad5 no-border">
                        <tbody>
                        <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                            <th class="text-left w80 bold">Address</th>
                            <th class="text-left w80 bold">Zone</th>
                        </tr>
                        <tr><td class="" colspan="15"></td></tr>
                        @if(count($detail) > 0)
                            @foreach($detail as $row)
                                <tr>
                                    <td class="text-left">{{ $row->address }}</td>
                                    <td class="text-left">{{ $row->zone}}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="2">Not Found Data!</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div><!-- /.box -->
            </div>
        </div><!-- /.col -->
    </div><!-- ./row -->
</section>