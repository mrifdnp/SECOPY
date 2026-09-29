<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;


class PenjualanPelangganExport implements WithEvents, WithTitle, ShouldAutoSize
{
    use Exportable, RegistersEventListeners;

    protected $groupedData;
    protected $dateLabel;
    protected $store;

    public function __construct($groupedData, $dateLabel, $store)
    {
        $this->groupedData = $groupedData;
        $this->dateLabel = $dateLabel;
        $this->store = $store;
    }

    public function title(): string
    {
        return "Report Penjualan Pelanggan";
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
                $event->sheet->mergeCells('A1:E1');
                $event->sheet->mergeCells('A2:E2');
                $event->sheet->setCellValue('A1', 'LAPORAN PENJUALAN PELANGGAN ' . strtoupper($this->store['name']));
                $event->sheet->setCellValue('A2', $this->dateLabel);
                $event->sheet->getStyle('A1')->applyFromArray($headerTitleStyle);
                $event->sheet->getStyle('A2')->applyFromArray($headerSubTitleStyle);

                // === HEADER TABLE ===
                $event->sheet->setCellValue('A4', 'No');
                $event->sheet->setCellValue('B4', 'Nama Pelanggan');
                $event->sheet->setCellValue('C4', 'Email');
                $event->sheet->setCellValue('D4', 'No Hp');
                $event->sheet->setCellValue('E4', 'Total Transaksi');
                $event->sheet->getStyle('A4:E4')->applyFromArray($tableHeaderStyle);
                // $event->sheet->getStyle('D')->getNumberFormat()
                // ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);


                // === DATA ROWS ===
                $rowIndex = 5;
                $no = 1;

                foreach ($this->groupedData as $pelanggan) {
                    $event->sheet->setCellValue('A' . $rowIndex, $no++);
                    $event->sheet->setCellValue('B' . $rowIndex, $pelanggan['first_name'] . ' ' . $pelanggan['last_name']);
                    $event->sheet->setCellValue('C' . $rowIndex, $pelanggan['email']);
                    $event->sheet->setCellValueExplicit(
                        'D' . $rowIndex,
                        $pelanggan['phone'],
                        DataType::TYPE_STRING
                    );
                    $event->sheet->setCellValue('E' . $rowIndex, $pelanggan['total']);
                     $rowIndex++;
                }

                $event->sheet->getStyle('A5:D' . ($rowIndex - 1))
                    ->getAlignment()->setHorizontal('center');

                foreach (range('A', 'E') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}
