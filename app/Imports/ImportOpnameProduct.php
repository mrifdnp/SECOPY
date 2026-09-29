<?php
namespace App\Imports;

use App\Models\FailedImportData;
use App\Models\ImportStatus;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductStockMovement;
use App\Models\Store;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\Rule;

class ImportOpnameProduct implements WithHeadingRow, OnEachRow, WithValidation, WithChunkReading, SkipsOnFailure, WithEvents
{
    use Importable, SkipsFailures;

    // Cache untuk mapping dan lookup
    protected Collection $variants;
    protected Collection $productsIds;
    protected Collection $productDetailIds;
    protected Collection $existingProductSKUs;
    protected string $userId;
    protected string $stockOpnameId;

    // Batch data untuk insert
    protected $batchProductDetails = [];
    protected $batchVariantUpdates = [];

    // Statistik impor
    protected int $successCount      = 0;
    protected int $failedCount       = 0;
    protected int $currentBatchCount = 0;

    // Constructor parameter
    protected string $storeId;
    protected string $importStatusId;

    public function __construct(string $storeId, string $importStatusId, ?string $userId ,?string $stockOpnameId)
    {
        $this->storeId        = $storeId;
        $this->importStatusId = $importStatusId;
        $this->userId = $userId;
        $this->stockOpnameId  = $stockOpnameId;
    }

    /**
     * Load semua data yang diperlukan untuk mapping dan validasi
     */
    protected function loadReferenceData(): void
    {
        // Ambil semua ProductDetail yang terkait, sekaligus ambil relasi product-nya
        $productDetails = ProductDetail::with('product')
            ->where('store_id', $this->storeId)
            ->get(['id', 'sku', 'product_id']);

        // Ambil product_id parent dari product detail dan distinct
        $this->productsIds = $productDetails->pluck('product_id')->unique();

        // Ambil SKU yang sudah ada, remove duplikat dan null
        $this->existingProductSKUs = $productDetails
            ->filter(fn($item) => ! is_null($item->sku) && $item->sku !== '')
            ->unique('sku')
            ->keyBy('sku');

        // Ambil semua id product detail
        $this->productDetailIds = $productDetails->pluck('id');

        // Ambil data variant
        // Ambil semua variant yang terkait dengan product_detail yang store_id-nya sama dengan $this->storeId
        $this->variants = Variant::whereHas('product_detail', function ($query) {
            $query->where('store_id', $this->storeId);
        })->get(['id', 'product_id', 'name']);
    }

    public function onRow(Row $row)
    {

        $product = ProductDetail::where('id', $row['id'])
        ->where('store_id', $this->storeId)
        ->first();

        if (! $product) {
            $this->failedCount++;
            Log::warning('Skip row karena product_detail tidak ditemukan', [
                'row' => $row->getIndex(),
                'id'  => $row['id'],
            ]);
            return;
        }
        
        $rowArray = $row->toArray();
        Log::info('Processing Row', ['row' => $row->getIndex(), 'data' => $rowArray]);

        // Skip jika row kosong
        if (empty(array_filter($rowArray))) {
            Log::warning('Skipped empty row', ['row' => $row->getIndex()]);
            return;
        }

        $opnameQty       = $rowArray['opname_qty'] ?? null;

        if ($opnameQty === null || $opnameQty === '') {
            return; // skip
        }

        $previousStock  = $product->qty;
        $rawStock = $row['opname_qty'] ?? null;
        $rawStock = is_string($rawStock) ? trim($rawStock) : $rawStock;

        if ($rawStock === null || $rawStock === '' || !is_numeric($rawStock)) {
            return;
        }

        $newStock = (int) $rawStock;

        if ($newStock === $product->qty) {
            return;
        }

        $userEmail = User::find($this->userId)->email ?? null;
        $opnameDate = $row['opname_date'];
        $rowDate = trim($row['opname_date'], "\"'");
        $dateObj = \DateTime::createFromFormat('d/m/Y H:i:s', $opnameDate);
        $productId = $row['id'];

        if (!$dateObj) {
            $dateObj = \DateTime::createFromFormat('d/m/Y H:i', $opnameDate);

            if (!$dateObj) {
                // Kalau gagal lagi, coba hanya tanggal
                $dateObj = \DateTime::createFromFormat('d/m/Y', $opnameDate);
                if ($dateObj) $dateObj->setTime(0,0,0);
                else $dateObj = now(); // fallback
            }
        }

        $prevMovement = ProductStockMovement::where('product_detail_id', $productId)
                    ->where('created_at', '<=', $rowDate)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->first();

        $lastStockPrev = $prevMovement ? $prevMovement->last_stock : 0;

        $nextMovement = ProductStockMovement::where('product_detail_id', $productId)
                    ->where('created_at', '>', $rowDate)
                    ->orderBy('created_at', 'asc')
                    ->get();

        $lastStock = $newStock;

        foreach ($nextMovement as $movement) {
            $lastStock = $lastStock + $movement->qty;
            $movement->last_stock = $lastStock;
            $movement->save();
        }

        $product->update(['qty' => $lastStock]);

        if($prevMovement){
            // logic diff kalau data input di tengah record
            $diffMidRecord = $newStock - $lastStockPrev;
            
            ProductStockMovement::create([
                'product_detail_id'   => $row['id'],
                'last_stock'          => $newStock,
                'qty'                 => $diffMidRecord,
                'remark'              => "Stock Opname-" . now()->format('ymd'),
                'stockopname_id'      => $this->stockOpnameId,
                'created_by'          => $userEmail,
                'created_at'          => $rowDate,
            ]);
        }else{
            // logic diff kalau data input di last record
            $diffLastRecord           = $newStock - $previousStock;
            ProductStockMovement::create([
                'product_detail_id'   => $row['id'],
                'last_stock'          => $newStock,
                'qty'                 => $diffLastRecord,
                'remark'              => "Stock Opname-" . now()->format('ymd'),
                'stockopname_id'      => $this->stockOpnameId,
                'created_by'          => $userEmail,
                'created_at'          => $rowDate,
            ]);
        }

        $this->successCount++;
    }


