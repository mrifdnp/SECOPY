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

class PenjualanExport implements WithEvents, WithTitle, ShouldAutoSize
{
    use Exportable, RegistersEventListeners;

    protected $jenisPembayaran;
    protected $data;
    protected $dateLabel;

    function __construct($jenisPembayaran, $data, $dateLabel)
    {
        $this->jenisPembayaran = $jenisPembayaran;
        $this->data = $data;
        $this->dateLabel = $dateLabel;
    }

    public function title(): string
    {
        return "Report Penjualan";
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
                $event->sheet->setCellValue('A1', 'LAPORAN TRANSAKSI PENJUALAN');
                $event->sheet->setCellValue('A2', $this->dateLabel);
                $event->sheet->getStyle('A1')->applyFromArray($headerTitleStyle);
                $event->sheet->getStyle('A2')->applyFromArray($headerSubTitleStyle);

                // Kolom Header
                $event->sheet->setCellValue('A4', 'No');
                $event->sheet->setCellValue('B4', 'Tanggal');
                $event->sheet->setCellValue('C4', 'Kode Transaksi');
                $event->sheet->setCellValue('D4', 'Pelanggan');
                $event->sheet->setCellValue('E4', 'Subtotal Produk');
                $event->sheet->setCellValue('F4', 'Ongkir');

                $arrayLabel = ['G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P']; 
                foreach ($this->jenisPembayaran as $key => $payment) {
                    $event->sheet->setCellValue($arrayLabel[$key] . '4', $payment->payment_type);
                }

                $colBelumBayar = $arrayLabel[count($this->jenisPembayaran)] ?? 'K';
                $colStatusBayar = chr(ord($colBelumBayar) + 1);
                $colTotal = chr(ord($colStatusBayar) + 1);

                // $event->sheet->setCellValue($colBelumBayar . '4', 'Belum Bayar');
                $event->sheet->setCellValue($colStatusBayar . '4', 'Status Bayar');
                $event->sheet->setCellValue($colTotal . '4', 'Total');

                $event->sheet->getStyle('A4:' . $colTotal . '4')->applyFromArray($tableHeaderStyle);

             
                $groupedData = $this->data->groupBy('transaction_id');

                $rowIndex = 5;
                $subtotal = 0;
                $totalSemuaOngkir = 0;
                $totalSemuanya = 0;

      
                $paymentTotals = array_fill(0, count($this->jenisPembayaran), 0);

                foreach ($groupedData as $firstItemId => $items) {
                    $firstItem = $items->first();
                    $event->sheet->setCellValue('A' . $rowIndex, $rowIndex - 4); 
                    $event->sheet->setCellValue('B' . $rowIndex, Carbon::parse($firstItem->date)->format('d F Y')); 
                    $event->sheet->setCellValue('C' . $rowIndex, $firstItem->invoice_id); 
                    $event->sheet->setCellValue('D' . $rowIndex, $firstItem->customer_first_name . ' ' . $firstItem->customer_last_name  ?? '-'); 
                    $courier = is_string($firstItem->courier) ? json_decode($firstItem->courier, true) : $firstItem->courier;
                    $ecommerceOngkir = $courier['cost'] ?? 0;
                    $posOngkir = $firstItem->additional_cost ?? 0;
                    $totalOngkir = $ecommerceOngkir + $posOngkir;

                    $event->sheet->setCellValue('E' . $rowIndex, 'Rp. ' . number_format($firstItem->sub_total, 0, ',', '.'));
                    $event->sheet->setCellValue('F' . $rowIndex, 'Rp. ' . number_format($totalOngkir, 0, ',', '.'));
                    $subtotal += $firstItem->sub_total;
                    $totalSemuaOngkir += $totalOngkir;
    
                    $colIndex = 0;
                    $totalPayments = 0; 
    
                    foreach ($this->jenisPembayaran as $payment) {
                        // $paymentAmount = $items->where('payment_type_id', $payment->id)->sum('payment_amount');

                        if(in_array($firstItem->payment_status, ["paid", "shipping", "completed"])){
                            if((string)$payment->id == (string)$firstItem->payment_type){
                                $event->sheet->setCellValue(
                                $arrayLabel[$colIndex] . $rowIndex,
                                'Rp. ' . number_format($firstItem->total, 0, ',', '.')
                                );

                                $paymentTotals[$colIndex] += (double) $firstItem->total;
                            }else{
                                $event->sheet->setCellValue(
                                $arrayLabel[$colIndex] . $rowIndex,
                                '-'
                                );
                            }
                        }else{
                            $event->sheet->setCellValue(
                                $arrayLabel[$colIndex] . $rowIndex,
                                '-'
                            );
                        }
                        $colIndex++;
                    }


                    
                    // $event->sheet->setCellValue($colBelumBayar . $rowIndex, $belumBayar > 0 ? $belumBayar : '-');
                    $event->sheet->setCellValue($colStatusBayar . $rowIndex, ucwords($firstItem->payment_status)); // Status Bayar
                    if (in_array($firstItem->payment_status, ["paid", "shipping", "completed"])) {
                        $event->sheet->setCellValue($colTotal . $rowIndex, 'Rp. ' . number_format($firstItem->total, 0, ',', '.'));
                        $totalSemuanya += $firstItem->total;
                    } else {
                        $event->sheet->setCellValue($colTotal . $rowIndex, '-'); 
                    }
    
                    $rowIndex++;
                }

               
                $event->sheet->setCellValue('A' . $rowIndex, 'Total');
                $event->sheet->mergeCells('A' . $rowIndex . ':D' . $rowIndex);
                $event->sheet->setCellValue('E' . $rowIndex, 'Rp. ' . number_format($subtotal, 0, ',', '.'));
                $event->sheet->setCellValue('F' . $rowIndex, 'Rp. ' . number_format($totalSemuaOngkir, 0, ',', '.'));

                $colIndex = 0;
                foreach ($paymentTotals as $total) {
                    $event->sheet->setCellValue($arrayLabel[$colIndex] . $rowIndex, $total > 0 ? 'Rp. ' . number_format($total, 0, ',', '.') : '-');
                    $colIndex++;
                }

            
                // $event->sheet->setCellValue($colBelumBayar . $rowIndex, $totalBelumBayar);
                $event->sheet->setCellValue($colTotal . $rowIndex, 'Rp. ' . number_format($totalSemuanya, 0, ',', '.'));

                $event->sheet->getStyle('A' . $rowIndex . ':' . $colTotal . $rowIndex)->applyFromArray($tableFooterStyle);

                foreach (range('A', $colTotal) as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }
            }
        ];
    }
}
