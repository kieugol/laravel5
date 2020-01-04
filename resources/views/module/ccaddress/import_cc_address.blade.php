<section class="content-header">
    <h1 class="inline-block">
        Import Address
        <small>Address</small>
    </h1>
    <div class="btn-group pull-right btn-back">
        <a href="{{ url('/admin/cc-address') }}" class="btn btn-sm btn-default" id="btn-back">
            <i class="fa fa-arrow-circle-left"></i>&nbsp;&nbsp;Back
        </a>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form action="{{url('/admin/save-address')}}" method="post" enctype="multipart/form-data" id="import-address">
                {{ csrf_field() }}
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label text-right">Choose CSV File</label>
                    <div class="col-sm-10">
                        <input type="file" name="address_file" id="address_file" accept="file_extension">
                    </div>
                </div>

            </form>
        </div><!-- /.col -->
    </div><!-- ./row -->
    <div class="row">
        <div class="col-md-2 text-right">
            <button class="btn btn-primary" onclick="saveAddress(this)">Submit</button>
        </div>
    </div>

</section>
<script>
    function saveAddress(obj) {
        var form = $('#import-address');
        var fileInput = document.getElementById('address_file');
        var file = fileInput.files[0];
        var formData = new FormData();
        formData.append('file', file);
        NProgress.start();
        $(obj).button('loading');
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                toastr.success(response.message);
                $( "#btn-back" ).click();
            },
            error: function (response) {
                toastr.error(response.responseJSON.message);
            },
            complete: function () {
                NProgress.done();
                $(obj).button('reset');
            }
        });
    };

</script>