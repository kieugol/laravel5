<?php
$report = $data['report'];
$param_url = http_build_query(array(
    "fromTime" => $from_time,
    "toTime" => $to_time
));
?>

<section class="content-header">
    <h1>End of Day  </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div style="width: 400px; margin:0 auto">
                <p class="bold">From : {{$from_time}}</p>
                <p class="bold">To : {{$to_time}}</p>
                <div class="display-flex mb10">
                    <select id="select-printer" class="form-control input-lg mr10">
                        <?php foreach ($printers as $printer_ip => $printer_name): ?>
                        <option value="<?php echo $printer_ip ?>"><?php echo "$printer_name ($printer_ip)"; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button  onclick="printBillEOD(this)" class="btn btn-primary btn-lg pl25 pr25">PRINT</button>
                    <textarea class="hidden" id="response-eod"><?php echo $response ?></textarea>
                </div>

                <table class="table-payment" style="width: 400px; margin:0 auto; background: white">
                    <tr>
                        <td colspan="2" style="padding:20px 10px !important">
                            <img style="width: 80%; height: auto; display: block; margin:0 auto;" src="<?php echo asset("images/logo-header-bill.png"); ?>"
                        </td>
                    </tr>
                    <tr><td colspan="2">Shop No: {{ $data['outlet_code'] }}</td></tr>
                    <tr><td colspan="2" style="border-bottom: 1px dashed gray !important">End of Day Report: </td></tr>
                    <tr><td colspan="2">End of Day Count: </td></tr>
                    <tr style="border-bottom: 1px dashed gray !important"><td colspan="2">Business Date: {{ $data['date'] }}</td></tr>
                    <tr><td colspan="2" style="text-decoration: underline; font-weight: bold">PAYMENT DETAIL: </td></tr>
                    @foreach( $report['payment_details'] as $payment )
                        <tr>
                            <td colspan="1">{{ $payment['payment_method_name'] }} ({{ $payment['quantity'] }})</td>
                            <td colspan="1" style="text-align: right">{{ number_format($payment['value']) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="1"><strong>TOTAL PAYMENT:</strong></td>
                        <td colspan="1" style="text-align: right; font-weight: bold">{{ number_format($report['payment_total']) }}</td>
                    </tr>

                    <tr><td colspan="2" style="text-decoration: underline; font-weight: bold">O.C DETAIL: </td></tr>
                    @foreach( $report['oc_details'] as $paymentoc )
                        <tr>
                            <td colspan="1">{{ $paymentoc['payment_method_name'] }} ({{ $paymentoc['quantity'] }})</td>
                            <td colspan="1" style="text-align: right">{{ number_format($paymentoc['value']) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="1"><strong>TOTAL O.C:</strong></td>
                        <td colspan="1" style="text-align: right; font-weight: bold">{{ number_format($report['oc_total']) }}</td>
                    </tr>
                    <tr style="border-top: 1px dashed gray !important; border-bottom: 1px dashed gray !important; font-weight: bold">
                        <td>Grand Total</td>
                        <td style="text-align: right">{{ number_format($report['grand_total']) }}</td>
                    </tr>
                    <tr><td colspan="2">End of Report </td></tr>
                    <tr><td colspan="2">Read By: <?php echo $read_by; ?></td></tr>
                    <tr>
                        <td colspan="2">
                            <p style="text-align: center; margin-top:20px"><?php echo date("d-m-Y", strtotime($date)) . " " . date("H:i:s"); ?></p>
                        </td>
                    </tr>
                </table>
                <div class="mt30"></div>
            </div>
        </div>
        <div class="col-md-6">
            <button style="max-width: 200px" onclick="$('#modal-eod-confirmation').modal('show')" class="btn btn-success btn-lg btn-full bt-report">REPORT</button>
            <br><br>
            <div class="box box-report hidden">
                <table class="table table-bordered">
                    <tr>
                        <td>Report Payment Non Cash</td>
                        <td class="text-center">
                            <button onclick="print_element('#report-payment-non-cash #box-report')" class="btn btn-primary disabled" data-element="#report-payment-non-cash" data-url="<?php echo admin_url("report/payment-non-cash") . "?printview=1&$param_url" ?>">Print</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Report Delivery Log</td>
                        <td class="text-center"><button onclick="print_element('#report-delivery-log #box-report')" class="btn btn-primary disabled" data-element="#report-delivery-log" data-url="<?php echo admin_url("report/summary-log") . "?printview=1&type=D&$param_url" ?>">Print</button></td>
                    </tr>
                    <tr>
                        <td>Report By Partner</td>
                        <td class="text-center"><button onclick="print_element('#report-partner #box-report')" class="btn btn-primary disabled" data-element="#report-partner" data-url="<?php echo admin_url("report/partner") . "?printview=1&$param_url" ?>">Print</button></td>
                    </tr>
                    <tr>
                        <td>Report Mix By Segment</td>
                        <td class="text-center"><button onclick="print_element('#report-sales-mix-by-segment #box-report')" class="btn btn-primary disabled" data-element="#report-sales-mix-by-segment" data-url="<?php echo admin_url("report/sales-mix-by-segment") . "?printview=1&$param_url" ?>">Print</button></td>
                    </tr>
                    <tr>
                        <td>Report Summary of Sales</td>
                        <td width="100" class="text-center"><button onclick="print_element('#report-summary-of-sales #box-report', 'portrait')" class="btn btn-primary disabled" data-element="#report-summary-of-sales" data-url="<?php echo admin_url("report/summary") . "?printview=1&$param_url" ?>">Print</button></td>
                    </tr>
                </table>
            </div>
            <!-- Display status csv-->
            <div class="box box-csv hidden">
                <table class="table table-bordered">
                    <tr>
                        <td>CKHEADER.csv</td>
                        <td width="100" class="text-center t20"><i class="fa fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td>DELIVERYLOG.csv</td>
                        <td class="text-center t20"><i class="fa fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td>ITEMTRS.csv</td>
                        <td class="text-center t20"><i class="fa fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td>MSMEMBER.csv</td>
                        <td class="text-center t20"><i class="fa fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td>PAYMENT.csv</td>
                        <td class="text-center t20"><i class="fa fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td>TAKEAWAYBY.csv</td>
                        <td class="text-center t20"><i class="fa fa-check text-success"></i></td>
                    </tr>
                </table>
            </div>
            <!-- Display status csv-->
            <div class="box hidden">
                <button onclick="reportEOD()" id="eod-directly">Trigger EOD</button>
            </div>
        </div>
    </div>
</section>

<div id="list-report" class="hidden">
    <div id="report-payment-non-cash"></div>
    <div id="report-delivery-log"></div>
    <div id="report-partner"></div>
    <div id="report-sales-mix-by-segment"></div>
    <div id="report-summary-of-sales"></div>
</div>
<!-- Modal -->
<div class="modal fade" id="modal-report" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">REPORT</h4>
            </div>
            <div class="modal-body">
                <div id="progress" class="progress">
                    <div class="progress-bar progress-bar-success progress-bar-striped"
                         role="progressbar"
                         aria-valuenow="0"
                         aria-valuemin="0"
                         aria-valuemax="100"
                         style="width:0%; transition: none">
                        <span class="sr-only"></span>
                    </div>
                </div>

                <button class="btn-finish btn btn-full btn-lg btn-primary hidden" data-dismiss="modal">FINISH</button>
            </div>
        </div>
    </div>
</div>

<!--Modal confirmation-->
<div class="modal fade" id="modal-eod-confirmation" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"><b>Confirmation</b></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 col-lg-12 col-md-12"><b>Do you want to send FTP report?</b></div>
                </div>
                <div class="mt30 text-center">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button onclick="reportEOD()" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>

<textarea id="report-type" class="hidden">
    {{ json_encode($report_type) }}
</textarea>
<input type="hidden" id="date-eod" value="<?php echo $date; ?>" />
<input type="hidden" id="from-time" value="<?php echo $from_time; ?>" />
<input type="hidden" id="to-time" value="<?php echo $to_time; ?>" />
<input type="hidden" id="eod-id" value="" />

<script>
  var total_success = 0;
    $(document).ready(function () {
        $(".box-report button").each(function (index, obj) {
            var url = $(obj).data("url");
            var element = $(obj).data("element");

            $.ajax({
                url: url,
                type: "GET",
                dataType: "html",
                success: function (data) {
                    $(element).append(data);
                    $(obj).removeClass("disabled");
                }
            });
        });

        $('.btn-download').click(function(){
            var path = $(this).attr('csv_path');
            if (path == '' || path == null) {
                toastr.error('The path not found!');
            } else {
                download_eod_csv(path);
            }
        });
    });

    function printBillEOD(obj) {
        var data = {data: $("#response-eod").val(), ip: $("#select-printer option:selected").val()};
        $(obj).button("loading");
        $.ajax({
            url: route("report/print-bill"),
            data: data,
            type: "POST",
            success: function (data) {
                if (data.result === false) {
                    alert(data.msg);
                    print_element('.table-payment')
                }
            },
            error: function () {
                alert("Can not connect to printer");
                print_element('.table-payment')
            },
            complete: function (data) {
                $(obj).button("reset");
            }
        });
    }

    function reportEOD() {
        var obj = $(".bt-report");
        var json = $("#report-type").val();
        var types = JSON.parse(json);
        var requests = [];
        total_success = 0;

        $('#modal-eod-confirmation').modal('hide');
        $("#progress .progress-bar").css("width", "0%").animate({width: "99%"}, 30000);
        $("#modal-report .btn-finish").addClass("hidden");
        $("#modal-report").modal("show");

        for (var i = 0; i < types.length; i++) {
            requests[i] = $.ajax({
                url: route("report/push_ftp"),
                data: {report_type: types[i].type, date: $("#date-eod").val(), from_time: $("#from-time").val(), to_time: $("#to-time").val()},
                type: "GET",
                dataType: "json",

                success: function (res) {
                    console.log(res);
                    if (res.result == true) {
                        total_success++;
                        if (total_success == types.length) {
                            $.ajax({
                                url: route("report/finish_eod"),
                                dataType: "json",
                                data: {
                                    from_time: "<?php echo $from_time ?>",
                                    to_time: "<?php echo $to_time ?>",
                                    fpt_folder: res.data.result.fpt_folder,
                                    path_file: res.data.result.path_file
                                },
                                success: function (response) {
                                    if (response.status !== true) {
                                        $("#progress .progress-bar").stop(true, true).animate({width: "100%"}, 1000);
                                        $("#modal-report").modal("hide");
                                        swal({
                                            title: 'Error!',
                                            type: 'error',
                                            text: response.message,
                                            html:true
                                        });
                                        $(obj).removeClass("btn-success").addClass("btn-warning").text("RE-SEND");
                                    } else {
                                        $("#progress .progress-bar").stop(true, true).animate({width: "100%"}, 1000);
                                        $("#modal-report").modal("hide");
                                        setTimeout(function () {
                                            $("#modal-report .btn-finish").removeClass('hidden');
                                            $(".box-report, .box-csv").removeClass("hidden");
                                        }, 1000);
                                        $(obj).addClass("disabled").removeAttr("onclick");
                                        $(obj).removeClass("btn-warning").addClass("btn-success").text("SENT");
                                        swal({
                                            title: 'Information!',
                                            type: 'info',
                                            text: response.message,
                                            html:true
                                        })
                                    }
                                }
                            });
                        }
                    } else {
                        $("#progress .progress-bar").stop(true, true);
                        for (var j in requests) {
                            requests[j].abort();
                        }
                        $("#modal-report").modal("hide");
                        swal({
                            title: 'Error!',
                            type: 'error',
                            text: res.error.message,
                            html:true
                        })
                    }
                }
            });
        }
    }
</script>

<style>
    table.table-payment tr td{padding:2px 10px !important}
    @media print{@page {size: landscape !important} }
</style>
