<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Store;
use App\Models\Module;

class AccountingController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->data['page_title'] = "Accounting";
        $this->data['titleHeader'] = (object)['name' => 'Accounting', 'icon' => 'bx bx-book-content'];
    }

    public function headAccountIndex()
    {
        // Fake variables needed for the Head Account UI
        $this->data['head_accounts_count'] = 0;
        $this->data['accounting_business_groups'] = [1 => 'Retail', 2 => 'Grosir'];
        $this->data['import_template'] = (object)['id' => 1, 'rules' => []];

        return view('backend.pages.head_account.index', $this->data);
    }

    public function incomeIndex()
    {
        $this->data['page_title'] = "Pemasukan";
        $this->data['titleHeader'] = (object)['name' => 'Pemasukan', 'icon' => 'bx bx-arrow-to-bottom'];
        $this->data['head_accounts'] = [(object)['id' => 1, 'name' => 'Kas Besar']];
        $this->data['pemasukanke'] = [(object)['id' => 1, 'name' => 'Pendapatan Jasa']];

        return view('backend.pages.pemasukan.index', $this->data);
    }

    public function expenditureIndex()
    {
        $this->data['page_title'] = "Pengeluaran";
        $this->data['titleHeader'] = (object)['name' => 'Pengeluaran', 'icon' => 'bx bx-arrow-from-bottom'];
        $this->data['head_accounts'] = [(object)['id' => 1, 'name' => 'Kas Besar']];
        $this->data['head_accounts_payment'] = [(object)['id' => 1, 'name' => 'Biaya Operasional']];

        return view('backend.pages.pengeluaran.index', $this->data);
    }

    public function dashboardFinanceIndex()
    {
        $this->data['page_title'] = "Dashboard Finance";
        $this->data['titleHeader'] = (object)['name' => 'Dashboard Finance', 'icon' => 'bx bx-pie-chart-alt-2'];
        
        $year = date('Y');
        $monthNumeric = date('m');
        
        $monthStr = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        
        $yearList = [];
        for ($i = (int)$year; $i >= 2020; $i--) {
            $yearList[$i] = $i;
        }

        $this->data['year'] = $year;
        $this->data['monthNumeric'] = $monthNumeric;
        $this->data['month'] = $monthStr[$monthNumeric] ?? date('F');
        $this->data['yearList'] = $yearList;
        $this->data['monthList'] = $monthStr;

        // Metrik Ringkasan (Laba Rugi, Pendapatan, Pengeluaran)
        $this->data['profitThisYear'] = 0;
        $this->data['profitThisYearGrowth'] = 0;
        $this->data['profitThisYearInMillion'] = '0';
        $this->data['profitRatio'] = 0;

        $this->data['revenueThisYear'] = 0;
        $this->data['revenueThisYearGrowth'] = 0;
        $this->data['expenseThisYear'] = 0;
        $this->data['expenseThisYearGrowth'] = 0;

        $this->data['revenueThisMonth'] = 0;
        $this->data['revenueThisMonthGrowth'] = 0;
        $this->data['expenseThisMonth'] = 0;
        $this->data['expenseThisMonthGrowth'] = 0;

        // Data Chart
        $this->data['categoryChartPerYear'] = [(int)$year - 2, (int)$year - 1, (int)$year];
        $this->data['revenueChartPerYear'] = [0, 0, 0];
        $this->data['expenseChartPerYear'] = [0, 0, 0];

        $this->data['categoryChartPerMonth'] = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $this->data['revenueChartPerMonth'] = array_fill(0, 12, 0);
        $this->data['expenseChartPerMonth'] = array_fill(0, 12, 0);

        $daysInMonth = (int)date('t');
        $this->data['categoryChartPerDay'] = range(1, $daysInMonth);
        $this->data['revenueChartPerDay'] = array_fill(0, $daysInMonth, 0);

        $this->data['revenueCategoryInYearChart'] = [];
        $this->data['expenseCategoryInYearChart'] = [];

        // Filter & List
        $this->data['lastBalance'] = [];
        $this->data['lastIncome'] = [];
        $this->data['head_account_revenue'] = [];
        $this->data['filter_account_exclude_revenue'] = [];
        $this->data['head_account_expense'] = [];
        $this->data['filter_account_exclude_expense'] = [];

        return view('backend.pages.dashboard-finance.index', $this->data);
    }

    public function journalIntegrationIndex(Request $request)
    {
        $this->data['page_title'] = "Integrasi Jurnal";
        $this->data['titleHeader'] = Module::where('url', '=', 'journal-integration')->first(['icon', 'name', 'url'])
            ?? (object)['name' => 'Integrasi Jurnal', 'icon' => 'bx bx-link'];

        $selected_outlet = $request->get('outlet_id') ?: null;
        $this->data['outlet_id'] = $selected_outlet;

        // Ambil data Store asli dari database untuk filter
        $stores = Store::whereNull('deleted_at')->orderBy('name')->get();
        $this->data['outlets'] = $stores;

        // Ambil data Akun riil dari tabel head_accounts
        $list = ['assets', 'liability', 'equity', 'expense', 'revenue', 'hpp'];
        $dataAccount = [];
        foreach ($list as $nature) {
            $tmp = \App\Models\HeadAccount::whereNull('deleted_at')
                ->where('nature', $nature)
                ->orderBy('name', 'ASC')
                ->get();

            if ($tmp->count() > 0) {
                $dataAccount[$nature] = $nature;
                foreach ($tmp as $value) {
                    $dataAccount[$value->id] = $value->name;
                }
            }
        }
        if (empty($dataAccount)) {
            $dataAccount = [
                'assets' => 'assets',
                '00000000-0000-0000-0000-000000000001' => '- Belum ada akun (silakan tambah di menu Akun) -'
            ];
        }
        $this->data['dataAccount'] = $dataAccount;

        // Ambil data Mapping sesuai outlet_id
        $queryMapping = \App\Models\HeadAccountMapping::whereNull('deleted_at');
        if ($selected_outlet) {
            $queryMapping->where(function ($q) use ($selected_outlet) {
                $q->where('store_id', $selected_outlet)->orWhere('outlet_id', $selected_outlet);
            });
        } else {
            $queryMapping->whereNull('store_id')->whereNull('outlet_id');
        }
        $head_account_mapping = $queryMapping->get();
        $this->data['head_account_mapping'] = $head_account_mapping;

        // Toggles
        $toggleOffUuid = '00000000-0000-0000-0000-000000000003';
        $useGlobalSales = $head_account_mapping->firstWhere('key', 'use_global_sales');
        $this->data['use_global_sales'] = !$useGlobalSales || $useGlobalSales->debit != $toggleOffUuid;

        $useGlobalPurchase = $head_account_mapping->firstWhere('key', 'use_global_purchase');
        $this->data['use_global_purchase'] = !$useGlobalPurchase || $useGlobalPurchase->debit != $toggleOffUuid;

        // Ambil data PaymentType asli dari DB
        $paymentTypes = \App\Models\PaymentType::whereNull('deleted_at')->orderBy('payment_type')->get();
        $payments = [];

        foreach ($paymentTypes as $pt) {
            if (strtolower($pt->payment_type) === 'cash' || strtolower($pt->payment_type) === 'tunai') {
                foreach ($stores as $store) {
                    if (!$selected_outlet || $selected_outlet == $store->id) {
                        $key = 'payment_' . $pt->id . '_' . $store->id;
                        $savedMap = \App\Models\HeadAccountMapping::whereNull('deleted_at')->where('key', $key)->first();
                        $payments[] = (object)[
                            'id' => $pt->id . '_' . $store->id,
                            'name' => $pt->payment_type,
                            'outlet_name' => $store->name,
                            'head_account_id' => $savedMap ? $savedMap->debit : null
                        ];
                    }
                }
            } else {
                $key = 'payment_' . $pt->id;
                $savedMap = \App\Models\HeadAccountMapping::whereNull('deleted_at')->where('key', $key)->first();
                $payments[] = (object)[
                    'id' => $pt->id,
                    'name' => $pt->payment_type,
                    'outlet_name' => '',
                    'head_account_id' => $savedMap ? $savedMap->debit : null
                ];
            }
        }
        $this->data['payments'] = $payments;

        return view('backend.pages.journal_integration.setting', $this->data);
    }

    public function journalIntegrationUpdate(Request $request)
    {
        $outlet_id = $request->outlet_id ?: null;
        $user = \Illuminate\Support\Facades\Auth::user();

        // 1. Simpan Mapping Metode Pembayaran
        if ($request->has('mapping_payment_account')) {
            foreach ($request->mapping_payment_account as $val) {
                if (!empty($val)) {
                    $parts = explode('_', $val);
                    $headAccountId = end($parts);
                    array_pop($parts);
                    $payKeyId = implode('_', $parts);

                    \App\Models\HeadAccountMapping::updateOrCreate(
                        ['key' => 'payment_' . $payKeyId],
                        [
                            'debit' => $headAccountId,
                            'users_id' => $user ? $user->id : null,
                            'store_id' => $outlet_id,
                            'outlet_id' => $outlet_id
                        ]
                    );
                }
            }
        }

        // 2. Simpan Toggle Global Sales & Purchase jika ada outlet_id
        $toggleOnUuid = '00000000-0000-0000-0000-000000000002';
        $toggleOffUuid = '00000000-0000-0000-0000-000000000003';

        if ($outlet_id) {
            $useGlobalSales = $request->has('use_global_sales') ? $toggleOnUuid : $toggleOffUuid;
            \App\Models\HeadAccountMapping::updateOrCreate(
                ['key' => 'use_global_sales', 'store_id' => $outlet_id],
                ['debit' => $useGlobalSales, 'outlet_id' => $outlet_id, 'users_id' => $user ? $user->id : null]
            );

            $useGlobalPurchase = $request->has('use_global_purchase') ? $toggleOnUuid : $toggleOffUuid;
            \App\Models\HeadAccountMapping::updateOrCreate(
                ['key' => 'use_global_purchase', 'store_id' => $outlet_id],
                ['debit' => $useGlobalPurchase, 'outlet_id' => $outlet_id, 'users_id' => $user ? $user->id : null]
            );
        }

        // 3. Simpan Mapping Penjualan (Sales)
        $shouldProcessSales = !$outlet_id || !$request->has('use_global_sales');
        if ($request->has('label') && $shouldProcessSales && $request->sales_debit) {
            $indexSales = 0;
            foreach ($request->label as $val) {
                $debitValue = $request->sales_debit[$indexSales] ?? '';
                $creditValue = $request->sales_credit[$indexSales] ?? '';

                $mapping = \App\Models\HeadAccountMapping::where('key', $val);
                if ($outlet_id) {
                    $mapping->where(function ($q) use ($outlet_id) {
                        $q->where('store_id', $outlet_id)->orWhere('outlet_id', $outlet_id);
                    });
                } else {
                    $mapping->whereNull('store_id')->whereNull('outlet_id');
                }
                $existMapping = $mapping->first();

                if ($debitValue === '' && $creditValue === '') {
                    if ($existMapping) {
                        $existMapping->delete();
                    }
                } else {
                    if (!$existMapping) {
                        $existMapping = new \App\Models\HeadAccountMapping();
                        $existMapping->key = $val;
                        $existMapping->store_id = $outlet_id;
                        $existMapping->outlet_id = $outlet_id;
                        $existMapping->users_id = $user ? $user->id : null;
                    }
                    $existMapping->debit = ($debitValue === '') ? null : $debitValue;
                    $existMapping->credit = ($creditValue === '') ? null : $creditValue;
                    $existMapping->save();
                }
                $indexSales++;
            }
        }

        // 4. Simpan Mapping Pembelian (Purchase)
        $shouldProcessPurchase = !$outlet_id || !$request->has('use_global_purchase');
        if ($request->has('purchase_label') && $shouldProcessPurchase && $request->purchase_debit) {
            $indexPurchase = 0;
            foreach ($request->purchase_label as $val) {
                $debitValue = $request->purchase_debit[$indexPurchase] ?? '';
                $creditValue = $request->purchase_credit[$indexPurchase] ?? '';

                $mapping = \App\Models\HeadAccountMapping::where('key', $val);
                if ($outlet_id) {
                    $mapping->where(function ($q) use ($outlet_id) {
                        $q->where('store_id', $outlet_id)->orWhere('outlet_id', $outlet_id);
                    });
                } else {
                    $mapping->whereNull('store_id')->whereNull('outlet_id');
                }
                $existMapping = $mapping->first();

                if ($debitValue === '' && $creditValue === '') {
                    if ($existMapping) {
                        $existMapping->delete();
                    }
                } else {
                    if (!$existMapping) {
                        $existMapping = new \App\Models\HeadAccountMapping();
                        $existMapping->key = $val;
                        $existMapping->store_id = $outlet_id;
                        $existMapping->outlet_id = $outlet_id;
                        $existMapping->users_id = $user ? $user->id : null;
                    }
                    $existMapping->debit = ($debitValue === '') ? null : $debitValue;
                    $existMapping->credit = ($creditValue === '') ? null : $creditValue;
                    $existMapping->save();
                }
                $indexPurchase++;
            }
        }

        \Session::flash('flash_message', 'Pengaturan integrasi jurnal berhasil disimpan');
        return redirect()->to(url('/journal-integration') . ($outlet_id ? '?outlet_id=' . $outlet_id : ''));
    }

    public function generalJournalIndex()
    {
        $this->data['page_title'] = "Jurnal Umum";
        $this->data['titleHeader'] = (object)['name' => 'Jurnal Umum', 'icon' => 'bx bx-book'];
        
        $this->data['head_accounts'] = [
            (object)['id' => '1', 'name' => '1000 - Kas Besar'],
            (object)['id' => '2', 'name' => '1001 - Kas Kecil']
        ];
        
        $this->data['import_template'] = (object)[
            'id' => 1,
            'rules' => []
        ];
        $this->data['general_journals_count'] = 0;
        
        return view('backend.pages.general_journal.index', $this->data);
    }

    public function bukuBesarIndex()
    {
        $this->data['page_title'] = "Buku Besar";
        $this->data['titleHeader'] = Module::where('url', '=', 'ledger')->first(['icon', 'name', 'url'])
            ?? (object)['name' => 'Buku Besar', 'icon' => 'ri-booklet-fill'];

        $currentDate = Carbon::now();
        $start = Carbon::parse('2026-09-01')->format('Y-m-d');
        $end = Carbon::parse('2026-09-30')->format('Y-m-d');

        $this->data['start_date'] = $start;
        $this->data['end_date'] = $end;

        // Ambil data Store/Cabang seco asli untuk filter
        $stores = Store::whereNull('deleted_at')->get();
        if ($stores->isEmpty()) {
            $stores = collect([
                (object)['id' => '1', 'name' => 'Seco Prawirotaman'],
                (object)['id' => '2', 'name' => 'Seco Showroom'],
                (object)['id' => '3', 'name' => 'Seco Sabin'],
            ]);
        }
        $this->data['businessGroups'] = $stores;

        $subTitle = Carbon::parse($start)->translatedFormat('d F Y') . ' - ' . Carbon::parse($end)->translatedFormat('d F Y');
        $htmlTable = $this->renderMockBukuBesar($start, $end);

        $dataArray = [
            'BUKU BESAR',
            $subTitle,
            $htmlTable
        ];
        $this->data['data'] = json_encode(['data' => $dataArray]);

        return view('backend.pages.general_journal.buku_besar', $this->data);
    }

    public function bukuBesarGetData(Request $request)
    {
        $start = $request->tglAwal ?: '2026-09-01';
        $end = $request->tglAkhir ?: '2026-09-30';
        $selectedGroups = $request->business_group ?? [];

        $subTitle = Carbon::parse($start)->translatedFormat('d F Y') . ' - ' . Carbon::parse($end)->translatedFormat('d F Y');
        $htmlTable = $this->renderMockBukuBesar($start, $end, $selectedGroups);

        return response()->json([
            'data' => [
                'BUKU BESAR',
                $subTitle,
                $htmlTable
            ]
        ]);
    }

    public function bukuBesarExport(Request $request)
    {
        \Session::flash('flash_message', 'Buku Besar dalam proses export (Mock Preview).');
        return redirect()->back();
    }

    private function renderMockBukuBesar($start, $end, $selectedStores = [])
    {
        $accounts = [
            [
                'code' => '1101',
                'name' => '1101 - Kas Seco Prawirotaman',
                'store_name' => 'Seco Prawirotaman',
                'items' => [
                    ['date' => '2026-09-01', 'desc' => 'Saldo Awal Kas Toko', 'debit' => 750000, 'kredit' => 0],
                    ['date' => '2026-09-05', 'desc' => 'Penjualan POS Tunai #INV-2026-001', 'debit' => 1250000, 'kredit' => 0],
                    ['date' => '2026-09-10', 'desc' => 'Beli Perlengkapan & ATK Toko', 'debit' => 0, 'kredit' => 75000],
                    ['date' => '2026-09-15', 'desc' => 'Penjualan POS Tunai #INV-2026-004', 'debit' => 820000, 'kredit' => 0],
                ]
            ],
            [
                'code' => '1102',
                'name' => '1102 - Kas Seco Showroom',
                'store_name' => 'Seco Showroom',
                'items' => [
                    ['date' => '2026-09-01', 'desc' => 'Saldo Awal Kas Toko', 'debit' => 1000000, 'kredit' => 0],
                    ['date' => '2026-09-08', 'desc' => 'Penjualan POS Tunai #INV-2026-002', 'debit' => 2450000, 'kredit' => 0],
                    ['date' => '2026-09-12', 'desc' => 'Setoran Tunai ke Bank BCA', 'debit' => 0, 'kredit' => 2000000],
                ]
            ],
            [
                'code' => '1103',
                'name' => '1103 - Bank BCA',
                'store_name' => 'Semua Cabang',
                'items' => [
                    ['date' => '2026-09-01', 'desc' => 'Saldo Awal Rekening', 'debit' => 15000000, 'kredit' => 0],
                    ['date' => '2026-09-07', 'desc' => 'Penjualan Transfer BCA #INV-2026-003', 'debit' => 3500000, 'kredit' => 0],
                    ['date' => '2026-09-12', 'desc' => 'Terima Setoran Tunai dr Showroom', 'debit' => 2000000, 'kredit' => 0],
                    ['date' => '2026-09-18', 'desc' => 'Pembayaran Listrik & Internet Toko', 'debit' => 0, 'kredit' => 1500000],
                ]
            ],
            [
                'code' => '1104',
                'name' => '1104 - Bank Mandiri (EDC)',
                'store_name' => 'Semua Cabang',
                'items' => [
                    ['date' => '2026-09-01', 'desc' => 'Saldo Awal Rekening', 'debit' => 8500000, 'kredit' => 0],
                    ['date' => '2026-09-14', 'desc' => 'Penjualan EDC Mandiri #INV-2026-005', 'debit' => 1850000, 'kredit' => 0],
                ]
            ],
            [
                'code' => '1201',
                'name' => '1201 - Inventaris & Peralatan (Aset Tetap)',
                'store_name' => 'Semua Cabang',
                'items' => [
                    ['date' => '2026-09-01', 'desc' => 'Saldo Awal Aset', 'debit' => 12500000, 'kredit' => 0],
                    ['date' => '2026-09-23', 'desc' => 'Perolehan Aset: Kursi Super Seco (kursiseco)', 'debit' => 2000000, 'kredit' => 0],
                ]
            ],
            [
                'code' => '4101',
                'name' => '4101 - Pendapatan Penjualan Produk',
                'store_name' => 'Semua Cabang',
                'items' => [
                    ['date' => '2026-09-05', 'desc' => 'Penjualan POS Tunai #INV-2026-001', 'debit' => 0, 'kredit' => 1250000],
                    ['date' => '2026-09-07', 'desc' => 'Penjualan Transfer BCA #INV-2026-003', 'debit' => 0, 'kredit' => 3500000],
                    ['date' => '2026-09-08', 'desc' => 'Penjualan POS Tunai #INV-2026-002', 'debit' => 0, 'kredit' => 2450000],
                    ['date' => '2026-09-14', 'desc' => 'Penjualan EDC Mandiri #INV-2026-005', 'debit' => 0, 'kredit' => 1850000],
                    ['date' => '2026-09-15', 'desc' => 'Penjualan POS Tunai #INV-2026-004', 'debit' => 0, 'kredit' => 820000],
                ]
            ],
        ];

        $html = '<table class="table table-bordered table-striped align-middle mb-4" style="min-width:700px;">';
        foreach ($accounts as $acc) {
            $html .= '<tr class="table-primary"><td colspan="5" style="font-weight:700;font-size:15px;padding:10px 15px;">' . e($acc['name']) . '</td></tr>';
            $html .= '<tr class="table-light" style="font-weight:600;text-transform:uppercase;font-size:12px;">
                        <td style="width:15%;">TANGGAL</td>
                        <td style="width:40%;">TRANSAKSI</td>
                        <td style="width:15%;text-align:right;">DEBIT</td>
                        <td style="width:15%;text-align:right;">KREDIT</td>
                        <td style="width:15%;text-align:right;">SALDO</td>
                      </tr>';

            $saldo = 0;
            foreach ($acc['items'] as $item) {
                if (str_starts_with($acc['code'], '4')) {
                    $saldo += $item['kredit'] - $item['debit'];
                } else {
                    $saldo += $item['debit'] - $item['kredit'];
                }

                $saldoTampil = $saldo < 0 ? '(' . number_format(abs($saldo), 0, ',', '.') . ')' : number_format($saldo, 0, ',', '.');
                $debitTampil = $item['debit'] > 0 ? 'Rp ' . number_format($item['debit'], 0, ',', '.') : '-';
                $kreditTampil = $item['kredit'] > 0 ? 'Rp ' . number_format($item['kredit'], 0, ',', '.') : '-';

                $html .= '<tr>
                            <td>' . date('d M Y', strtotime($item['date'])) . '</td>
                            <td>' . e($item['desc']) . '</td>
                            <td style="text-align:right;">' . $debitTampil . '</td>
                            <td style="text-align:right;">' . $kreditTampil . '</td>
                            <td style="text-align:right;font-weight:600;">Rp ' . $saldoTampil . '</td>
                          </tr>';
            }
        }
        $html .= '</table>';

        return $html;
    }
}
