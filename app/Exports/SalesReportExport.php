<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class SalesReportExport implements WithEvents, WithTitle, ShouldAutoSize
{
    use Exportable, RegistersEventListeners;

    protected $data;
    protected $dateLabel;

    function __construct($salesReport, $dateLabel)
    {
        $this->data = $salesReport;
        $this->dateLabel = $dateLabel;
    }

    public function title(): string
    {
        return "Report Performa Tim Sales";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Gaya untuk Header dan Sub-Header
                $headerTitleStyle = [
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'name' => 'Calibri'
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center'
                    ]
                ];

                $headerSubTitleStyle = [
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'name' => 'Calibri'
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center'
                    ]
                ];

                // Header untuk Tabel
                $tableHeaderStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center'
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => '00008B']
                    ]
                ];

                // Footer untuk Tabel
                $tableFooterStyle = [
                    'font' => [
                        'bold' => true
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center'
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'C0C0C0']
                    ]
                ];

                // Judul Laporan
                $event->sheet->mergeCells('A1:M1');
                $event->sheet->mergeCells('A2:M2');
                $event->sheet->setCellValue('A1', 'LAPORAN PERFORMA TIM SALES');
                $event->sheet->setCellValue('A2', $this->dateLabel);
                $event->sheet->getStyle('A4:D4')->applyFromArray($tableHeaderStyle);
                $event->sheet->getStyle('A1')->applyFromArray($headerTitleStyle);
                $event->sheet->getStyle('A2')->applyFromArray($headerSubTitleStyle);

                // Kolom Header
                $event->sheet->setCellValue('A4', 'No');
                $event->sheet->setCellValue('B4', 'Nama Sales');
                $event->sheet->setCellValue('C4', 'Total Transaksi');
                $event->sheet->setCellValue('D4', 'Total Nilai Transaksi');

                $arrayLabel = ['F', 'G', 'H', 'I'];
                // Data rows
                $rowIndex = 5;
                $totalTransaksi = 0;
                $totalNominal = 0;
             
                foreach ($this->data as $index => $row) {
                    $event->sheet->setCellValue('A' . $rowIndex, $index + 1);
                    $event->sheet->setCellValue('B' . $rowIndex, $row->nama_sales);
                    $event->sheet->setCellValue('C' . $rowIndex, $row->jumlah_transaksi);
                    $event->sheet->setCellValue('D' . $rowIndex, 'Rp' . number_format($row->total, 0, ',', '.'));

                    $totalTransaksi += $row->jumlah_transaksi;
                    $totalNominal += $row->total;
                    $rowIndex++;
                }

                $event->sheet->getStyle('A5:D' . ($rowIndex - 1))->getAlignment()->setHorizontal('center');

                // Footer
                $event->sheet->setCellValue('A' . $rowIndex, 'TOTAL');
                $event->sheet->mergeCells('A' . $rowIndex . ':B' . $rowIndex);
                $event->sheet->setCellValue('C' . $rowIndex, $totalTransaksi);
                $event->sheet->setCellValue('D' . $rowIndex, 'Rp' . number_format($totalNominal, 0, ',', '.'));
                $event->sheet->getStyle('A' . $rowIndex . ':D' . $rowIndex)->getFont()->setBold(true);
                $event->sheet->getStyle('A' . $rowIndex . ':D' . $rowIndex)->getAlignment()->setHorizontal('center');

                foreach (range('A', 'D') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
      
            }
        ];
    }
}
