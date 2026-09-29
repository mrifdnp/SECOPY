<?php
namespace App\Http\Controllers\Master;

use App\Exports\ProductDetailExport;
use App\Http\Controllers\Controller;
use App\Imports\ImportOpnameProduct;
use App\Imports\ImportProduct;
use App\Imports\ImportProductDetail;
use App\Jobs\CsvImporter;
use App\Models\ImportStatus;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;
use Maatwebsite\Excel\HeadingRowImport;

class ImportController extends Controller
{
    public function download_template(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:product',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        switch ($request->type) {
            case 'product':
                $templatePath = public_path('templates/product-template.csv');
                return response()->download($templatePath, 'product-template.csv');
                break;

            default:
                return redirect()->back()->withErrors(['type' => 'Type not found'])->withInput();
                break;
        }
    }

    public function download_product_detail_template(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        $mainCategoryId = $request->input('main_category_id');
        $categoryId = $request->input('category_id');
        $materialId = $request->input('material_id');
        $emptySkuOnly = $request->input('empty_sku_only') ? true : false;
        $emptyCogmOnly = $request->input('empty_cogm_only') ? true : false;

        if ($request->store_id === 'all') {
            $filename = "[Semua Cabang] Product Detail Template.csv";
            return FacadesExcel::download(new ProductDetailExport('all', $productIds, $mainCategoryId, $categoryId, $materialId, $emptySkuOnly, $emptyCogmOnly), $filename, \Maatwebsite\Excel\Excel::CSV, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ]);
        }

        $store    = Store::findOrFail($request->store_id);
        $filename = "[{$store->name}] Product Detail Template.csv";

        return FacadesExcel::download(new ProductDetailExport($store->id, $productIds, $mainCategoryId, $categoryId, $materialId, $emptySkuOnly, $emptyCogmOnly), $filename, \Maatwebsite\Excel\Excel::CSV, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function import_product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Validasi kolom yang diperlukan
            $mustExistColumns = collect(Product::importProductRules())->keys();
            $headings         = collect((new HeadingRowImport)->toArray($request->file('file'))[0][0]);
            $missingColumns   = $mustExistColumns->diff($headings);
            if ($missingColumns->isNotEmpty()) {
                throw new \Exception('Kolom yang diperlukan tidak ditemukan: ' . $missingColumns->implode(', '));
            }

            // Simpan file yang diunggah ke penyimpanan sementara
            $extension    = $request->file('file')->getClientOriginalExtension();
            $filename     = 'import-product-' . Str::uuid() . '.' . $extension;
            $uploadedPath = Storage::disk('local')->putFileAs('temp', $request->file('file'), $filename);
            if (! $uploadedPath) {
                throw new \Exception('Failed to upload imported file');
            }

            // Buat import status, untuk tracking proses import
            $importStatus = ImportStatus::create([
                'task'       => 'Import product',
                'status'     => 'waiting',
                'source_url' => route('product.index'),
            ]);

            if (! $importStatus) {
                throw new \Exception('Failed to create import status');
            }

            $csvImporter = new CsvImporter(
                ImportProduct::class,
                [$importStatus->id],
                $importStatus->id,
                $uploadedPath,
                'local',
                \Maatwebsite\Excel\Excel::CSV,
                'Import Product'
            );

            $csvImporter->onQueue('imports');
            dispatch($csvImporter);

            Session::flash('flash_message', 'File berhasil diunggah dan sedang diproses. Silakan tunggu beberapa saat untuk melihat hasilnya.');
            return redirect()->route('import_status.index');
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            Session::flash('error_message', 'Gagal mengunggah file. Silakan coba lagi.');
            return redirect()->back()
                ->withErrors(['file' => 'Gagal mengunggah file. Silakan coba lagi.'])
                ->withInput();
        }
    }

    public function import_product_detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'file'     => 'required|file|mimes:csv,txt|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->store_id !== 'all' && !\App\Models\Store::where('id', $request->store_id)->exists()) {
                $validator->errors()->add('store_id', 'Toko yang dipilih tidak valid.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Validasi kolom yang diperlukan
            $mustExistColumns = collect(Product::importProductDetailRules($request->store_id))
                ->keys()
                ->map(fn($col) => \Illuminate\Support\Str::slug($col, '_'));
            
            $headings         = collect((new HeadingRowImport)->toArray($request->file('file'))[0][0]);
            $missingColumns   = $mustExistColumns->diff($headings);
            if ($missingColumns->isNotEmpty()) {
                throw new \Exception('Kolom yang diperlukan tidak ditemukan: ' . $missingColumns->implode(', '));
            }

            // Simpan file yang diunggah ke penyimpanan sementara
            $extension    = $request->file('file')->getClientOriginalExtension();
            $filename     = 'import-product-detail-' . Str::uuid() . '.' . $extension;
            $uploadedPath = Storage::disk('local')->putFileAs('temp', $request->file('file'), $filename);
            if (! $uploadedPath) {
                throw new \Exception('Failed to upload imported file');
            }

            // Buat import status, untuk tracking proses import
            $importStatus = ImportStatus::create([
                'task'       => 'Import product detail',
                'status'     => 'waiting',
                'source_url' => route('product.index'),
            ]);

            if (! $importStatus) {
                throw new \Exception('Failed to create import status');
            }

            $csvImporter = new CsvImporter(
                ImportProductDetail::class,
                [$request->store_id, $importStatus->id],
                $importStatus->id,
                $uploadedPath,
                'local',
                \Maatwebsite\Excel\Excel::CSV,
                'Import Product Detail'
            );

            $csvImporter->onQueue('imports');
            dispatch($csvImporter);

            Session::flash('flash_message', 'File berhasil diunggah dan sedang diproses. Silakan tunggu beberapa saat untuk melihat hasilnya.');
            return redirect()->route('import_status.index');
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            Session::flash('error_message', 'Gagal mengunggah file. Silakan coba lagi.');
            return redirect()->back()
                ->withErrors(['file' => 'Gagal mengunggah file. Silakan coba lagi.'])
                ->withInput();
        }
    }

    public function import_product_opname(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'store_id' => 'required|exists:stores,id',
            'file'     => 'required|file|mimes:csv,txt|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Validasi kolom yang diperlukan
            $mustExistColumns = collect([
                'no',
                'download_date',
                'id',
                'sku',
                'title',
                'category',
                'variant',
                'color',
                'last_qty',
                'opname_qty',
                'opname_date'
            ]);
            // $mustExistColumns = collect(Product::importProductDetailRules())->keys();
            $headings         = collect((new HeadingRowImport)->toArray($request->file('file'))[0][0]);
            $missingColumns   = $mustExistColumns->diff($headings);
            if ($missingColumns->isNotEmpty()) {
                throw new \Exception('Kolom yang diperlukan tidak ditemukan: ' . $missingColumns->implode(', '));
            }

            // Simpan file yang diunggah ke penyimpanan sementara
            $extension    = $request->file('file')->getClientOriginalExtension();
            $filename     = 'import-opname-product-' . Str::uuid() . '.' . $extension;
            $uploadedPath = Storage::disk('local')->putFileAs('temp', $request->file('file'), $filename);
            if (! $uploadedPath) {
                throw new \Exception('Failed to upload imported file');
            }

            // Buat import status, untuk tracking proses import
            $importStatus = ImportStatus::create([
                'task'       => 'Import opname product',
                'status'     => 'waiting',
                'source_url' => route('product.index'),
            ]);

            if (! $importStatus) {
                throw new \Exception('Failed to create import status');
            }
            
            $storeId = $request->store_id;

            $csvImporter = new CsvImporter(
                ImportOpnameProduct::class,
                // [$request->store_id, $importStatus->id],
                [$storeId ?? '', $importStatus->id, (string) auth()->id(), $request->input('stockOpname')],
                $importStatus->id,
                $uploadedPath,
                'local',
                \Maatwebsite\Excel\Excel::CSV,
                'Import Opname Product'
            );

            $csvImporter->onQueue('imports');
            dispatch($csvImporter);

            Session::flash('flash_message', 'File berhasil diunggah dan sedang diproses. Silakan tunggu beberapa saat untuk melihat hasilnya.');
            return redirect()->route('import_status.index');
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            Session::flash('error_message', 'Gagal mengunggah file. Silakan coba lagi.');
            return redirect()->back()
                ->withErrors(['file' => 'Gagal mengunggah file. Silakan coba lagi.'])
                ->withInput();
        }
    }

    public function import_product_image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Validasi kolom yang diperlukan
            $mustExistColumns = collect([
                'product_id',
                'product_name',
                'main_pic',
                'hover_pic',
                'pic_1', 'pic_2', 'pic_3', 'pic_4', 'pic_5', 'pic_6', 'pic_7', 'pic_8'
            ]);
            
            $headings         = collect((new HeadingRowImport)->toArray($request->file('file'))[0][0]);
            $missingColumns   = $mustExistColumns->diff($headings);
            if ($missingColumns->isNotEmpty()) {
                throw new \Exception('Kolom yang diperlukan tidak ditemukan: ' . $missingColumns->implode(', '));
            }

            // Simpan file yang diunggah ke penyimpanan sementara
            $extension    = $request->file('file')->getClientOriginalExtension();
            $filename     = 'import-product-image-' . Str::uuid() . '.' . $extension;
            $uploadedPath = Storage::disk('local')->putFileAs('temp', $request->file('file'), $filename);
            if (! $uploadedPath) {
                throw new \Exception('Failed to upload imported file');
            }

            // Buat import status, untuk tracking proses import
            $importStatus = ImportStatus::create([
                'task'       => 'Import product image',
                'status'     => 'waiting',
                'source_url' => route('product.index'),
            ]);

            if (! $importStatus) {
                throw new \Exception('Failed to create import status');
            }

            $csvImporter = new CsvImporter(
                \App\Imports\ImportProductImage::class,
                [$importStatus->id],
                $importStatus->id,
                $uploadedPath,
                'local',
                \Maatwebsite\Excel\Excel::CSV,
                'Import Product Image'
            );

            $csvImporter->onQueue('imports');
            dispatch($csvImporter);

            Session::flash('flash_message', 'File berhasil diunggah dan sedang diproses. Silakan tunggu beberapa saat untuk melihat hasilnya pada menu Status Import.');
            return redirect()->route('import_status.index');
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            Session::flash('error_message', 'Gagal mengunggah file. Silakan coba lagi: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['file' => 'Gagal mengunggah file. Silakan coba lagi.'])
                ->withInput();
        }
    }
}
