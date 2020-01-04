@if (!isset($is_exported_file))
    <section class="content-header">
    <div class="row">
        <div class="col-md-6">
            <form action="<?php echo url()->current() ?>" method="get" pjax-container>
            @if(isset($orderTypesMaster))
                <div class="pull-left mr10">
                    <select onchange="$('#form-filter .btn-primary').trigger('click')" name="type" class="form-control select2" style="width:180px">
                        <option value="">ALL</option>
                        @foreach ($orderTypesMaster as $key => $type)
                            <option value="{{$key}}" <?php echo request("type") == $key ? "selected" : "" ?>>{{$type}}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(isset($action_edit_order))
                <div class="pull-left mr10">
                    <select onchange="$('#form-filter .btn-primary').trigger('click')" name="action_edit" class="form-control select2" style="width:180px">
                        <option value="">ALL</option>
                        @foreach ($action_edit_order as $key => $name)
                            <option value="{{$key}}" <?php echo request("action_edit") == $key ? "selected" : "" ?>>{{$name}}</option>
                        @endforeach
                    </select>
                </div>
            @endif
                @if ($widget)
                    <div class="pull-left">{!! $widget->render() !!}</div>
                    <button type="submit" class="btn btn-primary pull-left  ml10"><i class="fa fa-filter"></i> Filter</button>
                @endif
            </form>
        </div>
        <div class="col-md-6">
            <div class="pull-right">
                @if ($base_url_download)
                    <div class="btn-group pull-right ml10">
                        <a class="btn btn-twitter"><i class="fa fa-download"></i> Export</a>
                        <button type="button" class="btn btn-twitter dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#" onclick="download_report('{{$base_url_download}}', 'excel')" target="_blank">Excel</a></li>
                            <li><a href="#"  onclick="download_report('{{$base_url_download}}', 'csv')" target="_blank">CSV</a></li>
                        </ul>
                    </div>
                @endif
                <button class="btn btn-info" onclick="print_element('#box-report')"><i
                            class="fa fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</section>
@endif
