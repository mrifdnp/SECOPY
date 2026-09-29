<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Store;
use App\Models\Category;
use App\Models\MainCategory;
use App\Models\Material;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Picqer\Barcode\Renderers\DynamicHtmlRenderer;
use Picqer\Barcode\Renderers\HtmlRenderer;
use Picqer\Barcode\Renderers\PngRenderer;
use Picqer\Barcode\Renderers\SvgRenderer;
use Picqer\Barcode\Types\TypeCode128;
use Yajra\DataTables\DataTables;

class PrintBarcodeController extends BackendController
{
    private $barcodeGenerator;
    private $htmlRenderer;
    private $dynamicHtmlRenderer;
    private $svgRenderer;
    private $pngRenderer;

    public function __construct()
    {
        parent::__construct();

        $this->htmlRenderer        = new HtmlRenderer();
        $this->dynamicHtmlRenderer = new DynamicHtmlRenderer();
        $this->svgRenderer         = new SvgRenderer();
        $this->pngRenderer         = new PngRenderer();
        $this->barcodeGenerator    = new TypeCode128();
    }

    public function index()
    {
        $authUser     = Auth::user();
        $userStoreIds = json_decode($authUser->store_id ?? '[]', true);
        $stores       = Store::whereIn('id', $userStoreIds)->get();

        $mainCategories = MainCategory::all();
        $categories = Category::all();
        $materials = Material::all();

        $this->data['stores'] = $stores;
        $this->data['mainCategories'] = $mainCategories;
        $this->data['categories'] = $categories;
        $this->data['materials'] = $materials;

        return view('backend.pages.print_barcode.index', $this->data);
    }

