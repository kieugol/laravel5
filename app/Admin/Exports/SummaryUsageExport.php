<?php

namespace App\Admin\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SummaryUsageExport implements FromView
{
    protected  $data = '';

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('report.summary_usage', $this->data);
    }
}