    public function rules(): array
    {
        return [
            'id'        => ['required', 'uuid', Rule::in($this->productDetailIds->toArray())],
            'opname_qty' => 'nullable|numeric|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'id.required'        => 'Kolom ID Produk wajib diisi.',
            'id.uuid'            => 'Kolom ID Produk harus berupa UUID yang valid.',
            'id.in'              => 'Produk dengan ID tersebut tidak ditemukan di toko ini.',

            'opname_qty.integer'              => 'Kolom Opname Qty harus berupa bilangan bulat.',
            'opname_qty.min'                  => 'Kolom Opname Qty tidak boleh kurang dari 0.',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        // Group failures berdasarkan row number
        $failuresByRow = [];

        foreach ($failures as $failure) {
            $rowNumber = $failure->row();

            if (! isset($failuresByRow[$rowNumber])) {
                $failuresByRow[$rowNumber] = [
                    'values' => $failure->values(),
                    'errors' => [],
                ];
            }

            // Kumpulkan error berdasarkan attribute
            $attribute = $failure->attribute();
            $messages  = $failure->errors();

            if (! isset($failuresByRow[$rowNumber]['errors'][$attribute])) {
                $failuresByRow[$rowNumber]['errors'][$attribute] = [];
            }

            $failuresByRow[$rowNumber]['errors'][$attribute] = array_merge(
                $failuresByRow[$rowNumber]['errors'][$attribute],
                $messages
            );
        }

        // Batch insert failed data
        $failedDataBatch = [];
        foreach ($failuresByRow as $rowNumber => $rowData) {
            $this->failedCount++;
            $formatted = [];

            foreach ($rowData['values'] as $column => $value) {
                $mistake = '';

                // Cari error untuk kolom ini
                if (isset($rowData['errors'][$column])) {
                    $mistake = implode(', ', array_unique($rowData['errors'][$column]));
                }

                $formatted[] = [
                    'column'    => $column,
                    'value'     => $value,
                    'mistake'   => $mistake,
                    'old_value' => '',
                ];
            }

            $failedDataBatch[] = [
                'id'               => Str::uuid(),
                'import_status_id' => $this->importStatusId,
                'row'              => json_encode($formatted),
                'replace_action'   => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        // Batch insert failed import data
        if (! empty($failedDataBatch)) {
            FailedImportData::insert($failedDataBatch);
        }
    }

    public function chunkSize(): int
    {
        return 300;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                // Load reference data sebelum import dimulai
                $this->loadReferenceData();
            },
            AfterImport::class  => function (AfterImport $event) {
                // Insert sisa batch data jika ada
                // $this->insertBatch();

                // Update final status to done with all counts
                $this->updateImportStatus('done', [
                    'success_row' => $this->successCount,
                    'failed_row'  => $this->failedCount,
                    'total_row'   => $this->successCount + $this->failedCount,
                ]);
            },
        ];
    }

    private function updateImportStatus(string $status, array $additionalData = [])
    {
        $updateData = array_merge(['status' => $status], $additionalData);
        ImportStatus::where('id', $this->importStatusId)->update($updateData);
    }

    private function cleanupVariantWithoutDescriptionAndDuplicateId(): array
    {
        $newBatchVariant    = collect([]);
        $allHaveDescription = true; // default true

        foreach ($this->batchVariantUpdates as $variant) {
            // Hanya masukkan jika id belum ada
            if (! $newBatchVariant->contains('id', $variant['id'])) {
                // Decode JSON
                $descriptions = json_decode($variant['json_description'], true) ?? [];

                // Filter deskripsi kosong
                $filtered = array_filter($descriptions, fn($d) => ! empty(trim($d['description'] ?? '')));

                // Kalau kosong semua → set null dan flag false
                if (empty($filtered)) {
                    $variant['json_description'] = null;
                    $allHaveDescription          = false;
                } else {
                    $variant['json_description'] = json_encode(array_values($filtered));
                }

                // Push variant dengan id unik
                $newBatchVariant->push($variant);
            }
        }

        return [
            'needToUpdateJsonDescription' => $allHaveDescription,
            'updatedVariantData'          => $newBatchVariant->toArray(),
        ];
    }

}
