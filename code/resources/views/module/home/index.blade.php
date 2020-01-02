<section class="content">

    {{--<div class="row">
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Total Menu</h3>
                </div>

                <!-- /.box-header -->
                <div class="box-body">
                    <div class="table-responsive">
                        <h1><i class="fa fa-glass text-primary"></i> {{ $total_menu }}</h1>
                    </div>
                    <!-- /.table-responsive -->
                </div>
                <!-- /.box-body -->
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Total Order</h3>
                </div>

                <!-- /.box-header -->
                <div class="box-body">
                    <div class="table-responsive">
                        <h1><i class="fa fa-cart-plus text-success"></i> {{ $total_order }}</h1>
                    </div>
                    <!-- /.table-responsive -->
                </div>
                <!-- /.box-body -->
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Total Sku</h3>
                </div>

                <!-- /.box-header -->
                <div class="box-body">
                    <div class="table-responsive">
                        <h1><i class="fa fa-hashtag text-purple"></i> {{ $total_sku }}</h1>
                    </div>
                    <!-- /.table-responsive -->
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    </div>--}}

    <div class="row">
        <div class="col-md-4">
        </div>
        <div class="col-md-4">
            {{--<br><br><br><br>
            <button onclick="sync_menu(this)" class="btn btn-full btn-lg btn-primary btn-full">SYNC MENU</button>
            <br><br><br><br>
            <button onclick="sync_promotion(this)" class="btn btn-full btn-lg btn-primary btn-full">SYNC PROMOTION</button>--}}
            <br><br><br><br>
            <div class="form-horizontal">
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">Date</label>
                    <div class="col-sm-10">
                        <input id="date-start" class="form-control" value='{{ $from_time }}' />
                        <input type="hidden" id="opening-hour" value='{{ $opening_hour }}' />
                        {{--<input id="date-end" class="form-control" value='{{ $to_time }}' />--}}
                        {{--<input type="hidden" id="closing-hour" value='{{ $closing_hour }}' />--}}
                    </div>
                </div>
                <div class="form-group mt10 ">
                    <div class="col-sm-offset-2 col-sm-10">

                        <button onclick="$('#modal-eod').modal('show')" class="btn btn-full btn-lg btn-primary">END OF DAY</button>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-4">
        </div>

    </div>

</section>

<!--Modal-->
<div class="modal fade" id="modal-eod" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"><b>Confirmation</b></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 col-lg-12 col-md-12"><b>Do you want to EOD?</b></div>
                </div>
                <div class="mt30 text-center">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button onclick="check_eod(this)" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('#date-start').datetimepicker({
            "format": "YYYY-MM-DD"
        });
    });
</script>
