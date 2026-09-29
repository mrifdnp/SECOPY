<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\SalesTransactionItem;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Core\BackendController;

class HomeController extends BackendController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        parent::__construct();
        $this->data['page_title'] = "Dashboard Page";
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $request)
    {
        $period = $request->get('period', date('Y-m'));
        [$year, $month] = explode('-', $period);

        $storeId = $request->get('store_id');
        $stores = \App\Models\Store::whereNull('deleted_at')->get();

        $monthlyEarning = (object)['total' => \App\Models\SalesTransaction::getTotalByPeriod($year, $month, $storeId)];
        $prevMonth = $month == 1 ? 12 : (int)$month - 1;
        $prevMonthlyEarning = \App\Models\SalesTransaction::getTotalByPeriod($year, $prevMonth, $storeId);

        $todayStats = \App\Models\SalesTransaction::getTodayStats($storeId);

        $currTotal = \App\Models\SalesTransaction::getTotalByPeriod($year, null, $storeId);
        $prevTotal = \App\Models\SalesTransaction::getTotalByPeriod($year - 1, null, $storeId);

        $monthlyEarnings = \App\Models\SalesTransaction::getMonthlyEarningsData($year, $storeId);
        $dailyEarnings = \App\Models\SalesTransaction::getDailyEarningsData($year, $month, $storeId);
        $yearlyEarnings = \App\Models\SalesTransaction::getYearlyEarningsData($storeId);

        $topItems = \App\Models\SalesTransaction::getTopItemsByPeriod($year, $month, 10, $storeId);
        $topConsignmentItems = \App\Models\SalesTransaction::getTopConsignmentItemsByPeriod($year, $month, 10, $storeId);

        $shippingNeedsProcessQuery = \App\Models\SalesTransaction::where('channel', 'ecommerce')
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);
            
        $shippingProcessedQuery = \App\Models\SalesTransaction::where('channel', 'ecommerce')
            ->whereNull('deleted_at')
            ->whereIn('payment_status', ['shipping', 'completed'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        $shippingCanceledQuery = \App\Models\SalesTransaction::where('channel', 'ecommerce')
            ->whereNull('deleted_at')
            ->whereIn('payment_status', ['canceled', 'FAILED', 'EXPIRED'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        if ($storeId) {
            $shippingNeedsProcessQuery->where('store_id', $storeId);
            $shippingProcessedQuery->where('store_id', $storeId);
            $shippingCanceledQuery->where('store_id', $storeId);
        }

        $shippingNeedsProcess = $shippingNeedsProcessQuery->count();
        $shippingProcessed = $shippingProcessedQuery->count();
        $shippingCanceled = $shippingCanceledQuery->count();

        $variantsQuery = \App\Models\ProductDetail::query();
        if ($storeId) {
            $variantsQuery->where('store_id', $storeId);
        }

        $this->data = array_merge($this->data, [
            'monthlyEarning' => $monthlyEarning,
            'prevMonthlyEarning' => $prevMonthlyEarning,
            'todayEarning' => $todayStats['earning'],
            'totalTransactions' => $todayStats['transactions'],
            'currTotal' => $currTotal,
            'prevTotal' => $prevTotal,
            'monthlyEarnings' => $monthlyEarnings,
            'dailyEarnings' => $dailyEarnings,
            'yearlyEarnings' => $yearlyEarnings,
            'topItems' => $topItems,
            'topConsignmentItems' => $topConsignmentItems,
            'shippingNeedsProcess' => $shippingNeedsProcess,
            'shippingProcessed' => $shippingProcessed,
            'shippingCanceled' => $shippingCanceled,
            'totalProductVariants' => $variantsQuery->count(),
            'year' => $year,
            'month' => $month,
            'stores' => $stores,
            'storeId' => $storeId,
            'latestLogins' => \App\Models\User::whereNotNull('last_login_at')
                                ->orderBy('last_login_at', 'desc')
                                ->take(10)
                                ->get(),
        ]);

        return view('home', $this->data);
    }

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }
}
