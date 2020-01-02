<section class="content-header">
    <h1 class="inline-block">
        Order Details
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
                            <th class="text-left w80 bold">Id</th>
                            <th class="text-left w80 bold">PluCode</th>
                            <th class="text-left w80 bold">Price</th>
                            <th class="text-left w80 bold">Sub Price</th>
                            <th class="text-left bold">Short Name</th>
                            <th class="text-left bold">Menu Name</th>
                            <th class="text-left w220 bold">Menu Price</th>
                            <th class="text-left bold">Variant Name</th>
                            <th class="text-left bold">Add-on Price</th>
                            <th class="text-left bold">Add-on Name</th>
                        </tr>
                        <tr><td class="" colspan="10"></td></tr>
                        @if(count($detail) > 0)
                        @foreach($detail as $row)
                            <tr>
                                <td class="text-left">{{ $row->id }}</td>
                                <td class="text-left">{{ $row->plucode }}</td>
                                <td class="text-left">{{ $PosHelper::format_amount($row->price) }}</td>
                                <td class="text-left">{{ $PosHelper::format_amount($row->sub_price)}}</td>
                                <td class="text-left">{{ $row->short_name}}</td>
                                <td class="text-left">{{ $row->menu_name}}</td>
                                <td class="text-left">{{ $PosHelper::format_amount($row->menu_price)}}</td>
                                <td class="text-left">{{ $row->variant_name}}</td>
                                <td class="text-left">{{ $PosHelper::format_amount($row->variant_price)}}</td>
                                <td class="text-left">{{ $row->addon_name}}</td>
                            </tr>
                        @endforeach
                        @else
                            <tr><td colspan="10">Not Found Data!</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div><!-- /.box -->
            </div>
        </div><!-- /.col -->
    </div><!-- ./row -->
</section>
