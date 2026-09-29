<?php
namespace App\Exports;

use App\Models\ProductDetail;
use App\Models\Store;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ProductDetailExport implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings
{
    protected $storeId;
    protected $stores;
    protected $productIds;
    protected $mainCategoryId;
    protected $categoryId;
    protected $materialId;
    protected $emptySkuOnly;
    protected $emptyCogmOnly;

    public function __construct(string $storeId, array $productIds = [], $mainCategoryId = null, $categoryId = null, $materialId = null, $emptySkuOnly = false, $emptyCogmOnly = false)
    {
        $this->storeId = $storeId;
        $this->productIds = $productIds;
        $this->mainCategoryId = $mainCategoryId;
        $this->categoryId = $categoryId;
        $this->materialId = $materialId;
        $this->emptySkuOnly = $emptySkuOnly;
        $this->emptyCogmOnly = $emptyCogmOnly;
        if ($this->storeId === 'all') {
            $this->stores = Store::whereNull('deleted_at')->get();
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = ProductDetail::with(['productWithoutScope', 'variant', 'color']);

        if (!empty($this->productIds)) {
            $query->whereIn('product_id', $this->productIds);
        }

        if (!empty($this->mainCategoryId) || !empty($this->categoryId) || !empty($this->materialId)) {
            $query->whereHas('productWithoutScope', function($q) {
                if (!empty($this->mainCategoryId)) {
                    $q->whereHas('categories', function($qCat) {
                        $qCat->where('main_categories_id', $this->mainCategoryId);
                    });
                }
                if (!empty($this->categoryId)) {
                    $q->where('category_id', $this->categoryId);
                }
                if (!empty($this->materialId)) {
                    $q->where('material_id', $this->materialId);
                }
            });
        }

        if ($this->emptySkuOnly) {
            $query->where(function($q) {
                $q->whereNull('sku')->orWhere('sku', '');
            });
        }

        if ($this->emptyCogmOnly) {
            $query->where(function($q) {
                $q->whereNull('cogm')->orWhere('cogm', 0);
            });
        }

        if ($this->storeId === 'all') {
            $firstStoreId = Store::whereNull('deleted_at')->first()->id ?? null;
            if (!$firstStoreId) return collect([]);
            return $query->where('store_id', $firstStoreId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return $query->where('store_id', $this->storeId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            'id_produk',
            'id_produk_detail',
            'nama',
            'varian',
            'warna',
        ];

        $isSuper = session()->get('current_role') && in_array(session()->get('current_role')->name, ['super', 'admin']);
        
        if ($this->storeId === 'all') {
            foreach ($this->stores as $store) {
                $headings[] = 'sku ' . $store->name;
                $headings[] = 'harga ' . $store->name;
                $headings[] = 'harga tambahan ' . $store->name;
                if ($isSuper) $headings[] = 'cogm ' . $store->name;
                $headings[] = 'stok ' . $store->name;
                $headings[] = 'berat ' . $store->name;
            }
        } else {
            array_push($headings, 'sku', 'harga', 'harga tambahan');
            if ($isSuper) array_push($headings, 'cogm');
            array_push($headings, 'stok', 'berat');
        }

        array_push($headings, 'deskripsi_produk', 'dimensi_produk');

        return $headings;
    }

    public function map($productDetail): array
    {
        $map = [
            $productDetail->getOriginal('product_id'), // id produk parent
            $productDetail->getOriginal('id'),         // id produk detail
            $productDetail->productWithoutScope?->getOriginal('title'),
            $productDetail->variant?->getOriginal('name'),
            $productDetail->color?->getOriginal('label'),
        ];

        $isSuper = session()->get('current_role') && in_array(session()->get('current_role')->name, ['super', 'admin']);

        if ($this->storeId === 'all') {
            $allDetails = ProductDetail::where('product_id', $productDetail->product_id)
                ->where('variant_id', $productDetail->variant_id)
                ->where('color_id', $productDetail->color_id)
                ->get()
                ->keyBy('store_id');
                
            foreach ($this->stores as $store) {
                $detail = $allDetails->get($store->id);
                if ($detail) {
                    $map[] = $detail->sku;
                    $map[] = $detail->price;
                    $map[] = $detail->additional_price ?? 0;
                    if ($isSuper) $map[] = $detail->cogm ?? 0;
                    $map[] = (string) $detail->qty;
                    $map[] = $detail->weight;
                } else {
                    $map[] = '';
                    $map[] = '0';
                    $map[] = '0';
                    if ($isSuper) $map[] = '0';
                    $map[] = '0';
                    $map[] = '0';
                }
            }
        } else {
            array_push($map, 
                $productDetail->getOriginal('sku'),
                $productDetail->getOriginal('price'),
                $productDetail->getOriginal('additional_price') ?? 0
            );
            if ($isSuper) array_push($map, $productDetail->getOriginal('cogm') ?? 0);
            array_push($map,
                (string) $productDetail->getOriginal('qty'),
                $productDetail->getOriginal('weight')
            );
        }

        array_push($map, '', '');

        return $map;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }
}
