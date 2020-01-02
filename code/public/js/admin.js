/* global NProgress, toastr */
$(document).ready(function () {
    changeStyleActionRows();
    updateTitleBrowser();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ajaxComplete(function () {
        updateTitleBrowser();
    });
});

function updateTitleBrowser() {
    var title = $("h1").text();
    if (title != "") {
        $("title").html(title);
    } else {
        $("title").html("Admin");
    }
}

function printpage() {
    window.focus();
    window.print();
    window.close();
}

function route(url) {
    var baseurl = $("#base_url").val();

    return baseurl + "/" + url;
}

function routeApi(url) {
  return $("#base_api_uri").val() + "/" + url;
}

function changeStyleActionRows() {
    $("table tr td:last-child a").each(function (index, object) {
        if ($(this).find("i").hasClass("fa-edit")) {
            $(this).addClass("btn btn-primary btn-xs");
        }
        if ($(this).find("i").hasClass("fa-trash")) {
            $(this).addClass("btn btn-danger btn-xs");
        }
    });
}

//$(document).ready(function () {
function check_eod(obj) {
    NProgress.start();
    $(obj).button('loading');
    var isValid = false;
    $.ajax({
        url: route("report/check_eod"),
        dataType: "json",
        data: {
            from_time: $("#date-start").val(),
            to_time: $("#date-end").val()
        },
        success: function (data) {
            if (data.result === true) {
                isValid = true;
                $('#pjax-container').html(data.html);
            } else {
                var html = '';
                if (Array.isArray(data.error)) {
                    html = '<div style="text-align: center;">';
                    html += '<div style="display: inline-block; text-align: left;">';
                    for (var i = 0; i < data.error.length; i++) {
                        html += (i == 0) ?  '<strong>' + data.error[i] + '</strong><br/>' : data.error[i];
                        html += '<br/>';
                    }
                    html += '</div>';
                    html += '</div>';
                } else {
                    html += data.error;
                }
                swal({
                    title: 'Error!',
                    type: 'error',
                    text: html,
                    html:true
                })
            }
        },
        complete: function () {
            $(".modal").modal("hide");
            $(".modal-backdrop").remove();
            $("body").removeClass("modal-open");
            NProgress.done();
            $(obj).button('reset');
            if (isValid) {
                $('#pjax-container').find("#eod-directly").trigger("click");
            }
        }
    });
}

function sync_menu(obj) {
    NProgress.start();
    $(obj).button('loading');
    $.ajax({
        url: route("sync/menu"),
        dataType: "json",
        success: function (data) {
            console.log(data);
            if (data.result) {
                toastr.success(data.msg);
            } else {
                toastr.error(data.msg);
            }
        },
        complete: function () {
            NProgress.done();
            $(obj).button('reset');
        }
    });
}

function sync_promotion(obj) {
    NProgress.start();
    $(obj).button('loading');
    $.ajax({
        url: route("sync/promotion"),
        dataType: "json",
        success: function (data) {
            console.log(data);
            if (data.result) {
                toastr.success(data.msg);
            } else {
                toastr.error(data.msg);
            }
        },
        complete: function () {
            NProgress.done();
            $(obj).button('reset');
        }
    });
}

function print_element(ele) {
    $space = $("#print-space");
    if ($space.size() == 0) {
        $("body").append("<div id='print-space'></div>");
        $space = $("#print-space");
    }
    $space.html("");
    $(ele).clone().addClass("print-box").appendTo($space);
    window.focus();
    window.print();
    window.close();
}

function load_html(url, element, callback) {
    $.ajax({
        url: url,
        type: "GET",
        dataType: "html",
        success: function (data) {
            $(element).html(data);
        }
    });

    if (callback && typeof (callback) === "function") {
        callback();
    }
}

function download_eod_csv(url) {
    window.open(url, '_blank');
}

function download_file(url) {
  window.open(url, '_blank');
}

function resync_order_status_online(obj, url) {
    NProgress.start();
    $(obj).button('loading');
    $.ajax({
        url: url,
        dataType: "json",
        success: function (data) {
            if (data.status) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
            console.log(data);
        },
        error: function(data) {
            toastr.error(data.responseJSON.message);
            console.log(data.responseJSON);
        },
        complete: function () {
            NProgress.done();
            $(obj).button('reset');
            $('.btn-facebook').click();
        }
    });
}

