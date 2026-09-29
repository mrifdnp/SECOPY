<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;
use Carbon\Carbon;

class SalesTransaction extends Model
{
    use Uuid;
    use HasFactory;
    protected $table = 'sales_transactions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'store_id',
        'date',
        'invoice_id',
        'customer_id',
        'sub_total',
        'discount_percent',
        'discount_nominal',
        'additional_cost',
        'total',
        'payment_type',
        'channel',
        'payment_status',
        'sale_people_id',
        'xendit_fees',
        'xendit_payment_method',
        'xendit_channel',
        'xendit_url',
        'xendit_metadata',
        'xendit_metapaid',
        'paid_at',
        'courier',
        'amount_paid',
        'change',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
        'resi',
        'xendit_expired_invoice',
        'remark',
    ];

    protected $casts = [
        'courier' => 'array',
    ];

    public function getCourierAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type', 'id');
    }

    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopePaid($query)
    {
        return $query->whereIn('payment_status', ['paid', 'completed', 'shipping']);
    }

    public function scopeForYear($query, $year)
    {
        return $query->whereYear('created_at', $year);
    }

    public function scopeForMonth($query, $month)
    {
        return $query->whereMonth('created_at', $month);
    }

    public static function getMonthlyEarningsData($year, $storeId = null)
    {
        $query = static::forYear($year)
            ->notDeleted()
            ->paid()
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(CAST(total AS NUMERIC)) as total');
            
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->orderByRaw('EXTRACT(MONTH FROM created_at)')
            ->get();
    }

    public static function getDailyEarningsData($year, $month, $storeId = null)
    {
        $query = static::forYear($year)
            ->forMonth($month)
            ->notDeleted()
            ->paid()
            ->selectRaw('EXTRACT(DAY FROM created_at) as day, SUM(CAST(total AS NUMERIC)) as total');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->groupByRaw('EXTRACT(DAY FROM created_at)')
            ->orderByRaw('EXTRACT(DAY FROM created_at)')
            ->get();
    }

    public static function getYearlyEarningsData($storeId = null)
    {
        $query = static::notDeleted()
            ->paid()
            ->selectRaw('EXTRACT(YEAR FROM created_at) as year, SUM(CAST(total AS NUMERIC)) as total');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->groupByRaw('EXTRACT(YEAR FROM created_at)')
            ->orderByRaw('EXTRACT(YEAR FROM created_at)')
            ->get();
    }

    public static function getTotalByPeriod($year, $month = null, $storeId = null)
    {
        $query = static::selectRaw('SUM(CAST(total AS NUMERIC)) as total')
            ->whereYear('created_at', $year)
            ->whereNull('deleted_at')
            ->paid();

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->value('total') ?? 0;
    }

    public static function getTodayStats($storeId = null)
    {
        $earningQuery = static::whereDate('created_at', Carbon::today())
            ->whereNull('deleted_at')
            ->paid()
            ->selectRaw('SUM(CAST(total AS NUMERIC)) as total');
            
        $transactionsQuery = static::whereDate('created_at', Carbon::today())
            ->whereNull('deleted_at');

        if ($storeId) {
            $earningQuery->where('store_id', $storeId);
            $transactionsQuery->where('store_id', $storeId);
        }

        return [
            'earning' => $earningQuery->value('total') ?? 0,
            'transactions' => $transactionsQuery->count()
        ];
    }

    public static function getTopItemsByPeriod($year, $month, $limit = 10, $storeId = null)
    {
        $query = \App\Models\SalesTransactionItem::join('sales_transactions as st', 'sales_transaction_items.sales_transaction_id', '=', 'st.id')
            ->join('product_details as pd', 'sales_transaction_items.product_detail_id', '=', 'pd.id')
            ->join('products as p', 'pd.product_id', '=', 'p.id')
            ->join('variants as v', 'pd.variant_id', '=', 'v.id')
            ->join('colors as c', 'pd.color_id', '=', 'c.id')
            ->select(
                'p.id as product_id',
                'pd.id as product_detail_id',
                'p.title as product_title',
                'pd.price as last_price',
                'v.name as variant_name',
                'c.label as color_label',
                \DB::raw('SUM(sales_transaction_items.qty) as total_qty')
            )
            ->whereYear('st.created_at', $year)
            ->whereMonth('st.created_at', $month)
            ->whereNull('st.deleted_at')
            ->whereIn('st.payment_status', ['paid', 'completed', 'shipping']);

        if ($storeId) {
            $query->where('st.store_id', $storeId);
        }

        return $query->groupBy('pd.id', 'p.title', 'pd.price', 'v.name', 'c.label', 'p.id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    public static function getTopConsignmentItemsByPeriod($year, $month, $limit = 10, $storeId = null)
    {
        $cacheKey = "top_consignment_materials_{$year}_{$month}_{$limit}_{$storeId}";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($year, $month, $limit, $storeId) {
            $query = \App\Models\SalesTransactionItem::join('sales_transactions as st', 'sales_transaction_items.sales_transaction_id', '=', 'st.id')
                ->join('product_details as pd', 'sales_transaction_items.product_detail_id', '=', 'pd.id')
                ->join('products as p', 'pd.product_id', '=', 'p.id')
                ->join('materials as m', 'p.material_id', '=', 'm.id')
                ->select(
                    'm.id as material_id',
                    'm.title as material_title',
                    \DB::raw('SUM(sales_transaction_items.qty) as total_qty')
                )
                ->whereYear('st.created_at', $year)
                ->whereMonth('st.created_at', $month)
                ->whereNull('st.deleted_at')
                ->where('m.is_consignment', 1)
                ->whereIn('st.payment_status', ['paid', 'completed', 'shipping']);

            if ($storeId) {
                $query->where('st.store_id', $storeId);
            }

            return $query->groupBy('m.id', 'm.title')
                ->orderByDesc('total_qty')
                ->limit($limit)
                ->get();
        });
    }

    public function items()
    {
        return $this->hasMany(SalesTransactionItem::class, 'sales_transaction_id', 'id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'sales_transaction_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

}
