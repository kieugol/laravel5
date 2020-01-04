<div class="box box-info daily-batch">
    <div class="box-header with-border">
        <h3 class="box-title">Create</h3>
        <div class="box-tools">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="{!! url('admin/inventory/daily-batch') !!}" class="btn btn-sm btn-default btn-home"><i
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
                <label class="col-sm-2 control-label">Recipe</label>
                <div class="col-sm-3">
                    <select class="form-control col-sm-8" id="recipe">
                        <option value="">Select</option>
                        @foreach($recipes as $recipe)
                            <option recipe-usage="{{ $recipe->usage }}" recipe-uom="{{ $recipe->uom_name }}" value="{{ $recipe->id }}"> {{ $recipe->code . ' - ' . $recipe->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Quantity</label>
                <div class="col-sm-3">
                    <input class="form-control" type="number" id="quantity" min="0" value="0">
                </div>
                <label class="col-sm-1">Batch</label>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Result</label>
                <div class="col-sm-3">
                    <input class="form-control" readonly id="result-usage" min="0" value="0">
                </div>
                <label class="col-sm-1" id="result-uom">Batch</label>
            </div>
        </form>
        <br>
        <div class="box-body table-responsive row">
            <table class="table table-hover" id="tbl-recipe-detail">
                <thead>
                <tr>
                    <th>Recipe/Material Code</th>
                    <th>Recipe/Material Name</th>
                    <th>Usage Detail</th>
                    <th>Uom</th>
                    <th>Price</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <br>
        </div>
        <div class="text-center">
            <button type="submit" onclick="submitForm()" class="btn btn-primary">Submit</button>
        </div>
        <br>
    </div>
</div>
<script>
    var table = $('#tbl-recipe-detail').DataTable({
        searching: false,
        paging:   false,
        ordering: false,
        data: []
    })
    $('#recipe').on('change', function (){
        table.clear().draw()
        data = []
        var recipe_id = $('#recipe').val()
        var usage = $('option:selected', this).attr('recipe-usage');
        var uom = $('option:selected', this).attr('recipe-uom');
        $('#result-usage').val(usage);
        $('#result-uom').text(uom);
        $('#quantity').val(1);//reset quantity after choosing recipe

        if (recipe_id) {
            $.ajax({
                url: route('inventory/master-recipe/get-recipe-detail/'+recipe_id),
                method: "GET",
                dataType: "json",
                contentType: 'application/json',
                success: function (response) {
                    response.data.forEach(function (item) {
                        table.row.add([item.recipe_detail_code, item.recipe_detail_name, item.usage, item.uom, item.price])
                    })
                    table.draw()
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
    })
    function submitForm() {
        swal({
            title: "Are you sure want to create this daily batch?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
            closeOnConfirm: true,
            cancelButtonText: "No"
        }, function () {
            if ($('#recipe').val() == '') {
                toastr.error('Please choice recipe');
            } else {
                var params = {
                    'recipe': $('#recipe').val(),
                    'quantity': $('#quantity').val()
                }
                $.ajax({
                    url: route('inventory/daily-batch/save'),
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

    $('#recipe').select2({
        allowClear: true,
        placeholder :'Select Recipe...'
    });

    //change result usage value when quantity changed
    $('#quantity').on('change', function (){
        var usage = $('option:selected', $('#recipe')).attr('recipe-usage');
        $('#result-usage').val(usage*$(this).val());
    })
</script>
