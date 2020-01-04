<?php

namespace App\Admin\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SetoranTunaiBankExport implements FromView
{
    protected  $data = '';

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('report.setoran_tunai_bank', $this->data);
    }
}
