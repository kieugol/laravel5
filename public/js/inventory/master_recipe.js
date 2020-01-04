var params = {
        "recipe_detail_type": null,
        "recipe_detail_id": null,
        "other_recipe_id": null,
        "recipe_id": null,
        "material_id": null,
        "usage": null,
        "status_id": null
    };

var recipe = {
        init: function () {
            this.switchRecipeDetailType();
            if (parseInt($('#is_active').val())) {
                $('#is_active').bootstrapSwitch('state', true);
            } else {
                $('#is_active').bootstrapSwitch('state', false);
            }
            if (parseInt($('#is_daily_batch').val())) {
                $('#is_daily_batch').bootstrapSwitch('state', true);
            } else {
                $('#is_daily_batch').bootstrapSwitch('state', false);
            }
            $('#is_active').bootstrapSwitch('disabled',true);
            $('#is_daily_batch').bootstrapSwitch('disabled',true);
        },
        switchRecipeDetailType: function() {
        $('input[type=radio][name=recipe_detail_type]').change(function() {
            switchRecipeDetailType(this.value);
         });
        }

    }
    recipe.init();

function showPopup(obj) {
    params.recipe_detail_id = $(obj).attr('id');
    params.recipe_id = $(obj).attr('recipe-id');
    var material_id = $(obj).attr('material-id');
    var other_recipe_id = $(obj).attr('other-recipe-id');
    $("#modal-usage").val($(obj).attr('usage'));
    if (parseInt($(obj).attr('status_id'))) {
        $('#modal_is_active').bootstrapSwitch('state', true);
    } else {
        $('#modal_is_active').bootstrapSwitch('state', false);
    }
    if (material_id != '') {
        params.material_id = material_id;
        params.other_recipe_id = null;
        $("#materials").val(material_id);
        $('#materials').trigger('change');
        $("#rad_material").prop("checked", true);
        $("#rad_recipe").prop("checked", false);
        switchRecipeDetailType('material');
    } else if (other_recipe_id != '') {
        params.material_id = null;
        params.other_recipe_id = other_recipe_id;
        $("#other_recipes").val(other_recipe_id);
        $('#other_recipes').trigger('change');
        $("#rad_material").prop("checked", false);
        $("#rad_recipe").prop("checked", true);
        switchRecipeDetailType('recipe');
    }
    $('#myModal').modal('show');
}

function confirmAction() {
    var title = 'Do you want to update this recipe detail ?';
    swal({
        title: title,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        closeOnConfirm: false,
        cancelButtonText: "No"
    }, function () {
        NProgress.start();
        params.usage = $('#modal-usage').val();
        params.status_id = $('#modal_is_active').is(":checked") ? 1 : 0;
        params.material_id = $('#materials').val();
        params.other_recipe_id = $('#other_recipes').val();
        if (params.recipe_detail_type == 'material') {
            params.other_recipe_id = null;
        } else {
            params.material_id = null;
        }
        $.ajax({
            url: route('inventory/master-recipe/update-recipe-detail'),
            method: "POST",
            data: params,
            success: function (response) {
                swal.close();
                $('#myModal').modal('hide');
                toastr.success(response.message);
                window.location.reload();
            },
            error: function (error) {
                swal({
                    title: '',
                    type: 'error',
                    text: error.responseJSON.message,
                    html: true
                })
            },
            complete: function () {
                NProgress.done();
            }
        });
    });
}

function switchRecipeDetailType(type) {
    if (type == 'material') {
        $('#choice-material').show();
        $('#choice-recipe').hide();
        params.recipe_detail_type = 'material';
    }else {
        $('#choice-material').hide();
        $('#choice-recipe').show();
        params.recipe_detail_type = 'recipe';
    }
}



