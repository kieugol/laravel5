<div class="box box-info daily-batch">
    <div class="box-header with-border">
        <h3 class="box-title">Create</h3>
        <div class="box-tools">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="{!! url('admin/inventory/wasted-material') !!}" class="btn btn-sm btn-default btn-home"><i
                            class="fa fa-list"></i>&nbsp;List</a>
            </div>

            <div class="btn-group pull-right" style="margin-right: 10px">
                <a class="btn btn-sm btn-default form-history-back"><i class="fa fa-arrow-left"></i>&nbsp;Back</a>
            </div>
        </div>
    </div>

    <div class="box-body">
        <form class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-2 control-label">Material</label>
                <div class="col-sm-3">
                    <select class="form-control col-sm-8" id="material">
                        <option value="">Select</option>
                        @foreach($material_details as $material_detail)
                            <option uom="{{ $material_detail->report_uom_name }}" value="{{ $material_detail->id }}"> {{ $material_detail->code . ' - ' . $material_detail->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Quantity</label>
                <div class="col-sm-3">
                    <input class="form-control" type="number" id="quantity" min="0" value="0">
                </div>
                <label class="col-sm-1" id="uom_name"></label>
            </div>
        </form>
        <br>
        <div class="text-center">
            <button type="submit" onclick="submitForm()" class="btn btn-primary">Submit</button>
        </div>
        <br>
    </div>
</div>
<script>
    $('#material').on('change', function (){
        var selected = $(this).find('option:selected');
        var uom = selected.attr("uom");

        $('#uom_name').html(uom);
    })
    function submitForm() {
        swal({
            title: "Are you sure want to create this wasted material?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
            closeOnConfirm: true,
            cancelButtonText: "No"
        }, function () {
            var is_validate = true;
            var msg = '';

            if ($('#quantity').val() == '' || $('#quantity').val() == 0) {
                is_validate = false;
                msg = 'Please choose quantity';
            }

            if ($('#material').val() == '') {
                is_validate = false;
                msg = 'Please choose material';
            }

            if (!is_validate) {
                toastr.error(msg);
            } else {
                var params = {
                    'material': $('#material').val(),
                    'quantity': $('#quantity').val()
                }
                $.ajax({
                    url: route('inventory/wasted-material/save'),
                    method: "POST",
                    dataType: "json",
                    contentType: 'application/json',
                    data: JSON.stringify(params),
                    success: function (response) {
                        $('.btn-home').click()
                        toastr.clear()
                        toastr.success(response.message)
                    },
                    error: function (error) {
                        swal({
                            title: '',
                            type: 'error',
                            text: error.responseJSON.message,
                            html: true
                        })
                    }
                })
            }
        });
    };

    $('#material').select2({
        allowClear: true,
        placeholder :'Select Material...'
    });
</script>
