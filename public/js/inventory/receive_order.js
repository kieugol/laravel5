var status_approve = $('#status_approve').val()
    action_create = $('#action_create').val(),
    tbl_material_detail = $('#tbl-material-detail'),
    tbl_account = $('#tbl-account'),
    input_total = $('#grand-total'),
    invoice_number = $('#invoice_number'),
    receive_date = $('#receive_date'),
    purchase = $('#purchase'),
    supplier = $("#supplier"),
    is_returnable = $('#is_returnable'),
    purchase_id_initial = $('#purchase_id').val(),
    action_form = $('#action_form').val(),
    is_import_do = false;
    params = {
        "material_details": {},
        "accounts": {},
        "total": 0
    };
    datatable = $(tbl_material_detail).DataTable({
    paging: false,
    searching: false,
    columnDefs: [
        {orderable: true, targets: [0, 1]},
        {orderable: false, targets: '_all'}
    ],
    bDestroy: true
    });
var receive_order = {
    init: function () {
        this.initParams();
        this.importReceive();
        this.selectPurchaseOrder();
        this.countTotal();
        $('#is_returnable').bootstrapSwitch();
        if (parseInt(purchase_id_initial) > 0 && action_form == action_create) {
            purchase.val(purchase_id_initial).trigger('change');
        }
    },

    initParams: function () {
        var url_current = window.location.href;
        var last_param = url_current.substring(url_current.lastIndexOf('/') + 1);
        if (last_param != 'create' && $.isEmptyObject(params.material_details)) {
            $("#tbl-material-detail tbody tr").each(function () {
                var material_detail_id = $(this).find('input:eq(1)').attr('material-detail-id');
                var qty = $(this).find('.quantity').val();
                var price = $(this).find('.price').val();
                var total = parseFloat(qty) * parseFloat(price);
                params.material_details[material_detail_id] = {
                    material_id: $(this).find('#material_id').val(),
                    material_detail_id: material_detail_id,
                    account_id: $(this).find('#account_id').val(),
                    quantity: qty,
                    price: price,
                    total: total
                };
            });
            $("#tbl-account tbody tr").each(function () {
                var account_id = $(this).find('#account_id').val();
                params.accounts[account_id] = {
                    account_id: account_id,
                    account_code: $(this).find('.account_code').text(),
                    account_name: $(this).find('.account_name').text(),
                    total: $(this).find('.total').text()
                }
            });
        }
    },
    importReceive: function () {
        $('body #file').off('change').on('change', function () {
            NProgress.start();
            var url = route('inventory/receive-order/read-csv');
            var fileInput = document.getElementById('file');
            var file = fileInput.files[0];
            var formData = new FormData();
            formData.append('file', file);
            formData.append('supplier_code', supplier.attr('supplier-code'));
            formData.append('purchase_id', purchase_id_initial);
            $.ajax({
                type: "POST",
                processData: false, // important
                contentType: false, // important
                url: url,
                data: formData,
                success: function (response) {
                    receive_order.appendMaterial(response.data.material_details);
                    receive_order.appendAccount(response.data.accounts);
                    toastr.success(response.message);

                    // show notice if purchase order and DO are different
                    if (response.show_notice) {
                        swal({
                            title: '',
                            type: 'warning',
                            text: response.notice_message,
                            html: true
                        })
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
            $('#file').val('');
        });
    },
    selectPurchaseOrder: function () {
        $('body #purchase').off('change').on('change', function () {
            var purchase_id = purchase.val();
            if (purchase_id) {
                NProgress.start();
                $.ajax({
                    type: "GET",
                    url: routeApi('inventory/purchase/get-by-id'),
                    data: {
                        purchase_id: purchase_id,
                    },
                    success: function (response) {
                        is_import_do = response.data.is_import_do;
                        if (is_import_do) {
                            $('.import-do').show();
                            $('.btn_save_do').show();
                            $('.btn_save_approve').hide();
                        } else {
                            $('.import-do').hide();
                            $('.btn_save_do').hide();
                            $('.btn_save_approve').show();
                        }
                        supplier.val(response.data.name);
                        supplier.attr('data-id', response.data.id);
                        supplier.attr('supplier-code', response.data.code);
                        $.ajax({
                            url: routeApi('inventory/purchase/get-detail-by-purchase'),
                            data: {
                                purchase_id: purchase_id,
                            },
                            method: "GET",
                            dataType: "json",
                            success: function (response) {
                                receive_order.appendMaterial(response.data.material_details)
                                receive_order.appendAccount(response.data.accounts)
                                if (is_import_do) {
                                    $('.uom_id').prop('disabled', true);
                                } else {
                                    $('.uom_id').prop('disabled', false);
                                }
                                toastr.success('Added Material detail successfully.');
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
                        })
                    }
                });
            }
        });
    },

    appendMaterial: function (data) {
        params.material_details = {};
        params.total = 0;
        datatable.clear();
        $.each(data, function (index, item) {
            var qty = item['quantity'] == null ? 0 : item['quantity'];
            var price = item['price'] == null ? 0 : item['price'];
            var total = item['total'] == null ? 0 : item['total'];
            if (item['uoms']) {
                var uom_html = '<select class="form-control uom_id" conversion_rate="'
                    +item["smaller_uom_conversion_rate"]+'" from_uom_id="'+item["supplier_uom_id"]
                    +'" to_uom_id="'+item["smaller_uom_id"]+'" onchange="changeUom(' + item["id"] + ', this)">';
                $.each(item['uoms'], function (index, element) {
                    var selected = item["uom_id"] == index ? "selected" : "";
                    uom_html += '<option value="' + index + '" ' + selected + '>' + element + '</option>';
                });
                uom_html += '</select>'
            } else {
                var uom_html = item['uom_name'];
            }
            var qty_html   = '<input type="number" min="1" material-detail-id="'+ item['id']+'" class="form-control text-right quantity input-number" value="' + qty + '" >';
            var price_html = '<input type="number" material-detail-id="'+ item['id']+'" class="form-control text-right price input-number" value="' + price + '" >';
            datatable.row.add( [
                item['code'],
                item['name'],
                item["smaller_uom_detail_name"],
                uom_html,
                qty_html,
                price_html,
                total.toFixed(2)
            ] );

            params.material_details[item['order']] = {
                material_id: item['material_id'],
                material_detail_id: item['id'],
                account_id: item['account_id'],
                uom_id: item['uom_id'],
                quantity: qty,
                price: price,
                total: total
            };
            params.total += (item['total'] == null ? 0 : item['total']);

        });
        datatable.draw();
    },

    appendAccount: function (data) {
        var html_account = '';
        var grand_total = 0;
        var summary_account = [];
        params.accounts = {};
        $.each(data, function (index, item) {
            var total = item.total == null ? 0 : item['total'];
            params.accounts[item['account_id']] = {
                account_id: item['account_id'],
                account_code: item['account_code'],
                account_name: item['account_name'],
                total: total
            }
            if (summary_account.indexOf(item['account_code']) <= -1) {
                html_account += ''
                    + '<tr>'
                    + ' <td>' + item['account_code'] + '</td>'
                    + ' <td>' + item['account_name'] + '</td>'
                    + ' <td width="100">' + toCurrency(total) + '</td>'
                    + '</tr>';
                grand_total += parseFloat(total);
                summary_account.push(item['account_code'])
            }
        });

        tbl_account.find('tbody').html(null).append(html_account).slideDown("slow");
        input_total.val(toCurrency(grand_total));
    },

    countTotal: function () {
        $(document).on('change', '.quantity, .price', function () {
            var point = $(this).parent().parent();
            var qty = point.find('.quantity').val();
            var price = point.find('.price').val();
            var total = parseFloat(qty) * parseFloat(price);
            point.find('td:eq(6)').text(toCurrency(total));
            var material_detail_id = $(this).attr('material-detail-id');
            $.each(params.material_details, function (index, item) {
                if (parseInt(item.material_detail_id) == parseInt(material_detail_id)) {
                    params.total = params.total - item.total + total;
                    params.accounts[item.account_id].total = params.accounts[item.account_id].total - item.total + total;
                    item.quantity = qty;
                    item.price = price;
                    item.total = total;
                }
            })
            receive_order.appendAccount(params.accounts);
        });
    }

}

receive_order.init();

function changeUom(materialDetailId, obj) {
    params.material_details[materialDetailId].uom_id = $(obj).val();
    var uom = $(obj).val();
    var point = $(obj).parents('tr');
    var conversion_rate = $(obj).attr('conversion_rate');
    var to_uom_id = $(obj).attr('to_uom_id');
    var price = point.find('.price').val();
    var quantity = point.find('.quantity').val();
    if (parseInt(uom) == parseInt(to_uom_id)) {
        var price_after_calculate = parseFloat(price/conversion_rate).toFixed(2);
    } else {
        var price_after_calculate = parseFloat(price*conversion_rate).toFixed(2);
    }
    var total = quantity * price_after_calculate;
    point.find('.price').val(price_after_calculate);
    point.find('td:eq(6)').text(total);
    $.each(params.material_details, function (index, item) {
        if (parseInt(item.material_detail_id) === parseInt(materialDetailId)) {
            params.total = params.total - item.total + total;
            params.accounts[item.account_id].total = params.accounts[item.account_id].total - item.total + total;
            item.quantity = quantity;
            item.price = price_after_calculate;
            item.total = total;
        }
    })
    receive_order.appendAccount(params.accounts);
}

function createForm(obj) {
    params.purchase_id = purchase.val();
    params.invoice_number = invoice_number.val();
    params.receive_date = receive_date.val();
    params.supplier_id = supplier.attr('data-id');
    // params.is_returnable = is_returnable.is(":checked") ? 1 : 0;
    var exit = checkValidatePrice();
    if (!exit) {
        swal({
            title: 'Error',
            type: 'error',
            text: 'Price can not is 0',
            html: true
        })
        return;
    }

    NProgress.start();
    $(obj).attr('disabled', true);
    $.ajax({
        url: route('inventory/receive-order/save'),
        method: "POST",
        data: params,
        success: function (response) {
            toastr.success(response.message);
            goPurchaseList();
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
            $(obj).attr('disabled', false);
        }
    });
}

function checkValidatePrice() {
    var exit = true;
    $.each(params.material_details, function (index, value) {
        if (value.quantity > 0 && value.price == 0) {
            exit = false;
            return;
        }
    });
    return exit;
}

function confirmAction(id, status_id) {
    // check the difference with the latest order
    $('#approve-receive-btn').prop('disabled', 'disabled');
    NProgress.start();
    $.ajax({
        url: route('inventory/receive-order/compare-latest'),
        method: "POST",
        data: {
            id: id,
        },
        success: function (response) {
            var title = 'Are you sure to approve ?';
            swal({
                title: title,
                type: 'warning',
                text: response.message,
                html: true,
                showCancelButton: true,
                confirmButtonText: "Yes",
                closeOnConfirm: false,
                cancelButtonText: "No",
                customClass: 'approve-receive-popup'
            }, function (result) {
                if (result) {
                    // yes button clicked
                    approveReceive(id, status_id);
                } else {
                    // no button clicked
                    $('#approve-receive-btn').prop('disabled', '');
                }
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
            NProgress.done();
        }
    });
}

// approve a receive order
function approveReceive(id, status_id) {
    NProgress.start();
    $.ajax({
        url: route('inventory/receive-order/confirm'),
        method: "POST",
        data: {
            status_id: status_id,
            id: id,
            invoice_number: invoice_number.val()
        },
        success: function (response) {
            toastr.success(response.message);
            swal.close();
            goPurchaseList();
        },
        error: function (error) {
            swal({
                title: '',
                type: 'error',
                text: error.responseJSON.message,
                html: true
            });

            $('#approve-receive-btn').prop('disabled', '');
        },
        complete: function () {
            NProgress.done();
        }
    });
}

function goPurchaseList() {
    window.location.href = route('inventory/purchase-order');
}

