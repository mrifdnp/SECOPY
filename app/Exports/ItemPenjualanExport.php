<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ItemPenjualanExport implements WithEvents, WithTitle, ShouldAutoSize
{
    use Exportable, RegistersEventListeners;

    protected $data;
    protected $dateLabel;
    protected $store;

    public function __construct($groupedData, $dateLabel, $store)
    {
        $this->data = $groupedData;
        $this->dateLabel = $dateLabel;
        $this->store = $store;
    }

    public function title(): string
    {
        return "Report Item Penjualan";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // === STYLING ===
                $headerTitleStyle = [
                    'font' => ['bold' => true, 'size' => 14, 'name' => 'Calibri'],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ];

                $headerSubTitleStyle = [
                    'font' => ['bold' => true, 'size' => 12, 'name' => 'Calibri'],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ];

                $tableHeaderStyle = [
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => '00008B']
                    ]
                ];

                // === HEADER REPORT ===
                $event->sheet->mergeCells('A1:D1');
                $event->sheet->mergeCells('A2:D2');
                $event->sheet->setCellValue('A1', 'LAPORAN PENJUALAN DATA ITEMS ' . strtoupper($this->store['name']));
                $event->sheet->setCellValue('A2', $this->dateLabel);
                $event->sheet->getStyle('A1')->applyFromArray($headerTitleStyle);
                $event->sheet->getStyle('A2')->applyFromArray($headerSubTitleStyle);

                // === HEADER TABLE ===
                $event->sheet->setCellValue('A4', 'No');
                $event->sheet->setCellValue('B4', 'Tanggal');
                $event->sheet->setCellValue('C4', 'Nama Barang');
                $event->sheet->setCellValue('D4', 'Qty');
                $event->sheet->getStyle('A4:D4')->applyFromArray($tableHeaderStyle);

                // === DATA ROWS ===
                $rowIndex = 5;
                $no = 1;

                foreach ($this->data as $tanggal => $itemsByDate) {
                    // Group lagi berdasarkan nama barang + varian + warna
                    $groupedProducts = $itemsByDate->groupBy(function ($item) {
                        return $item->product_name . '|' . $item->variant . '|' . $item->color;
                    });

                    $firstRow = true;

                    foreach ($groupedProducts as $productKey => $items) {
                        $firstItem = $items->first();
                        $totalQty = $items->sum('qty');

                        if ($firstRow) {
                            $event->sheet->setCellValue('A' . $rowIndex, $no++);
                            $event->sheet->setCellValue('B' . $rowIndex, Carbon::parse($firstItem->date)->format('d/m/Y'));
                            $firstRow = false;
                        }

                        $event->sheet->setCellValue('C' . $rowIndex, $firstItem->product_name . ' (' . $firstItem->variant . ', ' . $firstItem->color . ')');
                        $event->sheet->setCellValue('D' . $rowIndex, $totalQty);
                        $rowIndex++;
                    }
                }

                $event->sheet->getStyle('A5:D' . ($rowIndex - 1))
                    ->getAlignment()->setHorizontal('center');

                foreach (range('A', 'D') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}
