<?php

namespace App\Admin\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PaymentByCashierExport implements FromView
{
    protected  $data = '';

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('report.payment_by_cashier', $this->data);
    }
}
