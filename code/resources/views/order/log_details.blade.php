<section class="content-header">
    <h1 class="inline-block">
        Log Status
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
                            <th class="text-left w80 bold">New Status</th>
                            <th class="text-left w80 bold">Reason</th>
                            <th class="text-left bold">Device</th>
                            <th class="text-left bold">Timestamp</th>
                        </tr>
                        <tr><td class="" colspan="15"></td></tr>
                        @if(count($detail) > 0)
                            @foreach($detail as $row)
                                <tr>
                                    <td class="text-left">
                                        @if(($row->status_name) == "Finished")
                                            <span class="label label-success">{{ $row->status_name}}</span>
                                        @elseif(($row->status_name) == "Parked")
                                            <span class="label label-primary">{{ $row->status_name}}</span>
                                        @elseif(($row->status_name) == "Ordered")
                                            <span class="label label-primary">{{ $row->status_name}}</span>
                                        @elseif(($row->status_name) == "Checkout")
                                            <span class="label label-info">{{ $row->status_name}}</span>
                                        @elseif(($row->status_name) == "Cancelled")
                                            <span class="label label-danger">{{ $row->status_name}}</span>
                                        @elseif(($row->status_name) == "Cooked")
                                            <span class="label label-primary">{{ $row->status_name}}</span>
                                        @elseif(($row->status_name) == "Delivering")
                                            <span class="label label-primary">{{ $row->status_name}}</span>
                                        @elseif(($row->status_name) == "Editing")
                                            <span class="label label-warning">{{ $row->status_name}}</span>
                                        @elseif(($row->status_name) == "Delivered")
                                            <span class="label label-success">{{ $row->status_name}}</span>
                                        @elseif(($row->status_name) == "Cooking")
                                            <span class="label label-info">{{ $row->status_name}}</span>
                                        @endif
                                    </td>
                                    <td class="text-left">{{ $row->reason}}</td>
                                    <td class="text-left">{{ $row->device}}</td>
                                    <td class="text-left">{{ $row->created_status_log}}</td>

                                </tr>
                            @endforeach
                        @else
                            <tr><td>Not Found Data!</td></tr>
                        @endif

                        </tbody>
                    </table>
                </div><!-- /.box -->
            </div>
        </div><!-- /.col -->
    </div><!-- ./row -->
</section>