<?php
namespace App\Imports;

use App\Models\FailedImportData;
use App\Models\Category;
use App\Models\ImportStatus;
use App\Models\Material;
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

class ImportProduct implements WithHeadingRow, OnEachRow, WithValidation, WithChunkReading, SkipsOnFailure, WithEvents
{
    use Importable, SkipsFailures;

    // Cosntructor parameter
    protected string $importStatusId;

    // Cache untuk mapping dan lookup
    protected $categories  = [];
    protected $categoryMap = [];
    protected $materials   = [];
    protected $materialMap = [];
    protected $storeIds    = [];

    // Batch data untuk insert
    protected $batchProduct       = [];
    protected $batchProductDetail = [];
    protected $batchVariant       = [];
    protected $batchColor         = [];
    protected $batchSize          = 300;

    protected int $successCount      = 0;
    protected int $failedCount       = 0;
    protected int $currentBatchCount = 0;

    public function __construct(string $importStatusId)
    {
        $this->importStatusId = $importStatusId;
    }

    /**
     * Load semua data yang diperlukan untuk mapping dan validasi
     */
    protected function loadReferenceData()
    {
        // Load categories dengan mapping main + sub -> id
        $categories = Category::with('mainCategory:id,title')
            ->select('id', 'title', 'main_categories_id')
            ->get();

        $this->categories  = [];
        $this->categoryMap = [];

        foreach ($categories as $category) {
            $mainTitle = $category->mainCategory ? trim($category->mainCategory->title) : '';
            $subTitle  = trim($category->title);
            $composite = $mainTitle . ' > ' . $subTitle;

            $this->categories[] = $composite;
            $this->categoryMap[$composite] = $category->id;
        }

        $this->categories = array_values(array_unique($this->categories));

        // Load materials dengan mapping title -> id
        $materials = Material::whereNull('deleted_at')->get(['id', 'title']);
        $this->materials   = [];
        $this->materialMap = [];

        foreach ($materials as $material) {
            $title = trim($material->title);
            $this->materials[] = $title;
            $this->materialMap[$title] = $material->id;
        }

        // Load store, cukup id saja
        $this->storeIds = DB::table('stores')->pluck('id')->toArray();
    }

