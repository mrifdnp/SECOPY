<?php
namespace App\Exports;

use App\Models\FailedImportData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportFailedImportData implements FromCollection, WithHeadings, WithMapping
{
    protected $importStatusId;
    protected $dynamicColumns;

    public function __construct($importStatusId = null)
    {
        $this->importStatusId = $importStatusId;
        $this->dynamicColumns = $this->getDynamicColumns();
    }

    /**
     * Ambil data yang akan diekspor
     */
    public function collection()
    {
        $query = FailedImportData::query();

        if ($this->importStatusId) {
            $query->where('import_status_id', $this->importStatusId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Dapatkan kolom dinamis dari semua data row
     */
    protected function getDynamicColumns()
    {
        $query = FailedImportData::query();

        if ($this->importStatusId) {
            $query->where('import_status_id', $this->importStatusId);
        }

        $allData = $query->get();
        $columns = [];

        foreach ($allData as $data) {
            $rowData = json_decode($data->row, true);

            if (is_array($rowData)) {
                foreach ($rowData as $item) {
                    if (isset($item['column'])) {
                        $columns[$item['column']] = true;
                    }
                }
            }
        }

        return array_keys($columns);
    }

    /**
     * Header kolom untuk CSV (hanya kolom dari row data)
     */
    public function headings(): array
    {
        // Kembalikan nama kolom apa adanya (snake_case)
        return $this->dynamicColumns;
    }

    /**
     * Mapping data untuk setiap baris (hanya value dari row)
     */
    public function map($failedImportData): array
    {
        // Decode JSON row data
        $rowData = json_decode($failedImportData->row, true);

        // Buat array untuk menyimpan nilai
        $values = [];

        // Parse data dari JSON
        if (is_array($rowData)) {
            foreach ($rowData as $item) {
                if (isset($item['column'])) {
                    $column          = $item['column'];
                    $values[$column] = $item['value'] ?? '';
                }
            }
        }

        // Return hanya nilai untuk setiap kolom dinamis
        $result = [];
        foreach ($this->dynamicColumns as $column) {
            $result[] = $values[$column] ?? '';
        }

        return $result;
    }
}
