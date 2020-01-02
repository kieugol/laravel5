var action_create = 1;
var action_save_prod = 1;
var action_update = 2;
var id = $("#stock_opname_id").val();
var pcc = parseInt($("#pcc").val());
var action_form = parseInt($("#action-form").val());
var disable_edit_form = $("#disable-edit-form").val();
var btn_back_home_page = $(".btn-list");
var btn_confirm = $("#btn-confirm");
var btn_save_daft = $("#btn-save-daft");
var tbl_material = $("#stock_op_name");
var tbl_recipe = $("#stock_op_name_recipe");
var tbl_summary = $("#stock_op_name_summary");
var data_recipe = JSON.parse($("#data_recipe").val());
var data_summary = JSON.parse($("#data_summary").val());
var params_material = JSON.parse($("#data_material").val());
var params_merging = JSON.parse($("#data_material").val());
var data_total_usage = JSON.parse($("#data_total_usage").val());
var params_summary = {};
var json_data = {};
var params_recipe = {};
var dtTableSummary = undefined;
var is_lock = $('#is_lock').val();

var stock_opname = {
  init: function () {
    // disable all input if this PCC is locked
    if (is_lock == 1) {
      swal({
        title: '',
        type: 'warning',
        text: 'This PCC is locked!',
        html: true
      })
      $('input').prop('disabled', true);
      $('.btn-save').hide();
    }

    $('#month').datetimepicker({"format": "MM"});
    $('#year').datetimepicker({"format": "YYYY"});
    $('#materials').select2({width: '300px'});

    this.saveStockOpname();
    this.loadPCCByPeriod();
    this.parseRecipeData();
    if (action_form === action_update) {
      this.mergeDataSummary();
    }

    $("#pcc").on('change', function () {
      var period = $("#pcc option:selected").text() + ', ' + $("#month").val() + ' ' + $('#year').val()
      $('#period').text(period)
      $('.nav-tabs li:eq(1) a').tab('show'); // active recipe tab
      NProgress.start();
      stock_opname.parseRecipeData();
      stock_opname.removeRecipeDataInParamsMerging();
      NProgress.done();
    })

    // Set material id for recipe and summary
    // $(".select-recipe-material").on('focusin', function () {
    //   $(this).attr('old-data', this.value)
    // }).change(function() {
    //   var recipeDetailId = parseInt($(this).attr('recipe-detail-id'));
    //   var materialDetailId = parseInt(this.value);
    //   var prevMaterialDetailId = parseInt($(this).attr('old-data'));
    //
    //   setMaterialDetailId(recipeDetailId, prevMaterialDetailId, materialDetailId)
    //   $(this).blur();
    // });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
       if ($(this).html() === 'Summary') {
         stock_opname.showSummaryData();
         $(btn_confirm).removeClass('hidden');
         $(btn_save_daft).addClass('hidden');
         $(btn_save_daft).trigger('click');
       } else {
         $(btn_save_daft).removeClass('hidden');
         $(btn_confirm).addClass('hidden');
       }
    });

    tbl_material.DataTable({
      info: false,
      language: {
        search: "",
        searchPlaceholder: "Search ..."
      },
      paging: false,
      dom: '<"pull-left"f><"pull-right"l>tip',
      columnDefs: [
        { orderable: true, targets: [0,1] },
        { orderable: false, targets: '_all' },
      ]
    });

    dtTableSummary = tbl_summary.DataTable({
      info: false,
      paging: false,
      searching: false,
      columnDefs: [
        { orderable: true, targets: [0,1] },
        { orderable: false, targets: '_all' }
      ],
    });

  },
  
  showSummaryData: function() {
    params_summary = {};

    $.each(params_merging, function (index, row) {
      var qty_material = 0;
      var materialDetailId = row['material_detail_id'];

      if (row.hasOwnProperty('locations')) {
        $.each(row.locations, function (i, location) {
          qty_material += parseFloat(location.quantity_outlet_uom);
          qty_material += parseFloat(location.quantity_supplier_uom) * row['final_conversion_rate'];
        });
      }

      var qty_recipe = row.hasOwnProperty('recipe_qty') ? parseFloat(row.recipe_qty) : 0;
      var totalQty = (qty_recipe + qty_material);

      //if (totalQty > 0) {
        if (params_summary[materialDetailId] !== undefined) {
          params_summary[materialDetailId].quantity_recipe += qty_recipe;
          params_summary[materialDetailId].quantity_material += qty_material;
          params_summary[materialDetailId].ending_inv += totalQty;
        } else {
          params_summary[materialDetailId] = {
            material_id: row['material_id'],
            material_detail_id: row['material_detail_id'],
            material_detail_code: row['code'],
            material_detail_name: row['name'],
            report_uom_description: row['report_uom_description'],
            quantity_recipe: qty_recipe,
            quantity_material: qty_material,
            ending_inv: totalQty,
            conversation_net_weight: row['conversation_net_weight'],
            net_weight_name: row['net_weight_name'],
            potential_ending: row['potential_ending'],
            arr_total_available_final: row['arr_total_available_final'],
            total_usage_material: row['total_usage_material'],
          };
        }
      //}
    });

    var summaryDataByMaterialId = this.filterSummaryDataByMaterialId(params_summary);
    // Display data summary table
    dtTableSummary.clear();
    $.each(summaryDataByMaterialId, function (i, row) {
        $.each(row['material_detail_ids'], function (index, material_detail_id) {
          var totalEndingEnv = (index === 0) ? row['total_ending_inv'].toFixed(2) : '';
          //var potentialEndingInv = (index === 0) ? (row['total_available'] - row['total_usage_material']).toFixed(2) : '';
          var potentialEndingInv = (index === 0) ? row['potential_ending'] : '';
          var varianceStock = (index === 0 && potentialEndingInv != 0) ? (row['total_ending_inv'] - row['potential_ending']).toFixed(2) + ' ' + row['net_weight_name'] : '';
          //var bgColor = varianceStock !== '' && !(varianceStock >= -200 && varianceStock <= 200) ? 'btn-danger' : '';
          var bgColor =''; // TODO

          var objRow =  dtTableSummary.row.add([
            params_summary[material_detail_id]['material_detail_code'],
            params_summary[material_detail_id]['material_detail_name'],
            params_summary[material_detail_id]['report_uom_description'],
            params_summary[material_detail_id]['quantity_material'].toFixed(2),
            params_summary[material_detail_id]['quantity_recipe'].toFixed(2),
            params_summary[material_detail_id]['ending_inv'].toFixed(2),
            totalEndingEnv,
            potentialEndingInv,
            varianceStock
          ]);
          $(objRow.node()).find('td:eq(8)').addClass(bgColor);
        });
    });
    dtTableSummary.draw()
  },

  filterSummaryDataByMaterialId: function (dataSummary) {
    var dataGroupBy = {};
    pcc = parseInt($("#pcc").val());
    $.each(dataSummary, function (index, item) {
      var materialId =  item['material_id'];
      var materialDetailId =  item['material_detail_id'];
      var totalAvailable  = item['arr_total_available_final'] !== undefined && item['arr_total_available_final'].hasOwnProperty(pcc)
        ? parseFloat(item['arr_total_available_final'][pcc]) : 0;
      var totalEnding = item['ending_inv'] * item['conversation_net_weight'];
      
      if (dataGroupBy.hasOwnProperty(materialId)) {
        dataGroupBy[materialId]['total_ending_inv'] += totalEnding;
        dataGroupBy[materialId]['total_available'] += totalAvailable;
        dataGroupBy[materialId]['material_detail_ids'].push(materialDetailId);
      } else {
        dataGroupBy[materialId] = {
          'total_available' : totalAvailable,
          'total_ending_inv' : totalEnding,
          'net_weight_name' : item['net_weight_name'],
          'potential_ending' : item['potential_ending'],
          'total_usage_material' : data_total_usage[materialId + '_' + pcc] ? data_total_usage[materialId + '_' + pcc] : 0,
          'material_detail_ids' : [materialDetailId]
        };
      }
    });

    return dataGroupBy;
  },

  parseRecipeData: function() {
    pcc = parseInt($("#pcc").val());
    params_recipe = {};
    var strHtml = '';
    var code = '';
    $(tbl_recipe).find('tbody').html(null);
    $.each(data_recipe, function (index, row) {
      // Get what recipe belongs to pcc id
      if (row['master_pcc_ids'].indexOf(pcc) >= 0 || action_form === action_update) {
        params_recipe[row['recipe_detail_id']] = $.extend(true, {}, row);

        strHtml  += '<tr>';
        if (code !== row['recipe_code']) {
          strHtml  += '<td>' + row['recipe_code'] + '</td>';
          strHtml  += '<td>'+ row['recipe_name'] + '</td>';
          strHtml  +='<td>';
          strHtml  += '<input class="w100 quantity_recipe" ' + disable_edit_form + ' type="number" min="0" value="'+ row['recipe_qty'] + '" onchange="updateQtyRecipe(this, this.value,' + row['recipe_detail_id'] + ')"/>';
          strHtml  +='</td>';
          strHtml  += '<td>' + row['recipe_uom_name'] + '</td>';
          code = row['recipe_code'];
        } else {
          strHtml  += ''
            + '<td></td>'
            + '<td></td>'
            + '<td></td>'
            + '<td></td>';
        }

        strHtml  += '<td>'+ row['material_name'] + '</td>';
        
        strHtml  += '<td>';
        strHtml  += '<select onfocus="focusMaterial(this)" onchange="changeMaterial(this)" class="form-control w300 select-recipe-material" ' + disable_edit_form + ' old-data="0" recipe-detail-id="'+ row['recipe_detail_id'] + '">';
        strHtml  += '<option value="0">Select Material Detail</option>';

        var count = 0;
        $.each(row['material_details'], function (i, materialDetail) {
          // if the first material detail has transaction = 1, selected
          var selected = count == 0 && materialDetail['has_transaction'] == 1 ? 'selected' : '';
          strHtml  += '<option ' + selected + ' value="'+ materialDetail['id'] + '">' + materialDetail['code'] + ' - ' + materialDetail['name'] + '</option>';

          // set quantity for select material
          if (selected == 'selected') {
            var recipeDetailId = parseInt(row['recipe_detail_id']);
            var materialDetailId = parseInt(materialDetail['id']);
            var prevMaterialDetailId = 0;

            setMaterialDetailId(recipeDetailId, prevMaterialDetailId, materialDetailId)
          }

          count++;
        });
        strHtml  += '</select>';
        strHtml  += '</td>';

        strHtml  += '<td class="text-right" id="material-detail-' + row['recipe_detail_id']+ '">' + row['material_detail_qty'] + '</td>';
        strHtml  += '<td>' +  row['material_recipe_uom_name'] + '</td>';
        strHtml  += '<tr>';
      }
    });
    $(tbl_recipe).find('tbody').html(strHtml);

    // disable recipe input if this PCC is locked
    if (is_lock == 1) {
      $('input, select').prop('disabled', true);
    }
  },
  
  mergeDataSummary: function() {
    var params_merging_temp = [];
    for (var i in data_summary) {
      var materialDetailId = data_summary[i].material_detail_id;
      var recipeDetailId = data_summary[i].recipe_detail_id;
      if (parseInt(recipeDetailId) > 0) {
          var arrTotalAvailableFinal = [];
          var reportUOMDesc = '';
          var arrMaterialDetail = params_recipe[recipeDetailId]['material_details'];
          for (var j = 0; i < arrMaterialDetail.length; j++) {
            if (arrMaterialDetail[j].id === materialDetailId) {
              arrTotalAvailableFinal = arrMaterialDetail[j].arr_total_available_final;
              reportUOMDesc = arrMaterialDetail[j].report_uom_description;
              break;
            }
          }
          
          var index = materialDetailId + '_' + recipeDetailId;
          params_merging[index] = {
            total_usage_material : params_recipe[recipeDetailId]['total_usage_material'],
            material_id: params_recipe[recipeDetailId]['material_id'],
            material_detail_id: materialDetailId,
            code: data_summary[i].material_detail_code,
            name: data_summary[i].material_detail_name,
            potential_ending: params_recipe[recipeDetailId]['potential_ending'],
            recipe_qty: (params_merging_temp && params_merging_temp.indexOf(materialDetailId) === -1) ? data_summary[i].quantity_recipe : 0,
            conversation_net_weight: params_recipe[recipeDetailId]['conversation_net_weight'],
            net_weight_name: params_recipe[recipeDetailId]['net_weight_name'],
            is_recipe_data: true,
            arr_total_available_final: arrTotalAvailableFinal,
            report_uom_description:  reportUOMDesc
          };
          params_merging_temp[materialDetailId] = materialDetailId;
      }
    }
  },
  
  loadPCCByPeriod: function () {
    $('body #month').on('dp.change', function () {
      stock_opname.updateListPCC($('#month').val(), $('#year').val())
    });

    $('body #year').on('dp.change', function () {
      stock_opname.updateListPCC($('#month').val(), $('#year').val())
    });
  },
  
  updateListPCC: function (month, year) {
    var params = {
      month: month,
      year: year
    };

    $("#pcc").attr('disabled', true);
    NProgress.start();
    $.ajax({
      url: routeApi('inventory/master-pcc/get-by-period'),
      data: params,
      method: "GET",
      dataType: "json",
      success: function (response) {
        $('#pcc').find('option').remove();
        $.each(response.data, function (index, item) {
          $('#pcc').append("<option value='" + item.id + "'>" + item.name + "</option>")
        });
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
        $("#pcc").attr('disabled', false)
        NProgress.done();
      }
    })
  },

  saveStockOpname: function () {
    $('body .stock-opname .btn-save').off('click').on('click', function (e) {

      var $this = $(this);
      var month = $("#month").val();
      var year = $("#year").val();
      pcc = parseInt($("#pcc").val());
      var pcc_name = $('#pcc').find(":selected").text();
      var url = action_form === action_create ? route('inventory/stock-opname/save') : route('inventory/stock-opname/update');
      var isUserAction = (e.originalEvent !== undefined);

      if (month.length === 0 || year.length === 0) {
        swal({
          title: "",
          text: "Please input month or year!",
          type: 'error',
        });
        return;
      }
      stock_opname.showSummaryData();
      json_data = {};
      json_data = {
        'id': id,
        'month': month,
        'year': year,
        'pcc_id': pcc,
        'is_draft': 0,
        'is_sent_ftp': parseInt($this.attr('action-submit')) === action_save_prod ? 1 : 0,
        'stock_opname_details': params_material,
        'stock_opname_recipe': params_recipe,
        'stock_opname_summary': params_summary
      };

      var date = new Date(year, (month-1), 10);
      var month_name = date.toLocaleString('default', { month: 'long' });
      var pcc_period =  pcc_name + " " + month_name + " " + year;

      var mgsAction = action_form === action_create ? ( json_data.is_sent_ftp ? 'save' : 'save as draft' ) : 'update';
      var confirmMgs = json_data.is_sent_ftp ? "Are you sure to " + mgsAction + " and Send FTP " + pcc_period + " ?" : "Are you sure to " + mgsAction + " " + pcc_period + "?";

      swal({
        title: "",
        type: "warning",
        text: confirmMgs,
        showCancelButton: true,
        confirmButtonText: "Yes",
        closeOnConfirm: false,
        showLoaderOnConfirm: true
      }, function () {

        swal.close();
        NProgress.start();
        $.ajax({
          url: url,
          type: "POST",
          contentType: 'application/json',
          data: JSON.stringify(json_data),
          success: function (response) {
            toastr.success(response.message);
            if (isUserAction) {
              $(btn_back_home_page).click();
            }
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

    });
  },
  
  removeRecipeDataInParamsMerging: function () {
    // when reload recipe data, need to remove old recipe data ib params_merging
    $.each(params_merging, function (index, row) {
      if (row.hasOwnProperty('is_recipe_data') && row['is_recipe_data'] === true) {
        delete params_merging[index];
      }
    })
  },
}

function updateQtySupplierUOM(material_id, location_id, qty) {
  params_material[material_id].locations[location_id].quantity_supplier_uom = qty;
  params_merging[material_id].locations[location_id].quantity_supplier_uom = qty;
}

function updateQtyOutletUOM(material_id, location_id, qty) {
  params_material[material_id].locations[location_id].quantity_outlet_uom = qty;
  params_merging[material_id].locations[location_id].quantity_outlet_uom = qty;
}

function updateQtyRecipe(obj, qty, recipeDetailId) {
  NProgress.start();

  var recipeId = params_recipe[recipeDetailId].recipe_id;
  var materialDetailId = parseInt(params_recipe[recipeDetailId].material_detail_id);

  // Update qty for all recipe detail is having recipeId
  for (var i in params_recipe) {
    if (params_recipe[i].recipe_id === recipeId) {
      params_recipe[i].recipe_qty = qty;
    }
  }

  // Update qty for all material detail corresponding with recipe_detail_id
  this.calculateRecipeMaterialQty(recipeId, recipeDetailId, materialDetailId);
  // Update qty summary params
  $.each(params_recipe, function(recipeDetailIdTmp, row) {
    if (row['recipe_id'] === recipeId) {
      if (row['material_detail_id'] > 0) {
        updateSummaryParams(recipeDetailIdTmp, row['material_detail_id']);
      }
    }
  });

  NProgress.done();
}

function calculateRecipeMaterialQty(recipeId, recipeDetailId, material_detail_id) {
  if (recipeId > 0) {
    for (var i in params_recipe) {
      if (params_recipe[i].recipe_id === recipeId) {
        var recipe_qty = parseFloat(params_recipe[i].recipe_qty);
        var material_detail_id_tmp = parseInt(params_recipe[i].material_detail_id);
        var qty = 0;
        if (recipe_qty !== 0 && material_detail_id_tmp > 0) {
          qty = (recipe_qty / params_recipe[i].recipe_usage) * params_recipe[i].recipe_detail_usage;
          qty = qty.toFixed(2);
        }
        params_recipe[i].material_detail_qty = qty;
        // Update material qty of recipe table
        $(tbl_recipe).find("tbody>tr>td#material-detail-"+i).text(qty);
      }
    }
  } else {
    var recipe_qty = parseFloat(params_recipe[recipeDetailId].recipe_qty);
    var qty = 0;
    if (recipe_qty !== 0 && material_detail_id > 0) {
      qty = (recipe_qty / params_recipe[recipeDetailId].recipe_usage) * params_recipe[recipeDetailId].recipe_detail_usage;
      qty = qty.toFixed(2);
    }
    params_recipe[recipeDetailId].material_detail_qty = qty;
    // Update material qty of recipe table
    $(tbl_recipe).find("tbody>tr>td#material-detail-"+recipeDetailId).text(qty);
  }
}

function setMaterialDetailId (recipeDetailId, oldMaterialDetailId, materialDetailId) {
  params_recipe[recipeDetailId].material_detail_id = materialDetailId;
  this.calculateRecipeMaterialQty(0, recipeDetailId, materialDetailId);

  var oldIndex = oldMaterialDetailId + '_' + recipeDetailId;
  // Update summary params
  updateSummaryParams(recipeDetailId, materialDetailId);
  // Delete old recipe already added into summary
  if (params_merging[oldIndex] !== undefined) {
      delete params_merging[oldIndex];
  }
}

function updateSummaryParams(recipeDetailId, materialDetailId) {
  // update qty or append into summary if not exist
  $.each(params_recipe[recipeDetailId].material_details, function (index, row) {
    if (row['id'] === materialDetailId) {
      var materialQty = parseFloat(params_recipe[recipeDetailId].material_detail_qty);
      var qtyConvertOutlet = materialQty;
      // Convert from material qty -> recipe qty
      if (params_recipe[recipeDetailId].recipe_rate_uom_id !== row['recipe_uom_id']) {
          qtyConvertOutlet = materialQty / params_recipe[recipeDetailId].recipe_rate;
      }
      qtyConvertOutlet = (qtyConvertOutlet / row['final_conversion_rate']);

      var qtyConverted = parseFloat(qtyConvertOutlet.toFixed(2));
      var index = materialDetailId + '_' + recipeDetailId;

      if (params_merging[index] !== undefined) {
        params_merging[index].recipe_qty = qtyConverted;
      } else {
        params_merging[index] = {
          total_usage_material : params_recipe[recipeDetailId]['total_usage_material'],
          material_id: params_recipe[recipeDetailId]['material_id'],
          material_detail_id: materialDetailId,
          code: row['code'],
          name: row['name'],
          potential_ending: params_recipe[recipeDetailId]['potential_ending'],
          recipe_qty: qtyConverted,
          conversation_net_weight: params_recipe[recipeDetailId]['conversation_net_weight'],
          net_weight_name: params_recipe[recipeDetailId]['net_weight_name'],
          is_recipe_data: true,
          arr_total_available_final: row['arr_total_available_final'],
          report_uom_description: row['report_uom_description']
        };
      }
    }
  });
}

function focusMaterial(obj) {
  $(obj).attr('old-data', obj.value)
}

function changeMaterial(obj) {
  var recipeDetailId = parseInt($(obj).attr('recipe-detail-id'));
  var materialDetailId = parseInt(obj.value);
  var prevMaterialDetailId = parseInt($(obj).attr('old-data'));
  
  setMaterialDetailId(recipeDetailId, prevMaterialDetailId, materialDetailId)
  $(obj).blur();
}

stock_opname.init();

