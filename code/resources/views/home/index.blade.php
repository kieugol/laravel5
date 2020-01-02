<section class="content">

    <div class="row">
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
    </div>

    <div class="row">
        <div class="col-md-4">
        </div>
        <div class="col-md-4">
            <br><br><br><br>
            <button onclick="sync_menu(this)" class="btn btn-full btn-lg btn-primary btn-full">SYNC MENU</button>
            <br><br><br><br>
            <button onclick="check_eod(this)" class="btn btn-full btn-lg btn-primary btn-full pull-right">END OF DAY</button>
        </div>
        <div class="col-md-4">
        </div>
    </div>

</section>