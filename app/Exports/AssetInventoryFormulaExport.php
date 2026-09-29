<?php

namespace App\Exports;

use App\Models\AssetInventory;
use App\Models\Store;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AssetInventoryFormulaExport implements WithEvents, WithDrawings, ShouldAutoSize
{
    protected $storeId;
    protected $store;
    protected $assets;
    protected $cutoffDate;

    public function __construct($storeId = null, $cutoffDate = null)
    {
        $activeStore = session('store');
        $activeStoreId = is_array($activeStore) ? ($activeStore['id'] ?? null) : ($activeStore->id ?? null);
        $this->storeId = $storeId ?: $activeStoreId;
        $this->store = Store::find($this->storeId);
        $this->cutoffDate = $cutoffDate ? Carbon::parse($cutoffDate) : Carbon::now();

        $query = AssetInventory::query()->whereNull('deleted_at');
        if ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }
        $this->assets = $query->orderBy('name', 'asc')->get();
    }

    public function drawings()
    {
        $drawings = [];
        $row = 8;
        foreach ($this->assets as $asset) {
            if ($asset->image) {
                $imagePath = public_path('uploads/asset_inventories/' . $asset->image);
                if (file_exists($imagePath)) {
                    $drawing = new Drawing();
                    $drawing->setName($asset->name ?? 'Asset Image');
                    $drawing->setDescription($asset->name ?? 'Asset Image');
                    $drawing->setPath($imagePath);
                    $drawing->setHeight(55);
                    $drawing->setCoordinates('B' . $row);
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(5);
                    $drawings[] = $drawing;
                }
            }
            $row++;
        }
        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1. Info Judul dan Store (Sesuai Format Excel)
                $activeStore = session('store');
                $storeName = $this->store ? $this->store->name : (is_array($activeStore) ? ($activeStore['name'] ?? 'SECO') : ($activeStore->name ?? 'SECO'));

                $sheet->mergeCells('A2:Q2');
                $sheet->setCellValue('A2', 'Laporan Penyusutan Inventaris');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A3:Q3');
                $sheet->setCellValue('A3', '"' . strtoupper($storeName) . '"');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 2. Tanggal Cutoff di Cell A4 (tampil center dan terbaca rumus YEAR($A$4) & MONTH($A$4))
                $sheet->mergeCells('A4:Q4');
                $sheet->setCellValue('A4', \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($this->cutoffDate));
                $sheet->getStyle('A4')->getNumberFormat()->setFormatCode('[$-id-ID]d mmmm yyyy;@');
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 3. Header Tabel di Baris 7
                $headers = [
                    'A7' => 'NAMA BARANG',
                    'B7' => 'PICTURE',
                    'C7' => 'GOLONGAN BARANG',
                    'D7' => 'MERK',
                    'E7' => 'KONDISI',
                    'F7' => '',
                    'G7' => "Umur\nManfaat\n(Tahun)",
                    'H7' => "Tanggal\nPerolehan",
                    'I7' => "Jumlah\nUnit",
                    'J7' => "Harga Per\nUnit",
                    'K7' => "Jumlah Nilai\nPerolehan",
                    'L7' => "Presentase\nPenyusutan\nPer Tahun",
                    'M7' => "Jumlah\nSusut",
                    'N7' => "Batas\nSusut",
                    'O7' => "Penyusutan Per\nBulan",
                    'P7' => "Akumulasi Pnyusutan\nPer Tahun",
                    'Q7' => "Nilai Buku",
                ];

                foreach ($headers as $cell => $text) {
                    $sheet->setCellValue($cell, $text);
                }

                $sheet->getRowDimension(7)->setRowHeight(40);

                // Styling Header:
                // Kolom A - E: Biru muda (#00AEEF / #33bbf5)
                $sheet->getStyle('A7:E7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6CD7F7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                // Kolom G - L: Merah muda / Coral (#FF7575 / #F88E86)
                $sheet->getStyle('G7:L7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FA9E98']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                // Kolom M - N: Kuning (#FFFF00)
                $sheet->getStyle('M7:N7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF200']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                // Kolom O - Q: Peach muda (#FFE8D6 / #FEE1D2)
                $sheet->getStyle('O7:Q7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCE1D5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                // 4. Pengisian Data dan Formula mulai baris 8
                $row = 8;
                foreach ($this->assets as $asset) {
                    $sheet->getRowDimension($row)->setRowHeight(50);

                    $umurManfaat = !empty($asset->umur_manfaat) ? (float) $asset->umur_manfaat : (!empty($asset->masa) ? round($asset->masa / 12, 1) : 4);
                    $qtyUnit = !empty($asset->jumlah_unit) ? (int) $asset->jumlah_unit : 1;
                    $hargaSatuan = !empty($asset->harga_per_unit) ? (float) $asset->harga_per_unit : (!empty($asset->purchase_price) ? (float) $asset->purchase_price / max(1, $qtyUnit) : 0);
                    $tglPerolehan = $asset->tgl ? Carbon::parse($asset->tgl)->format('d/m/Y') : '';

                    // Nilai Input
                    $sheet->setCellValue('A' . $row, $asset->name);
                    $sheet->setCellValue('C' . $row, $asset->category ?? '-');
                    $sheet->setCellValue('D' . $row, $asset->merk ?? '-');
                    $sheet->setCellValue('E' . $row, ucfirst($asset->last_condition ?? 'Aktif'));
                    $sheet->setCellValue('G' . $row, $umurManfaat);
                    $sheet->setCellValue('H' . $row, $tglPerolehan);
                    $sheet->setCellValue('I' . $row, $qtyUnit);
                    $sheet->setCellValue('J' . $row, $hargaSatuan);

                    // Rumus Excel Asli
                    $sheet->setCellValue('K' . $row, "=I{$row}*J{$row}");
                    $sheet->setCellValue('L' . $row, "=1/G{$row}");
                    $sheet->setCellValue('M' . $row, "=(YEAR(\$A\$4)-YEAR(H{$row}))*12+MONTH(\$A\$4)-MONTH(H{$row})");
                    $sheet->setCellValue('N' . $row, "=G{$row}*12");
                    $sheet->setCellValue('O' . $row, "=IF(M{$row}<=0,0,IF(M{$row}=N{$row},(K{$row}*L{$row}/12)-1,IF(M{$row}>N{$row},0,K{$row}*L{$row}/12)))");
                    $sheet->setCellValue('P' . $row, "=IF(M{$row}<=0,0,IF(M{$row}=N{$row},(M{$row}*(K{$row}*L{$row}/12))-1,IF(M{$row}>N{$row},(N{$row}*(K{$row}*L{$row}/12))-1,M{$row}*(K{$row}*L{$row}/12))))");
                    $sheet->setCellValue('Q' . $row, "=K{$row}-P{$row}");

                    // Alignment
                    $sheet->getStyle("A{$row}:E{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("G{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("M{$row}:N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("J{$row}:L{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("O{$row}:Q{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                    // Number Formats
                    $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode('0%');
                    $sheet->getStyle("O{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("P{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("Q{$row}")->getNumberFormat()->setFormatCode('#,##0');

                    $row++;
                }

                $lastRow = max(8, $row - 1);
                $totalRow = $lastRow + 1;

                // 5. Baris Total Penyusutan (Dalam Rp)
                $sheet->getRowDimension($totalRow)->setRowHeight(28);
                $sheet->mergeCells("A{$totalRow}:J{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", "Total Penyusutan (Dalam Rp)");
                $sheet->getStyle("A{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Kolom K: Jumlah Nilai Perolehan
                $sheet->setCellValue("K{$totalRow}", "=SUM(K8:K{$lastRow})");
                $sheet->getStyle("K{$totalRow}")->getNumberFormat()->setFormatCode('"Rp  "#,##0');
                $sheet->getStyle("K{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("K{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

                // Kolom O: Penyusutan Per Bulan (Font Merah)
                $sheet->setCellValue("O{$totalRow}", "=SUM(O8:O{$lastRow})");
                $sheet->getStyle("O{$totalRow}")->getNumberFormat()->setFormatCode('"Rp  "#,##0');
                $sheet->getStyle("O{$totalRow}")->getFont()->setBold(true)->getColor()->setRGB('CC0000');
                $sheet->getStyle("O{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

                // Kolom P: Akumulasi Penyusutan (Font Merah, Fill Abu-abu)
                $sheet->setCellValue("P{$totalRow}", "=SUM(P8:P{$lastRow})");
                $sheet->getStyle("P{$totalRow}")->getNumberFormat()->setFormatCode('"Rp  "#,##0');
                $sheet->getStyle("P{$totalRow}")->getFont()->setBold(true)->getColor()->setRGB('CC0000');
                $sheet->getStyle("P{$totalRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Kolom Q: Nilai Buku (Font Merah, Fill Kuning Terang)
                $sheet->setCellValue("Q{$totalRow}", "=SUM(Q8:Q{$lastRow})");
                $sheet->getStyle("Q{$totalRow}")->getNumberFormat()->setFormatCode('"Rp  "#,##0');
                $sheet->getStyle("Q{$totalRow}")->getFont()->setBold(true)->getColor()->setRGB('CC0000');
                $sheet->getStyle("Q{$totalRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Border tabel termasuk baris total
                $sheet->getStyle("A7:Q{$totalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Lebar kolom Picture
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(3);
            },
        ];
    }
}
