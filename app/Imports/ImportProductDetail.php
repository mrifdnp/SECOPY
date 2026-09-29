<?php
namespace App\Imports;

use App\Models\FailedImportData;
use App\Models\ImportStatus;
use App\Models\ProductDetail;
use App\Models\Variant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

class ImportProductDetail implements WithHeadingRow, OnEachRow, WithValidation, WithChunkReading, SkipsOnFailure, WithEvents
{
    use Importable, SkipsFailures;

    // Cache untuk mapping dan lookup
    protected Collection $variants;
    protected Collection $productsIds;
    protected Collection $productDetailIds;
    protected Collection $existingProductSKUs;
    protected Collection $productDetailsData;
    protected Collection $stores;

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

    public function __construct(string $storeId, string $importStatusId)
    {
        $this->storeId        = $storeId;
        $this->importStatusId = $importStatusId;
    }

    /**
     * Load semua data yang diperlukan untuk mapping dan validasi
     */
    protected function loadReferenceData(): void
    {
        if ($this->storeId === 'all') {
            $productDetails = ProductDetail::with('product')->get(['id', 'sku', 'product_id', 'store_id', 'variant_id', 'color_id']);
            $this->productDetailsData = $productDetails->keyBy('id');
            
            $this->productsIds = $productDetails->pluck('product_id')->unique();
            
            $this->existingProductSKUs = $productDetails
                ->filter(fn($item) => ! is_null($item->sku) && $item->sku !== '')
                ->groupBy('sku');
                
            $this->productDetailIds = $productDetails->pluck('id');
            $this->variants = Variant::all(['id', 'product_id', 'name']);
            $this->stores = \App\Models\Store::whereNull('deleted_at')->get();
        } else {
            $productDetails = ProductDetail::with('product')
                ->where('store_id', $this->storeId)
                ->get(['id', 'sku', 'product_id']);

            $this->productsIds = $productDetails->pluck('product_id')->unique();

            $this->existingProductSKUs = $productDetails
                ->filter(fn($item) => ! is_null($item->sku) && $item->sku !== '')
                ->unique('sku')
                ->keyBy('sku');

            $this->productDetailIds = $productDetails->pluck('id');

            $this->variants = Variant::whereHas('product_detail', function ($query) {
                $query->where('store_id', $this->storeId);
            })->get(['id', 'product_id', 'name']);
        }
    }

