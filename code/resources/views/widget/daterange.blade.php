<?php
    $from_value = request($from);
    $to_value = request($to);
    $class_display_to = !$display_to ? 'hidden' : '';
?>

<div style="display: inline-block">
    <div style="width: 190px; float:left">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="text" name="{{ $from }}" value="{{ $from_value }}" placeholder="From date" class="form-control {{ $from }}" style="width: 150px">
        </div>
    </div>

    <div style="width: 190px; float:left" class="{{$class_display_to}}">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="text" name="{{ $to }}" value="{{ $to_value }}" placeholder="To date" class="form-control {{ $to }}" style="width: 150px">
        </div>
    </div>
</div>

<script >
    $(document).ready(function () {
//        $('.{{ $from }}').datetimepicker({"format": "YYYY-MM-DD"});
//        $('.{{ $to }}').datetimepicker({"format": "YYYY-MM-DD"});
        $('.{{ $from }}').datetimepicker(<?php echo json_encode($option_from); ?>);
        $('.{{ $to }}').datetimepicker(<?php echo json_encode($option_to); ?>);
        $(".{{ $from }}").on("dp.change", function (e) {
            $('.{{ $to }}').data("DateTimePicker").minDate(e.date);
        });
        $(".{{ $to }}").on("dp.change", function (e) {
            $('.{{ $from }}').data("DateTimePicker").maxDate(e.date);
        });
    });
</script>
