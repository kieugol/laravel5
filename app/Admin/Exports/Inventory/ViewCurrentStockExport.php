<?php

namespace App\Admin\Exports\Inventory;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ViewCurrentStockExport implements FromView
{
    protected  $data = '';
    
    public function __construct($data = null)
    {
        $this->data = $data;
    }
    
    public function view(): View
    {
        return view('inventory_report.current_stock', $this->data);
    }
}