    public function onRow(Row $row)
    {
        $rowArray = $row->toArray();

        // Skip jika row kosong
        if (empty(array_filter($rowArray))) {
            return;
        }

        // Cari variant_id berdasarkan nama variant dan product_id
        $variant = $this->variants->first(function ($v) use ($rowArray) {
            // Disini memakai id_produk bukan id_produk_detail
            // Karena varian itu terkait ke product, bukan ke product detail
            return strtolower($v->name) === strtolower($rowArray['varian']) && $v->product_id === $rowArray['id_produk'];
        });

        // Case ini terjadi jika user memilih store yang berbeda waktu mendownload template dan pada saat import
        // Misal user mendownload template untuk store A, lalu pada saat import memilih store B
        $variantId = $variant->id ?? null;
        if (! $variantId) {
            return; // Skip jika variant tidak ditemukan
        }

        if ($this->storeId === 'all') {
            $sourceDetail = $this->productDetailsData->get($rowArray['id_produk_detail']);
            if (!$sourceDetail) {
                return;
            }

            $targetDetails = $this->productDetailsData->filter(function($item) use ($sourceDetail) {
                return $item->product_id === $sourceDetail->product_id && 
                       $item->variant_id === $sourceDetail->variant_id && 
                       $item->color_id === $sourceDetail->color_id;
            });

            foreach ($targetDetails as $target) {
                $storeName = $this->stores->firstWhere('id', $target->store_id)->name ?? '';
                $skuCol = \Illuminate\Support\Str::slug('sku ' . $storeName, '_');
                $priceCol = \Illuminate\Support\Str::slug('harga ' . $storeName, '_');
                $additionalPriceCol = \Illuminate\Support\Str::slug('harga tambahan ' . $storeName, '_');
                $cogmCol = \Illuminate\Support\Str::slug('cogm ' . $storeName, '_');
                $qtyCol = \Illuminate\Support\Str::slug('stok ' . $storeName, '_');
                $weightCol = \Illuminate\Support\Str::slug('berat ' . $storeName, '_');

                $this->batchProductDetails[] = [
                    'id'               => $target->id,
                    'product_id'       => $rowArray['id_produk'], // parent product
                    'store_id'         => $target->store_id,
                    'sku'              => $rowArray[$skuCol] ?? null,
                    'price'            => $rowArray[$priceCol] ?? 0,
                    'additional_price' => $rowArray[$additionalPriceCol] ?? 0,
                    'cogm'             => $rowArray[$cogmCol] ?? 0,
                    'qty'              => $rowArray[$qtyCol] ?? 0,
                    'weight'           => $rowArray[$weightCol] ?? 0,
                    'updated_at'       => now(),
                ];
                $this->currentBatchCount++;
            }
        } else {
            $productDetail = [
                'id'               => $rowArray['id_produk_detail'],
                'product_id'       => $rowArray['id_produk'], // parent product
                'store_id'         => $this->storeId,
                'sku'              => $rowArray['sku'],
                'price'            => $rowArray['harga'] ?? 0,
                'additional_price' => $rowArray['harga_tambahan'] ?? 0,
                'cogm'             => $rowArray['cogm'] ?? 0,
                'qty'              => $rowArray['stok'] ?? 0,
                'weight'           => $rowArray['berat'] ?? 0,
                'updated_at'       => now(),
            ];
            $this->batchProductDetails[] = $productDetail;
            $this->currentBatchCount++;
        }

        // Buat deskripsi untuk masing masing varian
        $variantDescriptions = [
            [
                'title'       => 'Deskripsi Produk',
                'description' => $rowArray['deskripsi_produk'] ?? '',
            ],
            [
                'title'       => 'Dimensi Produk',
                'description' => $rowArray['dimensi_produk'] ?? '',
            ],
        ];

        $variants = [
            'id'               => $variantId,
            'product_id'       => $rowArray['id_produk'],
            'name'             => $rowArray['varian'],
            'json_description' => json_encode($variantDescriptions),
            'updated_at'       => now(),
        ];

        // Tambah ke batch insert (detail produk sudah ditambahkan di atas)
        $this->batchVariantUpdates[] = $variants;

        if ($this->currentBatchCount >= 300) {
            $this->insertBatch();
        }
    }

    /**
     * Insert batch data ke database
     */
    protected function insertBatch()
    {
        if (empty($this->batchProductDetails)) {
            return;
        }

        try {
            [
                'needToUpdateJsonDescription' => $needToUpdateJsonDescription,
                'updatedVariantData'          => $this->batchVariantUpdates,
            ] = $this->cleanupVariantWithoutDescriptionAndDuplicateId();

            DB::transaction(function () use ($needToUpdateJsonDescription) {
                ProductDetail::upsert($this->batchProductDetails, ['id'], ['sku', 'price', 'additional_price', 'cogm', 'qty', 'weight', 'updated_at']);

                if ($needToUpdateJsonDescription && ! empty($this->batchVariantUpdates)) {
                    Variant::upsert($this->batchVariantUpdates, ['id'], ['json_description', 'updated_at']);
                }
            });

            $this->successCount += count($this->batchProductDetails);
        } catch (\Exception $e) {
            throw new \Exception("Batch insert failed: " . $e->getMessage());
        }

        // Reset batch
        $this->batchProductDetails = [];
        $this->batchVariantUpdates = [];
        $this->currentBatchCount   = 0;
    }