function resync_menu(obj, url) {
    NProgress.start();
    $(obj).button('loading');
    $.ajax({
        url: url,
        dataType: "json",
        success: function (data) {
            if (data.status) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
            console.log(data);
        },
        error: function(data) {
            toastr.error(data.responseJSON.message);
            console.log(data.responseJSON);
        },
        complete: function () {
            NProgress.done();
            $(obj).button('reset');
            $('.btn-facebook').click();
        }
    });
}

function send_fpt_report(obj, eod_id) {
    NProgress.start();
    $(obj).button('loading');
    $.ajax({
        url: route("report/send_ftp_eod"),
        dataType: "json",
        data: {id:eod_id},
        success: function (response) {
            if (response.status !== true) {
                toastr.error(response.message);
            } else {
                toastr.success(response.message);
                if (response.result.url_download == '') {
                    toastr.error('File not found');
                    return false;
                }
                download_eod_csv(response.result.url_download);
            }
        },
        complete: function () {
            NProgress.done();
            $(obj).button('reset');
            $('.btn-facebook').click();
        }
    });
}

function download_report(url, type) {
    var params = {
        fromDate: $('input[name="fromDate"]').val(),
        toDate: $('input[name="toDate"]').val(),
        export_type: type
    };
    var query = $.param(params);
    window.open(url + '?' + query, '_blank');
}

function resync_order_delivery(obj, url) {
    NProgress.start();
    $(obj).button('loading');
    $.ajax({
        url: url,
        dataType: "json",
        success: function (data) {
            if (data.status) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
            console.log(data);
        },
        error: function(data) {
            toastr.error(data.responseJSON.message);
            console.log(data.responseJSON);
        },
        complete: function () {
            NProgress.done();
            $(obj).button('reset');
            $('.btn-facebook').click();
        }
    });

}

function call_ajax(obj, id, url, method, param) {
    NProgress.start();
    $(obj).button('loading');
    var json_param = jQuery.parseJSON(param);
    $.ajax({
        url: url,
        type: method,
        data: json_param,
        dataType: "json",
        success: function () {
            var url = route('update-status-success/');
            url = url.concat(id);
            $.ajax({
                url: url,
                type: 'post',
                data: {id: id},
                dataType: "json",
                success: function (data) {
                    toastr.success(data.message);
                    location.reload();
                },
                error: function() {
                    toastr.error('Update status fail.');
                }
            });
        },
        error: function() {
            toastr.error('Resync fail.');
        }
    });
}

function truncateTable() {
    swal({
            title: 'Are you sure truncate data?', showCancelButton: true
        },
        function(isConfirm) {
            if (isConfirm) {
                var arr_table = [];
                $("table tr").each(function () {
                    if ($(this).find('div').attr('aria-checked') == 'true') {
                        arr_table.push($(this).find('span').html());
                    }
                });
                //truncate
                if (arr_table.length === 0) {
                    toastr.error('Please choose table to truncate.');
                } else {
                    var url = route('truncate-table');
                    $.ajax({
                        url: url,
                        type: 'post',
                        data: {arr_table: arr_table},
                        dataType: "json",
                        success: function () {
                            toastr.success('Truncate data success.');
                            location.reload();
                        },
                        error: function() {
                            toastr.error('Truncate data failed.');
                        }
                    });
                }
            }

        });
}

function resync_order_pos_to_jumpbox(obj, id, url, method, param) {
    NProgress.start();
    $(obj).button('loading');
    var json_param = jQuery.parseJSON(param);
    $.ajax({
        url: url,
        method: 'POST',
        data: json_param,
        dataType: "json",
        success: function (data) {
            var url = route('update-status-log-jobs/');
            url = url.concat(id);
            $.ajax({
                url: url,
                type: 'POST',
                dataType: "json",
                headers:
                    {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                success: function (data) {
                    if (data.result == 'success'){
                        toastr.success('Resync Order Pos To Jumpbox Success');
                    } else {
                        toastr.error('Resync Order Pos To Jumpbox Fail.');
                    }
                    location.reload();
                },
                error: function() {
                    toastr.error('Resync Order Pos To Jumpbox Fail.');
                    location.reload();
                }
            });
        },
        error: function() {
            toastr.error('Resync Order Pos To Jumpbox Fail.');
            location.reload();
        }
    });
}

