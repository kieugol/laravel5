var tblPurchase = $('#tbl-detail');
var tblSummary = $('#tbl-summary');
var deliveryDate = $('#delivery-date');
var supplier = $('#supplier');
var btnHome = $('#btn-home');
var btnReset = $('.btn-facebook');
var btnApprove = $('.btn-approve');
var btnSave = $('.btn-save');
var btnEdit = $('.btn-edit');
var POData = $('#po-detail');
var actionForm = parseInt($('#action_form').val());
var actionEdit = 3;
var materialDetailData = {};

var params = {
    "delivery_date": $(deliveryDate).val(),
    "supplier_id": $(supplier).val(),
    "purchase_order_detail": {},
    "is_edit_po_detail": 0
};

var datatable = $(tblPurchase).DataTable({
    paging: false,
    searching: false,
    columnDefs: [
        {orderable: true, targets: [0, 1]},
        {orderable: false, targets: '_all'}
    ],
    bDestroy: true
});

function getListMaterialDetail(obj) {
    var supplierId = $(obj).val();

    if (supplierId != 0) {
        $(obj).attr('disabled', true);
        NProgress.start();
        $.ajax({
            url: routeApi('inventory/material-detail/get-by-supplier/') + $(obj).val(),
            method: "GET",
            dataType: "json",
            success: function (response) {
                materialDetailData = {};

                $.each(response.data, function (index, item) {
                    materialDetailData[index] = item;
                });

                appendMaterial(materialDetailData, '');
                toastr.success('Added Material detail successfully.');
            },
            complete: function () {
                NProgress.done();
                $(obj).attr('disabled', false);
            }
        })
    }
}

function appendMaterial(data, disable) {
    var htmlSummary = '';
    var summaryAccount = [];
    params.purchase_order_detail = {};
    datatable.clear();

    $.each(data, function (index, item) {
        var price = item['price'];
        if (item['price'] == null) {
            price = 0;
        }

        price = toCurrency(price);

        var uom_html = '<select class="form-control uom_id" account-code="'+item['account_code']+'" conversion_rate="'
            +item["smaller_uom_conversion_rate"]+'" from_uom_id="'
            +item["supplier_uom_id"]+'" to_uom_id="'+item["smaller_uom_id"]
            +'" onchange="changeUom(' + item["id"] + ', this)" ' + disable + '>';
            $.each(item['uoms'], function (index, element) {
                var str = element.split('_-_');
                var uom_id      = str[0];
                var uom_name    = str[1];
                var selected    = item["uom_id"] == uom_id ? "selected" : "";
                uom_html += '<option value="' + uom_id + '" ' + selected + '>' + uom_name + '</option>';
            });
        uom_html += '</select>';
        var qty_html = '<input class="form-control detail_qty input-number" account-code="'+item['account_code']+'" min="0" ' + disable
            + ' onchange="updateQty(' + item['id'] + ', this.value)" type="number" value="'
            + item['quantity'] + '" id="' + item['id'] + '">';
        var node = datatable.row.add( [
            item['code'],
            item['name'],
            item["smaller_uom_detail_name"],
            uom_html,
            qty_html,
            price,
            0
        ] ).node();
        if (item['quantity'] > 0 && actionForm === actionEdit) {
            $(node).addClass('bg-info');
        } else if (actionForm === actionEdit) {
            $(node).addClass('hidden');
        }
        params.purchase_order_detail[item['id']] = {
            po_id: item['po_id'],
            material_detail_id: item['id'],
            material_id: item['material_id'],
            account_id: item['account_id'],
            uom_id: item['uom_id'] ? item['uom_id'] : Object.keys(item['uoms'])[0],
            quantity: item['quantity'],
            price: item['price']
        };

        if (summaryAccount.indexOf(item['account_code']) <= -1) {
            htmlSummary += ''
                + '<tr>'
                + ' <td class="account_code">' + item['account_code'] + '</td>'
                + ' <td>' + item['account_name'] + '</td>'
                + ' <td class="text-right account_price" width="100">0</td>'
                + '</tr>';
            summaryAccount.push(item['account_code']);
        }
    });
    datatable.draw();

    this.tblSummary.find('tbody').html(null).append(htmlSummary).slideDown("slow");
}