    public function rules(): array
    {
        $rules = [
            'id_produk'        => ['required', 'uuid'],
            'id_produk_detail' => ['required', 'uuid'],
            'varian'           => ['required', 'string'],
            'deskripsi_produk' => 'nullable|string',
            'dimensi_produk'   => 'nullable|string',
        ];

        if ($this->storeId === 'all') {
            $stores = \App\Models\Store::whereNull('deleted_at')->get();
            foreach ($stores as $store) {
                $skuCol = \Illuminate\Support\Str::slug('sku ' . $store->name, '_');
                $priceCol = \Illuminate\Support\Str::slug('harga ' . $store->name, '_');
                $additionalPriceCol = \Illuminate\Support\Str::slug('harga tambahan ' . $store->name, '_');
                $qtyCol = \Illuminate\Support\Str::slug('stok ' . $store->name, '_');
                $weightCol = \Illuminate\Support\Str::slug('berat ' . $store->name, '_');

                $rules[$skuCol] = 'nullable';
                $rules[$priceCol] = 'nullable|numeric|min:0';
                $rules[$additionalPriceCol] = 'nullable|numeric|min:0';
                $rules[$qtyCol] = 'nullable|integer';
                $rules[$weightCol] = 'nullable|numeric|min:0';
            }
        } else {
            $rules['sku'] = 'nullable';
            $rules['harga'] = 'nullable|numeric|min:0';
            $rules['harga_tambahan'] = 'nullable|numeric|min:0';
            $rules['stok'] = 'nullable|integer';
            $rules['berat'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    public function customValidationMessages()
    {
        $messages = [
            'id_produk.required'        => 'Kolom ID Produk wajib diisi.',
            'id_produk.uuid'            => 'Kolom ID Produk harus berupa UUID yang valid.',
            'id_produk.in'              => 'Produk dengan ID tersebut tidak ditemukan di toko ini.',

            'id_produk_detail.required' => 'Kolom ID Produk Detail wajib diisi.',
            'id_produk_detail.uuid'     => 'Kolom ID Produk Detail harus berupa UUID yang valid.',
            'id_produk_detail.in'       => 'Produk Detail dengan ID tersebut tidak ditemukan di toko ini.',

            'varian.required'           => 'Kolom Varian wajib diisi.',
            'varian.in'                 => 'Varian yang dimasukkan tidak valid untuk produk ini, harus salah satu dari: ' . $this->variants->pluck('name')->unique()->implode(', '),

            'harga.numeric'             => 'Kolom Harga harus berupa angka.',
            'harga.min'                 => 'Kolom Harga tidak boleh kurang dari 0.',

            'stok.integer'              => 'Kolom Stok harus berupa bilangan bulat.',

            'berat.numeric'             => 'Kolom Berat harus berupa angka.',
            'berat.min'                 => 'Kolom Berat tidak boleh kurang dari 0.',

            'deskripsi_produk.string'   => 'Kolom Deskripsi Produk harus berupa teks.',
            'dimensi_produk.string'     => 'Kolom Dimensi Produk harus berupa teks.',
        ];

        if ($this->storeId === 'all') {
            $stores = \App\Models\Store::whereNull('deleted_at')->get();
            foreach ($stores as $store) {
                $priceCol = \Illuminate\Support\Str::slug('harga ' . $store->name, '_');
                $additionalPriceCol = \Illuminate\Support\Str::slug('harga tambahan ' . $store->name, '_');
                $qtyCol = \Illuminate\Support\Str::slug('stok ' . $store->name, '_');
                $weightCol = \Illuminate\Support\Str::slug('berat ' . $store->name, '_');

                $messages["{$priceCol}.numeric"] = "Kolom Harga {$store->name} harus berupa angka.";
                $messages["{$priceCol}.min"] = "Kolom Harga {$store->name} tidak boleh kurang dari 0.";

                $messages["{$additionalPriceCol}.numeric"] = "Kolom Harga Tambahan {$store->name} harus berupa angka.";
                $messages["{$additionalPriceCol}.min"] = "Kolom Harga Tambahan {$store->name} tidak boleh kurang dari 0.";

                $messages["{$qtyCol}.integer"] = "Kolom Stok {$store->name} harus berupa bilangan bulat.";

                $messages["{$weightCol}.numeric"] = "Kolom Berat {$store->name} harus berupa angka.";
                $messages["{$weightCol}.min"] = "Kolom Berat {$store->name} tidak boleh kurang dari 0.";
            }
        } else {
            $messages['harga_tambahan.numeric'] = 'Kolom Harga Tambahan harus berupa angka.';
            $messages['harga_tambahan.min'] = 'Kolom Harga Tambahan tidak boleh kurang dari 0.';
        }

        return $messages;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();

            foreach ($data as $index => $row) {
                // Validasi id_produk ada di sistem
                if (!empty($row['id_produk']) && !$this->productsIds->contains($row['id_produk'])) {
                    $validator->errors()->add("{$index}.id_produk", "Produk dengan ID tersebut tidak ditemukan.");
                }

                // Validasi id_produk_detail ada di sistem
                if (!empty($row['id_produk_detail']) && !$this->productDetailIds->contains($row['id_produk_detail'])) {
                    $validator->errors()->add("{$index}.id_produk_detail", "Produk Detail dengan ID tersebut tidak ditemukan.");
                }

                // Validasi varian ada di sistem
                $validVariants = $this->variants->pluck('name')->unique();
                if (!empty($row['varian']) && !$validVariants->contains($row['varian'])) {
                    $validator->errors()->add("{$index}.varian", "Varian yang dimasukkan tidak valid.");
                }

                // Cek apakah SKU sudah ada di product detail lain
                // Abaikan jika id produk sama (update)
                if ($this->storeId === 'all') {
                    foreach ($this->stores as $store) {
                        $skuCol = \Illuminate\Support\Str::slug('sku ' . $store->name, '_');
                        if (! empty($row[$skuCol])) {
                            $existingGroup = $this->existingProductSKUs->get($row[$skuCol]);
                            if ($existingGroup) {
                                $sourceDetail = $this->productDetailsData->get($row['id_produk_detail']);
                                if ($sourceDetail) {
                                    $isDuplicate = $existingGroup->contains(function($item) use ($sourceDetail, $store) {
                                        return ($item->product_id !== $sourceDetail->product_id ||
                                               $item->variant_id !== $sourceDetail->variant_id ||
                                               $item->color_id !== $sourceDetail->color_id) && $item->store_id === $store->id;
                                    });
                                    if ($isDuplicate) {
                                        $validator->errors()->add("{$index}.{$skuCol}", "SKU '" . $row[$skuCol] . "' sudah digunakan oleh produk lain di toko ini.");
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if (! empty($row['sku'])) {
                        $existing = $this->existingProductSKUs->get($row['sku']);
                        if ($existing && $existing->id != $row['id_produk_detail']) {
                            $validator->errors()->add("{$index}.sku", "SKU '" . $row['sku'] . "' sudah digunakan oleh produk lain di toko ini.");
                        }
                    }
                }
            }
        });
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
                $this->insertBatch();

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

        foreach ($this->batchVariantUpdates as $variant) {
            // Hanya masukkan jika id belum ada
            if (! $newBatchVariant->contains('id', $variant['id'])) {
                // Decode JSON
                $descriptions = json_decode($variant['json_description'], true) ?? [];

                // Filter deskripsi kosong
                $filtered = array_filter($descriptions, fn($d) => ! empty(trim($d['description'] ?? '')));

                // Kalau tidak kosong, baru kita masukkan ke batch update
                if (! empty($filtered)) {
                    $variant['json_description'] = json_encode(array_values($filtered));
                    $newBatchVariant->push($variant);
                }
            }
        }

        return [
            'needToUpdateJsonDescription' => $newBatchVariant->isNotEmpty(),
            'updatedVariantData'          => $newBatchVariant->toArray(),
        ];
    }

}
