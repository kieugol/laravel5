<section class="content-header">
    <h1 class="inline-block">
        Payment Details
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
                            <th class="text-left w80 bold">Payment Method Name</th>
                            <th class="text-left w80 bold">Value</th>
                            <th class="text-left bold">Change</th>
                            <th class="text-left bold">Total Payment</th>
                            <th class="text-left w220 bold">Status</th>
                            <th class="text-left bold">Remark</th>
                            <th class="text-left bold">Action</th>
                        </tr>
                        <tr><td class="" colspan="7"></td></tr>
                        @if(count($detail) > 0)
                            @foreach($detail as $row)
                                <tr>
                                    <td class="text-left">{{ $row->payment_method_name }}</td>
                                    <td class="text-left">{{ $PosHelper::format_amount($row->value)}}</td>
                                    <td class="text-left">{{ $PosHelper::format_amount($row->change)}}</td>
                                    <td class="text-left">{{ $PosHelper::format_amount($row->total_payment)}}</td>
                                    <td class="text-left">{{ $row->status}}</td>
                                    <td class="text-left">{{ $row->remark}}</td>
                                    <td class="text-left">
                                        @if($hasPermissionEdit)
                                            <a href="{{ url('/admin/edit-order-payment/'. $row->id) }}"
                                               class="btn btn-primary" role="button"><i class="fa fa-edit"></i>  Edit
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="7">Not Found Data!</td></tr>
                        @endif
                    </tbody>
                    </table>
                </div><!-- /.box -->
            </div>
        </div><!-- /.col -->
    </div><!-- ./row -->
</section>