function changeUom(materialDetailId, obj) {
    params.purchase_order_detail[materialDetailId].uom_id = $(obj).val();
    var uom = $(obj).val();
    var point = $(obj).parents('tr');
    var conversion_rate = $(obj).attr('conversion_rate');
    var to_uom_id = $(obj).attr('to_uom_id');
    var price = point.find('td:eq(5)').text();
    if (parseInt(uom) == parseInt(to_uom_id)) {
        var price_after_calculate = parseFloat(parseFloat(revertCurrency(price))/conversion_rate);
    } else {
        var price_after_calculate = parseFloat(parseFloat(revertCurrency(price))*conversion_rate);
    }
    point.find('td:eq(5)').text(toCurrency(price_after_calculate));
    params.purchase_order_detail[materialDetailId].price = price_after_calculate;
}

function updateQty(id, qty) {
    params.purchase_order_detail[id].quantity = qty;
}

function confirmAction(obj) {
    params.delivery_date = $(deliveryDate).val();
    params.supplier_id = $(supplier).val();
    if ($.isEmptyObject(params.purchase_order_detail)) {
        swal({
            title: '',
            type: 'error',
            text: 'Please add Material Detail.',
            html: true
        })
        return false;
    }

    swal({
        title: "Are you sure to create this item ?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        closeOnConfirm: false,
        cancelButtonText: "No"
    }, function () {
        submitForm(obj, route('inventory/purchase-order'));
    });
}

function approveAction(obj, id, is_edit_po_detail) {
    params.delivery_date = $(deliveryDate).val();
    params.supplier_id = $(supplier).val();
    params._method = 'PUT';
    params.is_edit_po_detail = is_edit_po_detail;

    swal({
        title: is_edit_po_detail ? "Are you sure to edit purchase order ?" : "Are you sure to approve ?" ,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        closeOnConfirm: false,
        cancelButtonText: "No"
    }, function () {
        submitForm(obj, route('inventory/purchase-order/' + id));
    });
}

function approvePO(obj, id, status) {
    params = {
        status: status
    };
    swal({
        title: "Are you sure to Approve?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        closeOnConfirm: false,
        cancelButtonText: "No"
    }, function () {
        submitForm(obj, route('inventory/purchase-order/update-status/' + id))
    });
}

function initialEditData(data) {
    appendMaterial(data.purchase_detail, (data.status_id === 1 ? '' : 'disabled'));
}

function submitForm(obj, url) {
    swal.close();
    NProgress.start();
    $(obj).attr('disabled', true);
    $.ajax({
        url: url,
        method: "POST",
        dataType: "json",
        contentType: 'application/json',
        data: JSON.stringify(params),
        success: function (response) {
            $(btnHome).click()
            $(btnReset).click()
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
        },
        complete: function () {
            NProgress.done();
            $(obj).attr('disabled', false)
        }
    })
}

function showAllPoDetail() {
    btnApprove.hide();
    btnSave.removeAttr('hidden');
    btnEdit.addClass('hidden');
    datatable.rows('.hidden').nodes().to$().removeClass( 'hidden' );
}

if (actionForm === actionEdit) {
    initialEditData(JSON.parse(POData.val()));
}

function countTotal(obj) {
    var grand_total = 0;
    var point = $(obj).parent().parent();
    var account_code = $(obj).attr('account-code');
    var qty = point.find('.detail_qty').val();
    qty = qty !== '' ? qty : 0;
    var price = point.find('td:eq(5)').text();
    price = revertCurrency(price);

    var total = parseFloat(qty) * parseFloat(price);
    $('#tbl-summary tbody > tr').each(function () {
        if ($(this).find('.account_code').text() == account_code) {
            var account_sub_total = parseFloat(revertCurrency($(this).find('.account_price').text()))
                - parseFloat(revertCurrency(point.find('td:eq(6)').text())) + parseFloat(total);
            $(this).find('.account_price').text(toCurrency(account_sub_total));
            return;
        }
    })
    point.find('td:eq(6)').text(toCurrency(total));
    $('#tbl-detail tbody > tr').each(function () {
        grand_total = parseFloat(grand_total) + parseFloat(revertCurrency($(this).find('td:eq(6)').text()));
    })

    $('.grand_total').val(toCurrency(grand_total));
}

$(document).on('change', '.detail_qty, .uom_id', function () {
    countTotal(this);
});

