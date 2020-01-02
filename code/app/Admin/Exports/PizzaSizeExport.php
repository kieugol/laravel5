<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 12/5/2018
 * Time: 4:35 PM
 */

namespace App\Admin\Exports;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PizzaSizeExport implements FromView
{
    protected  $data = '';

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('report.pizza_size', $this->data);
    }
}