    public function onRow(Row $row)
    {
        $rowArray = $row->toArray();

        // Skip jika row kosong
        if (empty(array_filter($rowArray))) {
            return;
        }

        // Get category_id dari cache/mapping
        $mainCategoryTitle = $rowArray['main_kategori'] ?? '';
        $categoryTitle     = $rowArray['kategori'] ?? '';
        $compositeKey      = trim($mainCategoryTitle) . ' > ' . trim($categoryTitle);

        $categoryId = $this->categoryMap[$compositeKey] ?? null;

        $baseUrl = 'images/';
        $formatPic = function($value) use ($baseUrl) {
            $value = trim($value ?? '');
            if (empty($value)) return '';
            // Jika sudah ada tulisan images/, biarkan saja. Jika belum, tambahkan images/
            if (str_starts_with($value, 'images/')) {
                return $value;
            }
            // Jika ada link http/https, asumsikan itu link eksternal (meskipun sistem mungkin tidak mendownloadnya, kita biarkan saja)
            if (preg_match('/^https?:\/\//', $value)) {
                return $value;
            }
            return $baseUrl . ltrim($value, '/');
        };

        // Setup content path
        $contentPaths = [
            ['name' => 'main_pic', 'path' => $formatPic($rowArray['gambar_utama'] ?? '')],
            ['name' => 'hover_pic', 'path' => $formatPic($rowArray['gambar_hover'] ?? '')],
            ['name' => 'pic_1', 'path' => $formatPic($rowArray['gambar_1'] ?? '')],
            ['name' => 'pic_2', 'path' => $formatPic($rowArray['gambar_2'] ?? '')],
            ['name' => 'pic_3', 'path' => $formatPic($rowArray['gambar_3'] ?? '')],
            ['name' => 'pic_4', 'path' => $formatPic($rowArray['gambar_4'] ?? '')],
            ['name' => 'pic_5', 'path' => $formatPic($rowArray['gambar_5'] ?? '')],
            ['name' => 'pic_6', 'path' => $formatPic($rowArray['gambar_6'] ?? '')],
            ['name' => 'pic_7', 'path' => $formatPic($rowArray['gambar_7'] ?? '')],
            ['name' => 'pic_8', 'path' => $formatPic($rowArray['gambar_8'] ?? '')],
        ];

        $materialTitle = $rowArray['material'] ?? '';
        $materialId    = $materialTitle ? ($this->materialMap[trim($materialTitle)] ?? null) : null;

        // Siapkan data untuk batch insert
        $productData = [
            'id'                => Str::uuid(),
            'title'             => $rowArray['nama'],
            'category_id'       => $categoryId,
            'material_id'       => $materialId,
            'top_pick'          => isset($rowArray['top_pick']) && $rowArray['top_pick'] === 'Ya' ? true : false,
            'show_in_ecommerce' => isset($rowArray['tampil_di_ecommerce']) && $rowArray['tampil_di_ecommerce'] === 'Ya' ? true : false,
            'content_paths'     => json_encode($contentPaths),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        $variants = $this->createVariants($productData, $this->parseVariants($rowArray['varian'] ?? ''));
        $colors   = $this->createColors($productData, $this->parseColors($rowArray['hexa_warna'] ?? '', $rowArray['label_warna'] ?? ''));
        $details  = $this->createProductDetails($productData, $variants, $colors);

        // Tambahkan ke batch
        $this->batchProduct[]     = $productData;
        $this->batchVariant       = array_merge($this->batchVariant, $variants);
        $this->batchColor         = array_merge($this->batchColor, $colors);
        $this->batchProductDetail = array_merge($this->batchProductDetail, $details);
        $this->currentBatchCount++;

        // Jika batch sudah penuh, lakukan insert
        if ($this->currentBatchCount >= $this->batchSize) {
            $this->insertBatch();
        }
    }

    /**
     * Insert batch data ke database
     */
    protected function insertBatch()
    {
        if (empty($this->batchProduct)) {
            return;
        }

        try {
            DB::transaction(function () {
                DB::table('products')->insert($this->batchProduct);
                DB::table('variants')->insert($this->batchVariant);
                DB::table('colors')->insert($this->batchColor);
                DB::table('product_details')->insert($this->batchProductDetail);
            });

            $this->successCount += count($this->batchProduct);
        } catch (\Exception $e) {
            throw new \Exception('Batch insert failed: ' . $e->getMessage());
        }

        // Reset batch data
        $this->batchProduct       = [];
        $this->batchVariant       = [];
        $this->batchColor         = [];
        $this->batchProductDetail = [];
        $this->currentBatchCount  = 0;
    }

    public function rules(): array
    {
        return [
            'nama'                => 'required|string|max:255',
            'main_kategori'       => 'required|string',
            'kategori'            => 'required|string',
            'material'            => 'nullable|string',
            'tampil_di_ecommerce' => 'nullable|in:Ya,Tidak',
            'top_pick'            => 'nullable|in:Ya,Tidak',
            'varian'              => ['required', 'string', function ($attribute, $value, $fail) {
                $variants = array_filter(array_map('trim', explode(',', $value)));
                if (empty($variants)) {
                    $fail('Varian wajib diisi dengan minimal satu varian, dipisah dengan koma.');
                }
            }],
            'hexa_warna'   => ['required', 'string', function ($attribute, $value, $fail) {
                $hexArray = array_filter(array_map('trim', explode(',', $value)));
                foreach ($hexArray as $hex) {
                    if (! preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $hex)) {
                        $fail("Format warna hexa tidak valid: {$hex}. Contoh format yang benar: #FFFFFF, #000000");
                    }
                }
            }],
            'label_warna'  => ['required', 'string', function ($attribute, $value, $fail) {
                $label = array_filter(array_map('trim', explode(',', $value)));
                if (empty($label)) {
                    $fail('Label warna wajib diisi dengan minimal satu label, dipisah dengan koma.');
                }
            }],
            'gambar_utama' => 'nullable',
            'gambar_hover' => 'nullable',
            'gambar_1'     => 'nullable',
            'gambar_2'     => 'nullable',
            'gambar_3'     => 'nullable',
            'gambar_4'     => 'nullable',
            'gambar_5'     => 'nullable',
            'gambar_6'     => 'nullable',
            'gambar_7'     => 'nullable',
            'gambar_8'     => 'nullable',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            foreach ($data as $index => $row) {
                // Validate kategori
                $mainKategori = $row['main_kategori'] ?? '';
                $kategori     = $row['kategori'] ?? '';
                $compositeKey = trim($mainKategori) . ' > ' . trim($kategori);
                
                if (!isset($this->categoryMap[$compositeKey])) {
                    $validator->errors()->add("{$index}.kategori", "Kombinasi Main Kategori ({$mainKategori}) dan Sub Kategori ({$kategori}) tidak ditemukan di sistem.");
                }

                // Validate material
                $materialTitle = $row['material'] ?? '';
                if (!empty($materialTitle) && !isset($this->materialMap[trim($materialTitle)])) {
                    $validator->errors()->add("{$index}.material", "Material ({$materialTitle}) tidak ditemukan di sistem.");
                }

                // Cek jumlah hexa_warna dan label_warna harus sama
                $hexCount   = count(array_filter(array_map('trim', explode(',', $row['hexa_warna'] ?? ''))));
                $labelCount = count(array_filter(array_map('trim', explode(',', $row['label_warna'] ?? ''))));
                if ($hexCount !== $labelCount) {
                    $validator->errors()->add("{$index}.label_warna", "Jumlah label warna harus sesuai dengan jumlah hexa warna");
                }
            }
        });
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required'                => 'Nama wajib diisi',
            'nama.string'                  => 'Nama harus berupa teks',
            'nama.max'                     => 'Nama maksimal 255 karakter',

