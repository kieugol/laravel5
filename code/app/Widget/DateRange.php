<?php

namespace App\Widget;

class DateRange
{
    
    /**
     * Column name.
     *
     * @var string
     */
//    protected $column = [];
//
    private $from = null;
    private $display_from = true;
    private $to = null;
    private $display_to = true;
    private $option_from = null;
    private $option_to = null;
    
    
    public function __construct($from, $to, $option_from = array(), $option_to = array())
    {
        $this->from        = $from;
        $this->to          = $to;
        $this->option_from = $option_from;
        $this->option_to   = $option_to;
    }
    
    public function setFromDate($date, $isDisplay = true)
    {
        $this->option_from['defaultDate'] = $date;
        $this->display_from               = $isDisplay;
        return $this;
    }
    
    public function setToDate($date, $isDisplay = true)
    {
        $this->option_to['defaultDate'] = $date;
        $this->display_to               = $isDisplay;
        return $this;
    }
    
    
    public function render()
    {
        $data['from']                  = $this->from;
        $data['display_from']          = $this->display_from;
        $data['to']                    = $this->to;
        $data['display_to']            = $this->display_to;
        $data['option_from']           = $this->option_from;
        $data['option_to']             = $this->option_to;
        $data['option_from']['format'] = "YYYY-MM-DD";
        $data['option_to']['format']   = "YYYY-MM-DD";
        
        return view("widget.daterange", $data)->render();
//        $startOptions = json_encode(array());
//        $endOptions = json_encode(array());

//        $this->script = <<< EOT
//            $('.aa').datetimepicker();
//            $('.bb').datetimepicker();
//            $(".aa").on("dp.change", function (e) {
//                $('.bb').data("DateTimePicker").minDate(e.date);
//            });
//            $(".bb").on("dp.change", function (e) {
//                $('.aa').data("DateTimePicker").maxDate(e.date);
//            });
//EOT;
//
//        Admin::script($this->script);
    }
    
}
