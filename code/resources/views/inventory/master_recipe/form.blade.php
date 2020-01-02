<div class="box box-info receive-order">
    <div class="box-header with-border">
        <h3 class="box-title">{{ isset($recipe)? 'Edit' : 'Create' }}</h3>
        <div class="box-tools">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="{!! url('admin/inventory/master-recipe') !!}" class="btn btn-sm btn-default btn-reload"><i
                            class="fa fa-list"></i>&nbsp;List</a>
            </div>

            <div class="btn-group pull-right" style="margin-right: 10px">
                <a class="btn btn-sm btn-default form-history-back"><i class="fa fa-arrow-left"></i>&nbsp;Back</a>
            </div>
        </div>
    </div>

    <div class="box-body">
        <form class="form-horizontal row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-3 control-label">Code</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-gray" id="code" disabled="true"
                               value="{{ isset($recipe) ? $recipe->code : '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Uom</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-gray" id="code" disabled="true"
                               value="{{ isset($recipe) ? $recipe->uom->name : '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Sku</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-gray" id="sku" disabled="true"
                               value="{{ isset($recipe) ? $recipe->sku : '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Expired In</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-gray" id="expired_in" disabled="true"
                               value="{{ isset($recipe) ? $recipe->expired_in : '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Active</label>
                    <div class="col-sm-3">
                            <input id="is_active" value="{{ $recipe->is_active }}" type="checkbox">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-3 control-label">Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-gray" id="name" disabled="true"
                               value="{{ isset($recipe) ? $recipe->name : '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Plucode</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-gray" id="plucode" disabled="true"
                               value="{{ isset($recipe) ? $recipe->plucode : '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Usage</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-gray" id="usage" disabled="true"
                               value="{{ isset($recipe) ? $recipe->usage : '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Price</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-gray" id="price" disabled="true"
                               value="{{ isset($recipe) ? $recipe->price : '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Daily Batch</label>
                    <div class="col-sm-3">
                        <input id="is_daily_batch" value="{{ $recipe->is_daily_batch }}" type="checkbox">
                    </div>
                </div>
            </div>
        </form>
        <br>
        <div class="box-body table-responsive row">
            <table class="table table-hover" id="tbl-recipe-detail">
                <thead>
                    <tr>
                        <th>Recipe/Material Code</th>
                        <th>Recipe/Material Name</th>
                        <th>Weight</th>
                        <th>Usage Detail</th>
                        <th>Uom</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @if(!empty($recipe_details))
                    @foreach($recipe_details as $key => $recipe_detail)
                        <tr class="recipe-details {{ empty($recipe_detail->material_id) ? 'success' : '' }}">
                            <td>{{ empty($recipe_detail->material_id) ? $recipe_detail->other_recipe->code :  $recipe_detail->material->code}}</td>
                            <td>{{ empty($recipe_detail->material_id) ? $recipe_detail->other_recipe->name :  $recipe_detail->material->name }}</td>
                            <td>{{ $recipe_detail->weight }}</td>
                            <td class="recipe-detail-usage">{{ $recipe_detail->usage }}</td>
                            <td>{{ empty($recipe_detail->material_id) ? $recipe_detail->other_recipe->uom->name :  $recipe_detail->material->recipe_uom->name }}</td>
                            <td>{{ ($recipe_detail->is_active == STATUS_ACTIVE) ? 'Active' : 'Deactive' }}</td>
                            <td>{{ empty($recipe_detail->material_id) ? $recipe_detail->other_recipe->price :  $recipe_detail->material->price }}</td>
                            <td>
                                <button class="btn btn-sm btn-info" recipe-id="{{ $recipe_detail->recipe_id }}" other-recipe-id="{{ $recipe_detail->other_recipe_id }}" material-id="{{ $recipe_detail->material_id }}"
                                        other-recipe-name="{{ empty($recipe_detail->material_id) ? $recipe_detail->other_recipe->name :  '' }}"
                                        material-name="{{ empty($recipe_detail->other_recipe_id) ? $recipe_detail->material->name :  '' }}"
                                        id="{{ $recipe_detail->id }}"  usage="{{$recipe_detail->usage}}" status_id="{{ $recipe_detail->is_active }}"
                                        onclick="showPopup(this)">Edit</button>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
            <br>
        </div>
    </div>

    <!--Modal-->
    <div id="myModal" class="modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Recipe Detail</h4>
                </div>
                <div class="modal-body">
                    <label class="radio-inline"><input type="radio" name="recipe_detail_type" id="rad_material" value="material" checked>Using material</label>
                    <label class="radio-inline"><input type="radio" name="recipe_detail_type" id="rad_recipe" value="recipe">Using others recipe</label>
                    <form class="form-horizontal material-form">
                        <div class="form-group" id="choice-material">
                            <label class="col-sm-3 text-left">Material</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="materials">
                                    @foreach($materials as $material)
                                        <option material-name="{{ $material->name }}" value="{{ $material->id }}">{{ $material->code }} - {{ $material->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="choice-recipe" style="display: none;">
                            <label class="col-sm-3 text-left">Recipe</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="other_recipes">
                                    @foreach($recipes as $recipe)
                                        <option recipe-name="{{ $recipe->name }}" value="{{ $recipe->id }}" >{{ $recipe->code }} - {{ $recipe->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 text-left">Recipe Detail Usage</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="modal-usage" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 text-left">Recipe Detail Status</label>
                            <div class="col-sm-3">
                                <input id="modal_is_active" type="checkbox">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="confirmAction()">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="{{admin_asset("/js/inventory/master_recipe.js?v=" . time())}}"></script>
<script>
    $(function () {
        $('#materials, #other_recipes').select2({
            width: 420,
            dropdownParent: $("#myModal")
        });
    });
</script>
