<?php

namespace App\Http\Controllers\Master;

use App\Models\AssetInventory;
use App\Models\Module;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as Req;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Core\BackendController;
use App\Exports\AssetInventoryFormulaExport;
use Maatwebsite\Excel\Facades\Excel;

class AssetInventoryReportController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Laporan Penyusutan Inventaris";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
    }

    public function index()
    {
        \RuleHelper::UserHasPermission('access');

        $activeStore = session('store');
        $activeStoreId = is_array($activeStore) ? ($activeStore['id'] ?? null) : ($activeStore->id ?? null);

        if (!$activeStoreId) {
            $user = Auth::user();
            $userStoreIdsRaw = $user ? $user->store_id : null;
            if (!is_array($userStoreIdsRaw)) {
                $userStoreIdsDecoded = $userStoreIdsRaw ? json_decode($userStoreIdsRaw, true) : [];
                $userStoreIdsRaw = is_array($userStoreIdsDecoded) ? $userStoreIdsDecoded : [];
            }
            $firstStoreId = !empty($userStoreIdsRaw) ? $userStoreIdsRaw[0] : null;
            $defaultStore = $firstStoreId ? Store::whereNull('deleted_at')->find($firstStoreId) : Store::whereNull('deleted_at')->first();
            if ($defaultStore) {
                session(['store' => ['id' => (string) $defaultStore->id, 'name' => $defaultStore->name]]);
                $activeStoreId = (string) $defaultStore->id;
            }
        }

        $this->data['activeStoreId'] = $activeStoreId;
        $this->data['defaultCutoffDate'] = date('Y-m-d');

        return view('backend.pages.asset_inventory_report.index', $this->data);
    }

    public function get_datatable(Request $request)
    {
        $activeStore = session('store');
        $activeStoreId = is_array($activeStore) ? ($activeStore['id'] ?? null) : ($activeStore->id ?? null);
        $storeId = $request->store_id ?: $activeStoreId;

        $cutoffDateStr = $request->cutoff_date ?: date('Y-m-d');
        $cutoffDate = Carbon::parse($cutoffDateStr);

        $assets = AssetInventory::with('store')->select([
            'id',
            'store_id',
            'code',
            'name',
            'merk',
            'category',
            'depreciation_year',
            'masa',
            'nilai_residu',
            'purchase_price',
            'jumlah_unit',
            'harga_per_unit',
            'umur_manfaat',
            'last_condition',
            'tgl',
            'image',
            'created_at'
        ])->whereNull('deleted_at');

        if ($storeId) {
            $assets->where('store_id', $storeId);
        }

        // Hitung total keseluruhan untuk footer
        $allAssets = (clone $assets)->get();
        $totalNilaiPerolehan = 0;
        $totalSusutBulan = 0;
        $totalAkumulasiSusut = 0;
        $totalNilaiBuku = 0;

        foreach ($allAssets as $item) {
            $nilaiK = (float) $item->nilai_perolehan;
            $persenL = (float) $item->presentase_susut;
            $batasN = (int) $item->batas_susut;

            $diffM = 0;
            if ($item->tgl) {
                $tgl = Carbon::parse($item->tgl);
                $diffM = max(0, ($cutoffDate->year - $tgl->year) * 12 + ($cutoffDate->month - $tgl->month));
            }

            $susutNormal = ($nilaiK * $persenL) / 12;
            $susutBulan = 0;
            $akumulasi = 0;

            if ($diffM <= 0) {
                $susutBulan = 0;
                $akumulasi = 0;
            } elseif ($diffM == $batasN) {
                $susutBulan = max(0, $susutNormal - 1);
                $akumulasi = max(0, ($diffM * $susutNormal) - 1);
            } elseif ($diffM > $batasN) {
                $susutBulan = 0;
                $akumulasi = max(0, ($batasN * $susutNormal) - 1);
            } else {
                $susutBulan = $susutNormal;
                $akumulasi = $diffM * $susutNormal;
            }

            $nilaiBuku = max(0, $nilaiK - $akumulasi);

            $totalNilaiPerolehan += $nilaiK;
            $totalSusutBulan += $susutBulan;
            $totalAkumulasiSusut += $akumulasi;
            $totalNilaiBuku += $nilaiBuku;
        }

        $datatables = DataTables::of($assets);

        return $datatables
            ->with('totals', [
                'nilai_perolehan' => 'Rp ' . number_format($totalNilaiPerolehan, 0, ',', '.'),
                'penyusutan_per_bulan' => 'Rp ' . number_format($totalSusutBulan, 0, ',', '.'),
                'akumulasi_penyusutan' => 'Rp ' . number_format($totalAkumulasiSusut, 0, ',', '.'),
                'nilai_buku' => 'Rp ' . number_format($totalNilaiBuku, 0, ',', '.'),
            ])
            ->addColumn('number', function ($data) {
                return "";
            })
            ->addColumn('image', function ($data) {
                if ($data->image && file_exists(public_path('uploads/asset_inventories/' . $data->image))) {
                    return '<img src="' . asset('uploads/asset_inventories/' . $data->image) . '" height="40px" class="rounded" style="object-fit:cover;"/>';
                }
                return '<img src="' . asset('/img/noimage.png') . '" height="40px" class="rounded"/>';
            })
            ->addColumn('kondisi', function ($data) {
                return ucfirst($data->last_condition ?? 'Aktif');
            })
            ->addColumn('umur_manfaat_tahun', function ($data) {
                $umur = $data->umur_manfaat ?: ($data->masa ? round($data->masa / 12, 1) : 4);
                return (float) $umur;
            })
            ->addColumn('tgl_perolehan', function ($data) {
                return $data->tgl ? Carbon::parse($data->tgl)->format('d/m/Y') : '-';
            })
            ->addColumn('jumlah_unit', function ($data) {
                return $data->jumlah_unit ?? 1;
            })
            ->addColumn('harga_per_unit', function ($data) {
                $harga = $data->harga_per_unit ?: ($data->purchase_price ? ((float) $data->purchase_price / max(1, (int) ($data->jumlah_unit ?? 1))) : 0);
                return 'Rp ' . number_format($harga, 0, ',', '.');
            })
            ->addColumn('nilai_perolehan', function ($data) {
                return 'Rp ' . number_format($data->nilai_perolehan, 0, ',', '.');
            })
            ->addColumn('presentase_susut', function ($data) {
                return round($data->presentase_susut * 100, 1) . '%';
            })
            ->addColumn('jumlah_susut', function ($data) use ($cutoffDate) {
                if (!$data->tgl) return 0;
                $tgl = Carbon::parse($data->tgl);
                $diff = ($cutoffDate->year - $tgl->year) * 12 + ($cutoffDate->month - $tgl->month);
                return max(0, $diff);
            })
            ->addColumn('batas_susut', function ($data) {
                return $data->batas_susut;
            })
            ->addColumn('penyusutan_per_bulan', function ($data) use ($cutoffDate) {
                $nilaiK = $data->nilai_perolehan;
                $persenL = $data->presentase_susut;
                $batasN = $data->batas_susut;

                $diffM = 0;
                if ($data->tgl) {
                    $tgl = Carbon::parse($data->tgl);
                    $diffM = max(0, ($cutoffDate->year - $tgl->year) * 12 + ($cutoffDate->month - $tgl->month));
                }

                $susutBulan = 0;
                $susutNormal = ($nilaiK * $persenL) / 12;

                if ($diffM <= 0) {
                    $susutBulan = 0;
                } elseif ($diffM == $batasN) {
                    $susutBulan = max(0, $susutNormal - 1);
                } elseif ($diffM > $batasN) {
                    $susutBulan = 0;
                } else {
                    $susutBulan = $susutNormal;
                }

                return 'Rp ' . number_format($susutBulan, 0, ',', '.');
            })
            ->addColumn('akumulasi_penyusutan', function ($data) use ($cutoffDate) {
                $nilaiK = $data->nilai_perolehan;
                $persenL = $data->presentase_susut;
                $batasN = $data->batas_susut;

                $diffM = 0;
                if ($data->tgl) {
                    $tgl = Carbon::parse($data->tgl);
                    $diffM = max(0, ($cutoffDate->year - $tgl->year) * 12 + ($cutoffDate->month - $tgl->month));
                }

                $susutNormal = ($nilaiK * $persenL) / 12;
                $akumulasi = 0;

                if ($diffM <= 0) {
                    $akumulasi = 0;
                } elseif ($diffM == $batasN) {
                    $akumulasi = max(0, ($diffM * $susutNormal) - 1);
                } elseif ($diffM > $batasN) {
                    $akumulasi = max(0, ($batasN * $susutNormal) - 1);
                } else {
                    $akumulasi = $diffM * $susutNormal;
                }

                return 'Rp ' . number_format($akumulasi, 0, ',', '.');
            })
            ->addColumn('nilai_buku', function ($data) use ($cutoffDate) {
                $nilaiK = $data->nilai_perolehan;
                $persenL = $data->presentase_susut;
                $batasN = $data->batas_susut;

                $diffM = 0;
                if ($data->tgl) {
                    $tgl = Carbon::parse($data->tgl);
                    $diffM = max(0, ($cutoffDate->year - $tgl->year) * 12 + ($cutoffDate->month - $tgl->month));
                }

                $susutNormal = ($nilaiK * $persenL) / 12;
                $akumulasi = 0;

                if ($diffM <= 0) {
                    $akumulasi = 0;
                } elseif ($diffM == $batasN) {
                    $akumulasi = max(0, ($diffM * $susutNormal) - 1);
                } elseif ($diffM > $batasN) {
                    $akumulasi = max(0, ($batasN * $susutNormal) - 1);
                } else {
                    $akumulasi = $diffM * $susutNormal;
                }

                $nilaiBuku = max(0, $nilaiK - $akumulasi);
                return 'Rp ' . number_format($nilaiBuku, 0, ',', '.');
            })
            ->rawColumns(['image'])
            ->make(true);
    }

    public function export(Request $request)
    {
        $activeStore = session('store');
        $activeStoreId = is_array($activeStore) ? ($activeStore['id'] ?? null) : ($activeStore->id ?? null);
        $storeId = $request->store_id ?: $activeStoreId;
        $cutoffDate = $request->cutoff_date ?: Carbon::now();

        $store = Store::find($storeId);
        $storeSlug = $store ? \Str::slug($store->name) : 'seco';
        $fileName = 'laporan-penyusutan-inventaris-' . $storeSlug . '-' . Carbon::parse($cutoffDate)->format('Ymd') . '.xlsx';

        return Excel::download(new AssetInventoryFormulaExport($storeId, $cutoffDate), $fileName);
    }
}
