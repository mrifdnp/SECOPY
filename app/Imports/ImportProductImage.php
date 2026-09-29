<?php
namespace App\Imports;

use App\Models\FailedImportData;
use App\Models\ImportStatus;
use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class ImportProductImage implements WithHeadingRow, OnEachRow, WithChunkReading, WithEvents
{
    use Importable;

    protected string $importStatusId;
    protected int $successCount = 0;
    protected int $failedCount = 0;

    public function __construct(string $importStatusId)
    {
        $this->importStatusId = $importStatusId;
    }

    public function onRow(Row $row)
    {
        $rowArray = $row->toArray();

        // Skip jika row kosong
        if (empty(array_filter($rowArray))) {
            return;
        }

        $productId = $rowArray['product_id'] ?? null;
        if (!$productId) {
            $this->logFailure($rowArray, 'product_id', 'Product ID kosong');
            return;
        }

        $product = Product::find($productId);
        if (!$product) {
            $this->logFailure($rowArray, 'product_id', 'Product tidak ditemukan');
            return;
        }

        $existingImages = collect($product->content_paths ?? []);
        $hasUpdate = false;

        $imageFields = ['main_pic', 'hover_pic', 'pic_1', 'pic_2', 'pic_3', 'pic_4', 'pic_5', 'pic_6', 'pic_7', 'pic_8'];

        foreach ($imageFields as $fieldName) {
            $imageFileName = $rowArray[$fieldName] ?? '';
            
            if (!empty(trim($imageFileName))) {
                $existingIndex = null;
                foreach ($existingImages as $i => $existing) {
                    if (is_array($existing) && isset($existing['name']) && $existing['name'] === $fieldName) {
                        $existingIndex = $i;
                        break;
                    }
                }

                $newImage = [
                    'name' => $fieldName,
                    'path' => 'images/' . trim($imageFileName)
                ];

                if ($existingIndex !== null) {
                    $existingImages[$existingIndex] = $newImage;
                } else {
                    $existingImages->push($newImage);
                }
                $hasUpdate = true;
            }
        }

        if ($hasUpdate) {
            $product->content_paths = $existingImages->values()->toArray();
            $product->save();
            $this->successCount++;
        }
    }

    public function chunkSize(): int
    {
        return 300;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
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

    private function logFailure(array $rowArray, string $column, string $mistake)
    {
        $this->failedCount++;
        $formatted = [
            [
                'column'    => $column,
                'value'     => $rowArray[$column] ?? '',
                'mistake'   => $mistake,
                'old_value' => '',
            ]
        ];

        FailedImportData::create([
            'id'               => Str::uuid(),
            'import_status_id' => $this->importStatusId,
            'row'              => json_encode($formatted),
            'replace_action'   => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}
