<?php

namespace App\Exports;

use App\Models\SalesTransactionItem;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Models\TransactionSalesItem;

class PenjualanExportDetail implements WithEvents
{
    protected $jenisPembayaran;
    protected $data;
    protected $dateLabel;
    protected $arrayTipePembayaran;

    public function __construct($jenisPembayaran, $data, $dateLabel, $arrayTipePembayaran)
    {
        $this->jenisPembayaran = $jenisPembayaran;
        $this->data = $data;
        $this->dateLabel = $dateLabel;
        $this->arrayTipePembayaran = $arrayTipePembayaran;
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
                // Extend merge cells to include all columns for the detail view
                $lastCol = $this->getColumnLetter(8 + count($this->jenisPembayaran) + 2);
                $event->sheet->mergeCells('A1:' . $lastCol . '1');
                $event->sheet->mergeCells('A2:' . $lastCol . '2');
                $event->sheet->setCellValue('A1', 'LAPORAN TRANSAKSI DETAIL PENJUALAN');
                $event->sheet->setCellValue('A2', $this->dateLabel);
                $event->sheet->getStyle('A1')->applyFromArray($headerTitleStyle);
                $event->sheet->getStyle('A2')->applyFromArray($headerSubTitleStyle);

                // Kolom Header - Disesuaikan dengan format yang diminta
                $event->sheet->setCellValue('A4', 'No');
                $event->sheet->setCellValue('B4', 'Tanggal');
                $event->sheet->setCellValue('C4', 'Order ID');
                $event->sheet->setCellValue('D4', 'Customer');
                $event->sheet->setCellValue('E4', 'Item'); // Kolom Item
                $event->sheet->setCellValue('F4', 'Qty'); // Kolom Qty
                $event->sheet->setCellValue('G4', 'Harga'); // Kolom Harga
                $event->sheet->setCellValue('H4', 'Sub Total Item'); // Kolom Sub Total Item
                $event->sheet->setCellValue('I4', 'Sub Total');

                // Kolom untuk jenis pembayaran (mulai dari J)
                $startColPayment = 'J';
                $colIndex = 0;
                foreach ($this->jenisPembayaran as $payment) {
                    $colLetter = $this->getColumnLetter(ord($startColPayment) - ord('A') + $colIndex);
                    $event->sheet->setCellValue($colLetter . '4', $payment->payment_type);
                    $colIndex++;
                }

                // Kolom tambahan
                $colBelumBayar = $this->getColumnLetter(ord($startColPayment) - ord('A') + $colIndex);
                $colStatusBayar = $this->getColumnLetter(ord($startColPayment) - ord('A') + $colIndex + 1);
                $colTotal = $this->getColumnLetter(ord($startColPayment) - ord('A') + $colIndex + 2);

                // $event->sheet->setCellValue($colBelumBayar . '4', 'Belum Bayar');
                $event->sheet->setCellValue($colStatusBayar . '4', 'Status Bayar');
                $event->sheet->setCellValue($colTotal . '4', 'Total');

                // Apply header style
                $event->sheet->getStyle('A4:' . $colTotal . '4')->applyFromArray($tableHeaderStyle);

                // Process data and populate rows
                $groupedData = $this->data->groupBy('transaction_id');
                $rowIndex = 5;
                $totalSemuanya = 0;
                $totalSubTotal = 0;
                $totalBelumBayar = 0;

                $paymentTotals = array_fill(0, count($this->jenisPembayaran), 0);

                foreach ($groupedData as $transactionId => $records) {
                    $transaction = $records->first();
                    $totalSubTotal += (double) $transaction->sub_total;

                    // Get items for this transaction
                    $items = SalesTransactionItem::where('sales_transaction_id', $transactionId)
                    ->join('product_details', 'product_details.id', '=', 'sales_transaction_items.product_detail_id')
                    ->join('products', 'products.id', '=', 'product_details.product_id')
                    ->leftJoin('variants', 'product_details.variant_id', '=', 'variants.id')
                    ->leftJoin('colors', 'products.id', '=', 'colors.product_id')
                    ->select(
                        'sales_transaction_items.qty',
                        'products.title as product_name',
                        'sales_transaction_items.final_price as product_price',
                        'variants.name as variant',
                        'colors.label as color'
                        )
                        ->get();

                    // For multiline items, we need to create a merged cell for transaction info
                    $startRowIndex = $rowIndex;
                    $itemCount = count($items);
                    $endRowIndex = $startRowIndex + $itemCount - 1;

                    // If there are no items, still show one row
                    if ($itemCount == 0) {
                        $itemCount = 1;
                        $endRowIndex = $startRowIndex;
                    }

                    // Merge cells for transaction info if there are multiple items
                    if ($itemCount > 1) {
                        $event->sheet->mergeCells('A' . $startRowIndex . ':A' . $endRowIndex);
                        $event->sheet->mergeCells('B' . $startRowIndex . ':B' . $endRowIndex);
                        $event->sheet->mergeCells('C' . $startRowIndex . ':C' . $endRowIndex);
                        $event->sheet->mergeCells('D' . $startRowIndex . ':D' . $endRowIndex);
                        $event->sheet->mergeCells('I' . $startRowIndex . ':I' . $endRowIndex);

                        // Merge payment cells
                        $colIdx = 0;
                        foreach ($this->jenisPembayaran as $payment) {
                            $colLetter = $this->getColumnLetter(ord($startColPayment) - ord('A') + $colIdx);
                            $event->sheet->mergeCells($colLetter . $startRowIndex . ':' . $colLetter . $endRowIndex);
                            $colIdx++;
                        }

                        // Merge remaining columns
                        $event->sheet->mergeCells($colBelumBayar . $startRowIndex . ':' . $colBelumBayar . $endRowIndex);
                        $event->sheet->mergeCells($colStatusBayar . $startRowIndex . ':' . $colStatusBayar . $endRowIndex);
                        $event->sheet->mergeCells($colTotal . $startRowIndex . ':' . $colTotal . $endRowIndex);
                    }

                    // Set transaction info
                    $event->sheet->setCellValue('A' . $startRowIndex, $rowIndex - 4);
                    $event->sheet->setCellValue('B' . $startRowIndex, Carbon::parse($transaction->transaction_date)->format('d F Y H:i:s'));
                    $event->sheet->setCellValue('C' . $startRowIndex, $transaction->invoice_id);
                    $event->sheet->setCellValue('D' . $startRowIndex, $transaction->customer_first_name . " " . $transaction->customer_last_name ?? '-');

                    // Set sub_total value without formatting
                    $event->sheet->setCellValueExplicit(
                        'I' . $startRowIndex,
                        (double) $transaction->sub_total,
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                    );
                    $event->sheet->getStyle('I' . $startRowIndex)->getNumberFormat()->setFormatCode('0');

                    // Process payments
                    $paymentsByType = $records->groupBy('payment_type');
                    $totalPayments = 0;

                    // Set payment information
                    $colIdx = 0;
foreach ($this->jenisPembayaran as $payment) {
    $colLetter = $this->getColumnLetter(ord($startColPayment) - ord('A') + $colIdx);

    if (in_array($transaction->payment_status, ["paid", "shipping", "completed"]) && $transaction->payment_type == $payment->id) {
        // isi total transaksi ke kolom sesuai payment_type
        $event->sheet->setCellValueExplicit(
            $colLetter . $startRowIndex,
            (double) $transaction->total,
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
        );
        $event->sheet->getStyle($colLetter . $startRowIndex)->getNumberFormat()->setFormatCode('0');

        // tambahkan ke total per kolom
        $paymentTotals[$colIdx] += (double) $transaction->total;
    } else {
        // selain payment_type yg sesuai → kasih "-"
        $event->sheet->setCellValue($colLetter . $startRowIndex, '-');
    }

    $colIdx++;
}


                    // Hitung sisa pembayaran berdasarkan total yang baru dihitung
                    $belumBayar = $transaction->total - $totalPayments;
                    if ($transaction->status == 'paid') {
                        $belumBayar = 0;
                    }
                    $totalBelumBayar += max(0, $belumBayar);

                    $event->sheet->setCellValue($colStatusBayar . $startRowIndex, ucwords($transaction->payment_status));

                    // Set grand_total without formatting
                    if(in_array($transaction->payment_status, ["paid", "shipping", "completed"])){
                        $event->sheet->setCellValueExplicit(
                            $colTotal . $startRowIndex,
                            (double) $transaction->total,
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                        );
                        $event->sheet->getStyle($colTotal . $startRowIndex)->getNumberFormat()->setFormatCode('0');
                    }

                    // Set item details
                    if ($itemCount > 0) {
                        $currentRow = $startRowIndex;
                        foreach ($items as $item) {
                            $subTotal = $item->qty * $item->product_price;

                            $event->sheet->setCellValue('E' . $currentRow, "{$item->product_name} ({$item->color}, {$item->variant})");
                            $event->sheet->setCellValue('F' . $currentRow, $item->qty);

                            // Set product price without formatting
                            $event->sheet->setCellValueExplicit(
                                'G' . $currentRow,
                                (double) $item->product_price,
                                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                            );
                            $event->sheet->getStyle('G' . $currentRow)->getNumberFormat()->setFormatCode('0');

                            // Set subtotal item without formatting
                            $event->sheet->setCellValueExplicit(
                                'H' . $currentRow,
                                (double) $subTotal,
                                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                            );
                            $event->sheet->getStyle('H' . $currentRow)->getNumberFormat()->setFormatCode('0');

                            $currentRow++;
                        }
                    } else {
                        // If no items, leave these cells empty
                        $event->sheet->setCellValue('E' . $startRowIndex, '-');
                        $event->sheet->setCellValue('F' . $startRowIndex, '-');
                        $event->sheet->setCellValue('G' . $startRowIndex, '-');
                        $event->sheet->setCellValue('H' . $startRowIndex, '-');
                    }

                    if(in_array($transaction->payment_status, ["paid", "shipping", "completed"])){
                        $totalSemuanya += (double) $transaction->total;
                    }
                    $rowIndex = $endRowIndex + 1;
                }

                // Set the footer with totals
                $footerRow = $rowIndex;
                $event->sheet->setCellValue('A' . $footerRow, 'TOTAL');
                $event->sheet->mergeCells('A' . $footerRow . ':H' . $footerRow);
                $event->sheet->getStyle('A' . $footerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Set total sub_total without formatting
                $event->sheet->setCellValueExplicit(
                    'I' . $footerRow,
                    (double) $totalSubTotal,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                );
                $event->sheet->getStyle('I' . $footerRow)->getNumberFormat()->setFormatCode('0');

                // Set payment totals
                $colIdx = 0;
                foreach ($paymentTotals as $total) {
                    $colLetter = $this->getColumnLetter(ord($startColPayment) - ord('A') + $colIdx);
                        $event->sheet->setCellValue($colLetter . $footerRow, '-');
                    $colIdx++;
                }
                $event->sheet->getStyle($colBelumBayar . $footerRow)->getNumberFormat()->setFormatCode('0');

                $event->sheet->setCellValue($colStatusBayar . $footerRow, '');

                $event->sheet->setCellValueExplicit(
                    $colTotal . $footerRow,
                    (double) $totalSemuanya,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                );
                $event->sheet->getStyle($colTotal . $footerRow)->getNumberFormat()->setFormatCode('0');


                $event->sheet->getStyle('A' . $footerRow . ':' . $colTotal . $footerRow)->applyFromArray($tableFooterStyle);

                foreach (range('A', $colTotal) as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)->setAutoSize(true);
                }

                $event->sheet->getStyle('A4:' . $colTotal . $footerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        ];
    }

    private function getColumnLetter($index)
    {
        $baseChar = ord('A');
        if ($index < 26) {
            return chr($baseChar + $index);
        } else {
            $firstChar = chr($baseChar + floor($index / 26) - 1);
            $secondChar = chr($baseChar + ($index % 26));
            return $firstChar . $secondChar;
        }
    }
}