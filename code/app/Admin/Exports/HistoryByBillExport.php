<?php

namespace App\Admin\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class HistoryByBillExport implements FromView, WithEvents
{
    protected  $data = '';

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('report.history_by_bill', $this->data);
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        // TODO
        $data_export = $this->data['data_export'];
        return [
            AfterSheet::class  => function(AfterSheet $event) use($data_export) {
                $event->sheet->setShowGridlines(false);
            },
        ];
    }
}