            'varian.required'              => 'Varian wajib diisi',
            'varian.string'                => 'Varian harus berupa teks',

            'hexa_warna.required'          => 'Hexa Warna wajib diisi',
            'hexa_warna.string'            => 'Hexa Warna harus berupa teks',
            'label_warna.required'         => 'Label Warna wajib diisi',
            'label_warna.string'           => 'Label Warna harus berupa teks',

            'main_kategori.required'       => 'Main Kategori wajib diisi',
            'main_kategori.string'         => 'Main Kategori harus berupa teks',
            'kategori.required'            => 'Kategori wajib diisi',
            'kategori.string'              => 'Kategori harus berupa teks',

            'tampil_di_ecommerce.in'       => 'Tampil di E-commerce harus diisi dengan Ya atau Tidak',
            'top_pick.in'                  => 'Top Pick harus diisi dengan Ya atau Tidak',
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

    private function parseVariants(string $input): array
    {
        $variants = array_filter(array_map('trim', explode(',', $input)));
        return array_values(array_unique($variants));
    }

    private function parseColors(string $hex, string $label): array
    {
        $hexArray   = explode(',', $hex);
        $labelArray = explode(',', $label);

        $result = [];
        foreach ($hexArray as $index => $hexValue) {
            $result[] = [
                'hexa'  => trim($hexValue),
                'label' => $labelArray[$index] ? trim($labelArray[$index]) : '',
            ];
        }

        return $result;
    }

    private function createVariants(array $product, $variants): array
    {
        $result = [];
        foreach ($variants as $variant) {
            $result[] = [
                'id'         => Str::uuid(),
                'product_id' => $product['id'],
                'name'       => $variant,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $result;
    }

    private function createColors(array $product, $colors): array
    {
        $result = [];
        foreach ($colors as $color) {
            $result[] = [
                'id'         => Str::uuid(),
                'product_id' => $product['id'],
                'label'      => $color['label'],
                'hexa'       => $color['hexa'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $result;
    }

    private function createProductDetails(array $product, array $variants, array $colors): array
    {
        $result = [];
        foreach ($this->storeIds as $storeId) {
            foreach ($variants as $variant) {
                foreach ($colors as $color) {
                    $result[] = [
                        'id'         => Str::uuid(),
                        'store_id'   => $storeId,
                        'product_id' => $product['id'],
                        'variant_id' => $variant['id'],
                        'color_id'   => $color['id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return $result;
    }

    private function updateImportStatus(string $status, array $additionalData = [])
    {
        $updateData = array_merge(['status' => $status], $additionalData);
        ImportStatus::where('id', $this->importStatusId)->update($updateData);
    }
}
