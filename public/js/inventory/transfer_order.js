var tbl_material_detail = $('#tbl-material-detail'),
    tbl_account = $('#tbl-account'),
    input_total = $('#grand-total'),
    to_outlet_id = $('#to_outlet_id'),
    from_outlet_id = $('#from_outlet_id'),
    invoice_number = $('#invoice_number'),
    transfer_date  = $('#transfer_date'),
    transfer_in = $('#transfer-in'),
    transfer_out = $('#transfer-out'),
    store_code = $('#store_code'),
    type = $('#type'),
    params = {
        "material_details": {},
        "accounts": {},
        "total": 0
    }
    datatable = $(tbl_material_detail).DataTable({
        paging: false,
        searching: false,
        columnDefs: [
            {orderable: true, targets: [0, 1]},
            {orderable: false, targets: '_all'}
        ],
        bDestroy: true
    });
var transfer_order = {
    init: function () {
        transfer_in.hide();
        transfer_out.hide();
        this.changeType();
        this.importTransferIn();
        this.countTotal();
    },

    changeType: function () {
        type.off('change').on('change', function () {
            if ($(this).val() === $('#transfer_type_in').val()) {
                transfer_in.show();
                transfer_out.hide();
                from_outlet_id.attr('disabled', false);
                to_outlet_id.attr('disabled', true);
                input_total.val(0);
                tbl_material_detail.find('tbody').html(null).append('').slideDown("slow");
                tbl_account.find('tbody').html(null).append('').slideDown("slow");
                changeToTransferIn();
            } else if ($(this).val() === $('#transfer_type_out').val()) {
                transfer_in.hide();
                transfer_out.show();
                from_outlet_id.attr('disabled', true);
                to_outlet_id.attr('disabled', false);
                changeToTransferOut();
            } else {
                // Set default
                from_outlet_id.val('').trigger('change');
                to_outlet_id.val('').trigger('change');
                from_outlet_id.attr('disabled', false);
                to_outlet_id.attr('disabled', false);
            }
        });
    },

    importTransferIn: function () {
        $('body #file').off('change').on('change', function () {
            NProgress.start();
            var url = route('inventory/transfer-order/read-csv');
            var fileInput = document.getElementById('file');
            var file = fileInput.files[0];
            var formData = new FormData();
            formData.append('file', file);
            $.ajax({
                type: "POST",
                processData: false, // important
                contentType: false, // important
                url: url,
                data: formData,
                success: function (response) {
                    transfer_order.appendMaterial(response.data.material_details, '');
                    $('#from_outlet_id option').each(function () {
                        invoice_number.val(response.data.invoice_number);
                        if (response.data.outlet_from == $(this).attr('data-code')) {
                            from_outlet_id.val($(this).val()).trigger('change');
                            return;
                        }
                    });
                    toastr.success(response.message);
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

    appendMaterial: function (data, disable) {
            html_summary = '',
            grand_total = 0,
            arr_account = {};

        params.material_details = {};
        datatable.clear();
        $.each(data, function (index, item) {
            var price = parseFloat(item['price']),
                total = price * parseFloat(item['quantity']);
            item['total'] = total;
            params.material_details[item['id']] = item;
            params.total += total;
            if (item['uoms']) {
                var uom_html = '<select class="form-control uom_id" onchange="changeUom(' + item["id"] + ', this)">';
                $.each(item['uoms'], function (index, element) {
                    var selected = item['report_uom_id'] == index ? "selected" : "";
                    uom_html += '<option value="' + index + '" ' + selected + '>' + element + '</option>';
                });
                uom_html += '</select>'
            } else {
                var uom_html = item['uom_name'];
            }
            var qty_html = '<input class="form-control detail_qty quantity input-number" material-detail-id="'+item['id']+'" min="1" ' + disable + ' type="number" value="'
                + item['quantity'] + '" id="' + item['id'] + '">';
            datatable.row.add( [
                item['code'],
                item['name'],
                item["smaller_uom_detail_name"],
                uom_html,
                qty_html,
                toCurrency(price),
                toCurrency(total)
            ] );

            if (arr_account[item['account_id']] !== undefined) {
                arr_account[item['account_id']]['total'] += total;
            } else {
                arr_account[item['account_id']] = {
                    'account_id': item['account_id'],
                    'account_code': item['account_code'],
                    'account_name': item['account_name'],
                    'total': item['total']
                };
            }
            grand_total += total;
        });
        datatable.draw();
        params.accounts = arr_account;
        $.each(arr_account, function (index, item) {
            html_summary += ''
                + '<tr>'
                + ' <td>' + item['account_code'] + '</td>'
                + ' <td>' + item['account_name'] + '</td>'
                + ' <td class="text-right total" width="100">' + toCurrency(item['total']) + '</td>'
                + '</tr>';
        });

        input_total.val(toCurrency(grand_total));
        tbl_account.find('tbody').html(null).append(html_summary).slideDown("slow");
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
            var price = revertCurrency(point.find('td:eq(5)').text());
            var total = parseFloat(qty) * parseFloat(price);
            point.find('td:eq(6)').text(toCurrency(total));
            var material_detail_id = $(this).attr('material-detail-id');
            $.each(params.material_details, function (index, item) {
                if (parseInt(index) == parseInt(material_detail_id)) {
                    params.total = params.total - item.total + total;
                    params.accounts[item.account_id].total = params.accounts[item.account_id].total - item.total + total;
                    item.quantity = qty;
                    item.price = price;
                    item.total = total;
                }
            })
            transfer_order.appendAccount(params.accounts);
        });
    }

}

transfer_order.init();

function changeUom(id, obj) {
    params.material_details[id].uom_id = $(obj).val();
}

function submitForm(obj) {
    params.type = type.val();
    params.invoice_number = invoice_number.val();
    params.transfer_date = transfer_date.val();
    params.from_outlet_id = from_outlet_id.val();
    params.to_outlet_id = to_outlet_id.val();
    params.from_outlet_code = from_outlet_id.find('option:selected').attr('data-code');
    params.to_outlet_code = to_outlet_id.find('option:selected').attr('data-code');
    NProgress.start();
    $(obj).attr('disabled', true);
    $.ajax({
        url: route('inventory/transfer-order/save'),
        method: "POST",
        dataType: "json",
        contentType: 'application/json',
        data: JSON.stringify(params),
        success: function (response) {
            toastr.success(response.message);
            window.location.href = route('inventory/transfer-order');
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

function changeToTransferOut() {
    NProgress.start();
    $(".outlet_to").html("Outlet To");
    to_outlet_id.val('').trigger('change');
    $('#from_outlet_id option').each(function () {
        if (store_code.val() == $(this).attr('data-code')) {
            from_outlet_id.val($(this).val()).trigger('change');
            return;
        }
    });
    $.ajax({
        type: "GET",
        url: routeApi('inventory/transfer-order/get-material-detail'),
        success: function (response) {
            transfer_order.appendMaterial(response.data, '');
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

function changeToTransferIn() {
    from_outlet_id.val('').trigger('change');
    $(".outlet_to").html("My Outlet Code");
    $('#to_outlet_id option').each(function () {
        if (store_code.val() == $(this).attr('data-code')) {
            to_outlet_id.val($(this).val()).trigger('change');
            return;
        }
    });
}

