var tbl_material_detail = $('#tbl-material-detail'),
    tbl_account = $('#tbl-account'),
    input_total = $('#grand-total'),
    receive = $('#receive'),
    invoice_number = $('#invoice_number'),
    return_date = $('#return_date'),
    supplier_code = $('#supplier_code'),
    params = {
        "material_details": {},
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
var return_order = {
    init: function () {
        this.importReturn();
        this.selectReceiveInvoice();
        this.countTotal();
    },
    importReturn: function () {
        $('body #file').off('change').on('change', function (e) {
            if (!receive.val()) {
                swal({
                    title: '',
                    type: 'error',
                    text: 'Please select receive invoice number',
                    html: true
                })
            } else {
                NProgress.start();
                var url = route('inventory/return/read-csv');
                var fileInput = document.getElementById('file');
                var file = fileInput.files[0];
                var formData = new FormData();
                formData.append('file', file);
                formData.append('receive_id', receive.val());
                $.ajax({
                    type: "POST",
                    processData: false, // important
                    contentType: false, // important
                    url: url,
                    data: formData,
                    success: function (response) {
                        return_order.appendMaterial(response.data.material_details);
                        return_order.appendAccount(response.data.accounts);
                        invoice_number.val(response.data.invoice_number);
                        supplier_code.val(response.data.supplier_code);
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
            }
            $('#file').val('');
        });
    },

    selectReceiveInvoice: function () {
        $('body #receive').off('change').on('change', function () {
            var receive_id = receive.val();
            if (receive_id) {
                NProgress.start();
                $.ajax({
                    type: "GET",
                    url: routeApi('inventory/receive-order/get-material-detail'),
                    data: {
                        receive_id: receive_id,
                    },
                    success: function (response) {
                        return_order.appendMaterial(response.data.material_details);
                        return_order.appendAccount(response.data.accounts);
                        supplier_code.val(response.data.supplier_code);
                    },
                    error: function (error) {
                        toastr.error(error.responseJSON.message);
                    },
                    complete: function () {
                        NProgress.done();
                    }
                });
            }

        });
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
            });
            return_order.appendAccount(params.accounts);
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
                var uom_html = '<select class="form-control uom_id" conversion_rate="'+item["smaller_uom_conversion_rate"]+'" from_uom_id="'+item["supplier_uom_id"]+'" to_uom_id="'+item["smaller_uom_id"]+'" onchange="changeUom(' + item["material_detail_id"] + ', this)">';
                $.each(item['uoms'], function (index, element) {
                    var selected = item["uom_id"] == index ? "selected" : "";
                    uom_html += '<option value="' + index + '" ' + selected + '>' + element + '</option>';
                });
                uom_html += '</select>'
            } else {
                var uom_html = item['uom_name'];
            }
            var qty_html   = '<input type="number" material-detail-id="'+ item['material_detail_id']+'" class="form-control text-right quantity input-number" value="' + item['quantity'] + '" >';
            var price_html = '<input type="number" material-detail-id="'+ item['material_detail_id']+'" class="form-control text-right price input-number" value="' + item['price'] + '" >';
            datatable.row.add( [
                item['material_detail_code'],
                item['material_detail_name'],
                item["smaller_uom_detail_name"],
                uom_html,
                qty_html,
                price_html,
                addCommas(item['total'])
            ] );
            params.material_details[item['order']] = {
                material_id: item['material_id'],
                material_detail_id: item['material_detail_id'],
                uom_id: item['uom_id'],
                account_id: item['account_id'],
                quantity: qty,
                price: price,
                total: total
            }
            params.total += item['total'];
        });
        datatable.draw();

    },

    appendAccount: function (data) {
        var htmlAccount = '';
        var grand_total = 0;
        var summaryAccount = [];
        params.accounts = {};
        $.each(data, function (index, item) {
            var total = item.total == null ? 0 : item['total'];
            params.accounts[item['account_id']] = {
                account_id: item['account_id'],
                account_code: item['account_code'],
                account_name: item['account_name'],
                total: total
            }
            if (summaryAccount.indexOf(item['account_code']) <= -1) {
                htmlAccount += ''
                    + '<tr>'
                    + ' <td>' + item['account_code'] + '</td>'
                    + ' <td>' + item['account_name'] + '</td>'
                    + ' <td width="100">' + toCurrency(total) + '</td>'
                    + '</tr>';
                grand_total += parseFloat(total);
                summaryAccount.push(item['account_code'])
            }
        });

        tbl_account.find('tbody').html(null).append(htmlAccount).slideDown("slow");
        input_total.val(toCurrency(grand_total));
    },

}

return_order.init();

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
    return_order.appendAccount(params.accounts);
}

function submitForm(obj) {
    params.invoice_number = invoice_number.val();
    params.supplier_code = supplier_code.val();
    params.receive_id = receive.val();
    params.return_date = return_date.val();
    NProgress.start();
    $(obj).attr('disabled', true);
    $.ajax({
        url: route('inventory/return/save'),
        method: "POST",
        data: params,
        success: function (response) {
            toastr.success(response.message);
            window.location.href = route('inventory/return');
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