    public function preview_datatable(Request $request)
    {
        $title = $request->get('title', null);
        $sku = $request->get('sku', null);
        $mainCategoryId = $request->get('main_category_id', null);
        $categoryId = $request->get('category_id', null);
        $materialId = $request->get('material_id', null);
        
        $activeStoreId = session()->get('store')['id'] ?? null;

        $productDetails = ProductDetail::with([
            'productWithoutScope',
            'variant',
            'color',
            'store',
        ])
            ->whereNotNull('sku')
            ->whereHas('productWithoutScope')
            ->when($activeStoreId, function ($query) use ($activeStoreId) {
                return $query->where('store_id', $activeStoreId);
            })
            ->when($title, function ($query) use ($title) {
                return $query->whereHas('productWithoutScope', function ($q) use ($title) {
                    $q->whereRaw('LOWER(title) LIKE LOWER(?)', ['%' . $title . '%']);
                });
            })
            ->when($sku, function ($query) use ($sku) {
                return $query->whereRaw('LOWER(sku) LIKE LOWER(?)', ['%' . $sku . '%']);
            })
            ->when($mainCategoryId, function ($query) use ($mainCategoryId) {
                return $query->whereHas('productWithoutScope.categories.mainCategory', function ($q) use ($mainCategoryId) {
                    $q->where('id', $mainCategoryId);
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->whereHas('productWithoutScope', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($materialId, function ($query) use ($materialId) {
                return $query->whereHas('productWithoutScope', function ($q) use ($materialId) {
                    $q->where('material_id', $materialId);
                });
            })
            ->orderBy('created_at', 'desc');

        return DataTables::of($productDetails)
            ->addColumn('checkbox', function ($row) {
                $name = htmlspecialchars($row?->product?->title ?? '-', ENT_QUOTES);
                $variant = htmlspecialchars($row?->variant?->name ?? '-', ENT_QUOTES);
                $color = htmlspecialchars($row?->color?->label ?? '-', ENT_QUOTES);
                $sku = htmlspecialchars($row->sku ?? '-', ENT_QUOTES);
                return '<div class="form-check form-check-md text-center"><input class="form-check-input dt-checkbox" type="checkbox" value="' . $row->id . '" data-name="'.$name.'" data-variant="'.$variant.'" data-color="'.$color.'" data-sku="'.$sku.'"></div>';
            })
            ->addColumn('qty', function ($row) {
                return '<input type="number" class="form-control form-control-sm dt-qty p-1 text-center" min="1" value="1" data-id="' . $row->id . '" style="width: 50px; margin: 0 auto;">';
            })
            ->addColumn('product_name', function ($row) {
                return $row->productWithoutScope->title ?? '-';
            })
            ->addColumn('variant', function ($row) {
                return $row?->variant?->name ?? '-';
            })
            ->addColumn('color', function ($row) {
                return $row?->color?->label ?? '-';
            })
            ->addColumn('store', function ($row) {
                return $row?->store?->name ?? '-';
            })
            ->editColumn('sku', function ($row) {
                return $row->sku ?? '-';
            })
            ->rawColumns(['checkbox', 'qty'])
            ->toJson();
    }

    public function search_products(Request $request)
    {
        $searchTerm = $request->get('keyword', '');

        $products = Product::with(['productDetail', 'productDetail.variant', 'productDetail.color'])
            ->whereRaw('LOWER(title) LIKE LOWER(?)', ['%' . $searchTerm . '%'])
            ->limit(10)
            ->get();

        $variants = $products->flatMap(function ($product) {
            return $product->variants;
        })->unique('id')->values();

        $colors = $products->flatMap(function ($product) {
            return $product->colors;
        })->unique('id')->values();

        return response()->json([
            'products' => $products,
            'variants' => $variants,
            'colors'   => $colors,
        ]);
    }

    public function print(Request $request)
    {
        $storeId            = $request->get('store_id', null);
        $productId          = $request->get('product_id', null);
        $variantId          = $request->get('variant_id', null);
        $colorId            = $request->get('color_id', null);
        $mainCategoryId     = $request->get('main_category_id', null);
        $categoryId         = $request->get('category_id', null);
        $materialId         = $request->get('material_id', null);
        $includeSku         = $request->get('include_sku', false);
        $includeProductName = $request->get('include_product_name', false);
        $includeVariation   = $request->get('include_variation', false);
        $includePrice       = $request->get('include_price', false);

        $paperSize        = $request->get('paper_size', 'a4');
        $paperOrientation = $request->get('paper_orientation', 'landscape');
        $paperWidth       = $request->get('paper_width', null);
        $paperHeight      = $request->get('paper_height', null);
        
        $columns       = $request->get('columns', 5);
        $rowsPerPage   = $request->get('rows_per_page', 7);
        $marginTop     = $request->get('margin_top', 5);
        $marginLeft    = $request->get('margin_left', 5);
        $labelWidth    = $request->get('label_width', 52);
        $labelHeight   = $request->get('label_height', 30);
        $gapH          = $request->get('gap_h', 2);
        $gapV          = $request->get('gap_v', 2);
        $fontSize      = $request->get('font_size', 8);
        $barcodeHeight = $request->get('barcode_height', 40);
        $barcodeWidth  = $request->get('barcode_width', 2);
        $barcodeType   = $request->get('barcode_type', 'barcode');

        $includeBarcode = $request->get('include_barcode', false);
        $posSku         = $request->get('pos_sku', ['left' => 10, 'top' => 40]);
        $posName        = $request->get('pos_name', ['left' => 10, 'top' => 55]);
        $posPrice       = $request->get('pos_price', ['left' => 10, 'top' => 75]);
        $posBarcode     = $request->get('pos_barcode', ['left' => 10, 'top' => 10]);

        $selectedItemsJson = $request->get('selected_items');
        $selectedItems = $selectedItemsJson ? json_decode($selectedItemsJson, true) : [];

        $query = ProductDetail::with([
            'productWithoutScope',
            'variant',
            'color',
            'store',
        ])
        ->whereNotNull('sku')
        ->whereHas('productWithoutScope');

        if (!empty($selectedItems)) {
            $selectedIds = array_keys($selectedItems);
            $query->whereIn('id', $selectedIds);
        } else {
            $query->when($storeId, function ($q) use ($storeId) {
                return $q->where('store_id', $storeId);
            })
            ->when($productId, function ($q) use ($productId) {
                return $q->where('product_id', $productId);
            })
            ->when(! is_null($variantId) && ! is_null($colorId), function ($q) use ($variantId, $colorId) {
                return $q->where('variant_id', $variantId)
                    ->where('color_id', $colorId);
            })
            ->when(! is_null($colorId) && is_null($variantId), function ($q) use ($colorId) {
                return $q->where('color_id', $colorId);
            })
            ->when(! is_null($variantId) && is_null($colorId), function ($q) use ($variantId) {
                return $q->where('variant_id', $variantId);
            })
            ->when($mainCategoryId, function ($query) use ($mainCategoryId) {
                return $query->whereHas('product.categories.mainCategory', function ($q) use ($mainCategoryId) {
                    $q->where('id', $mainCategoryId);
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->whereHas('product', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($materialId, function ($query) use ($materialId) {
                return $query->whereHas('product', function ($q) use ($materialId) {
                    $q->where('material_id', $materialId);
                });
            });
        }

        $baseProductDetails = $query->orderBy('created_at', 'desc')->get();

        // Duplicate based on quantity
        $productDetails = collect();
        foreach ($baseProductDetails as $pd) {
            $qty = !empty($selectedItems) && isset($selectedItems[$pd->id]) ? (int)$selectedItems[$pd->id] : 1;
            for ($i = 0; $i < $qty; $i++) {
                $productDetails->push($pd);
            }
        }

        $autoPaperHeight = $request->get('auto_paper_height', false);

        if ($autoPaperHeight) {
            $totalItems = $productDetails->count();
            // Default columns to 1 if not provided, just to be safe
            $totalRows = ceil($totalItems / ($columns ?: 1));
            
            // Hitung total tinggi yang dibutuhkan (termasuk margin)
            $calculatedHeight = $marginTop + ($totalRows * ($labelHeight + $gapV));
            
            if ($paperSize === 'custom') {
                $widthPt = ($paperWidth ?? 58) * 2.83465;
            } elseif ($paperSize === 'a4') {
                $widthPt = 210 * 2.83465;
            } elseif ($paperSize === 'letter') {
                $widthPt = 215.9 * 2.83465;
            } else {
                $widthPt = 210 * 2.83465;
            }
            
            $heightPt = $calculatedHeight * 2.83465;
            $paperConfig = array(0, 0, $widthPt, $heightPt);
            
            // Set baris per halaman jadi tak terhingga agar tidak ada page break
            $rowsPerPage = 999999;
            
            // DomPdf may unconditionally swap custom arrays if landscape is passed.
            // Since we already calculated width and height, we should force 'portrait'.
            $paperOrientation = 'portrait';
        } elseif ($paperSize === 'thermal_roll') {
            // Untuk printer thermal berkolom: 1 halaman PDF = 1 baris fisik label
            $widthPt = ($paperWidth ?? 110) * 2.83465;
            
            // Tinggi 1 halaman = margin atas + tinggi label + gap vertikal + 1mm buffer
            $heightMm = $marginTop + $labelHeight + $gapV + 1;
            $heightPt = $heightMm * 2.83465;
            
            $paperConfig = array(0, 0, $widthPt, $heightPt);
            $paperOrientation = 'portrait';
            
            // Paksa 1 baris per halaman (karena 1 halaman PDF merepresentasikan 1 baris roll stiker)
            $rowsPerPage = 1;
        } elseif ($paperSize === 'custom' && $paperWidth && $paperHeight) {
            // dompdf accepts points (pt) for custom size, where 1 mm = 2.83465 pt
            $widthPt = $paperWidth * 2.83465;
            $heightPt = $paperHeight * 2.83465;
            $paperConfig = array(0, 0, $widthPt, $heightPt);
            
            // Hack untuk DomPdf: Paksa orientasi agar DomPdf tidak menukar width dan height
            $paperOrientation = 'portrait';
        } else {
            $paperConfig = $paperSize;
        }

        // Jika rotate diaktifkan, swap dimensi kertas agar sesuai dengan CSS @page yang di-swap
        $rotate = $request->get('rotate', 0);
        if (!empty($rotate) && is_array($paperConfig) && count($paperConfig) === 4) {
            $paperConfig = array(0, 0, $paperConfig[3], $paperConfig[2]);
        }

        $pdf = Pdf::loadView('backend.pages.print_barcode.barcodes_pdf', [
            'productDetails'      => $productDetails,
            'barcodeGenerator'    => $this->barcodeGenerator,
            'dynamicHtmlRenderer' => $this->dynamicHtmlRenderer,
            'svgRenderer'         => $this->svgRenderer,
            'pngRenderer'         => $this->pngRenderer,
            'includeSku'          => $includeSku,
            'includeProductName'  => $includeProductName,
            'includeVariation'    => $includeVariation,
            'includePrice'        => $includePrice,
            'columns'             => $columns,
            'rowsPerPage'         => $rowsPerPage,
            'marginTop'           => $marginTop,
            'marginLeft'          => $marginLeft,
            'labelWidth'          => $labelWidth,
            'labelHeight'         => $labelHeight,
            'gapH'                => $gapH,
            'gapV'                => $gapV,
            'paperWidth'          => $paperWidth ?? 70,
            'fontSize'            => $fontSize,
            'barcodeHeight'       => $barcodeHeight,
            'barcodeWidth'        => $barcodeWidth,
            'barcodeType'         => $barcodeType,
            
            'includeBarcode'      => $includeBarcode,
            'rotate'              => $request->get('rotate', 0),
            'posSku'              => $posSku,
            'posName'             => $posName,
            'posPrice'            => $posPrice,
            'posBarcode'          => $posBarcode,
        ])->setPaper($paperConfig, $paperOrientation);

        return $pdf->download('barcode-products.pdf');
        // atau return $pdf->stream('barcode-products.pdf'); untuk preview
    }
}
