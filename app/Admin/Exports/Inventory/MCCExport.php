<?php

namespace App\Admin\Exports\Inventory;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MCCExport implements FromView, WithEvents
{
    protected $data = '';
    
    public function __construct($data = null)
    {
        $this->data = $data;
    }
    
    public function view(): View
    {
        return view('inventory_report.mcc_report', $this->data);
    }
    
    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class  => function(BeforeExport $event) {
            },
            AfterSheet::class => function(AfterSheet $event) {
                $endingColumnName      = $event->sheet->getDelegate()->getHighestColumn();
                $currentColNameWriting = $event->sheet->getDelegate()->getCellCollection()->getCurrentColumn();
                
                // Set auto width for some ending column
                $arrColumn = ['T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'];
                foreach ($arrColumn as $column) {
                    $event->sheet->getDelegate()->getColumnDimension($column)->setAutoSize(true);
                }
                // Make scrolling horizontal and vertical
                $event->sheet->getDelegate()->freezePane("A14");
                $event->sheet->getDelegate()->freezePane("D14");
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(40);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension($currentColNameWriting)->setAutoSize(true);
                // Set font bold header
                $event->sheet->getDelegate()->getStyle("A12:{$endingColumnName}12")->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle("A13:{$endingColumnName}13")->getFont()->setBold(true);
                // Set border header
                $event->sheet->getDelegate()->getStyle("A13:{$endingColumnName}13")->applyFromArray(
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '808080']
                            ],
                        ]
                    ]
                );
                $event->sheet->getDelegate()->getStyle('C1:C5000')->getBorders()->applyFromArray(
                    [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '808080']
                        ]
                    ]
                );

                $startRow        = 14;
                $initRow         = 15;
                $endingRowDetail = 0;
                $detailData      = $this->data['data_detail'];
                $accountData     = $this->data['data_account'];
                $GroupData       = $this->data['data_group'];
                
                foreach ($GroupData as $groupId => $detailGroup) {
                    if ($groupId == $this->data['key_total_group_fb'] || !isset($accountData[$groupId])) {
                        continue;
                    }
                    foreach ($accountData[$groupId] as $accountId => $detailAccount) {
                        // make bold for account
                        $event->sheet->getDelegate()->getStyle("A{$startRow}:{$endingColumnName}{$startRow}")->getFont()->setBold(true);
                        $endingRow       = $startRow + count($detailData[$accountId]) + 1;
                        $startRow        = $endingRow + 1;
                        $endingRowDetail = $startRow;
                            // make bold account total
                        $event->sheet->getDelegate()->getStyle("A{$endingRow}:{$endingColumnName}{$endingRow}")->getFont()->setBold(true);
                        $event->sheet->getDelegate()->getStyle("D{$endingRow}:{$endingColumnName}{$endingRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                    $seqGroup = $endingRowDetail;
                    // make bold for group
                    $event->sheet->getDelegate()->getStyle("D{$seqGroup}:{$endingColumnName}{$seqGroup}")->getFont()->setBold(true);
                    $event->sheet->getDelegate()->getStyle("D{$seqGroup}:{$endingColumnName}{$seqGroup}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    
                    $startRow += 2; // go next 2 rows for each group
                }
    
                // format cell as Dashes
                for ($i = $initRow; $i <= $endingRowDetail; $i++) {
                    $event->sheet->getDelegate()->getStyle("A{$i}:{$endingColumnName}{$i}")->getNumberFormat()->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                }
                
                // Make bold text for summary data
                $indexSummary     = $endingRowDetail + 4;
                $totalLineSummary = 100;
                for ($i = $indexSummary; $i <= ($indexSummary + $totalLineSummary); $i++) {
                    $event->sheet->getDelegate()->getStyle("A{$i}:{$endingColumnName}{$i}")->getNumberFormat()->setFormatCode('_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)');
                    $event->sheet->getDelegate()->getStyle("A{$i}:{$endingColumnName}{$i}")->getFont()->setBold(true);
                }
            }
        ];
    }
}
