<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProductDetail extends Model
{
    use SoftDeletes, Uuid;

    protected $primaryKey = 'id';
    public $incrementing  = false;
    public $keyType       = 'string';

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::forget('product_summary_stats');
        });

        static::deleted(function ($model) {
            Cache::forget('product_summary_stats');
        });
    }

    protected $guarded = ['id'];

    public function color(): HasOne
    {
        return $this->hasOne(Color::class, 'id', 'color_id');
    }

    public function variant(): HasOne
    {
        return $this->hasOne(Variant::class, 'id', 'variant_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function product(): BelongsTo
    {
        $relation = $this->belongsTo(Product::class, 'product_id', 'id');
        $relation->withPricesAndDiscounts();
        return $relation;
    }

    public function productWithoutScope(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_products', 'product_detail_id', 'discount_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'product_detail_id', 'id');
    }

    public function scopeWithPricesAndDiscounts($query)
    {
        return $query
            ->addSelect('product_details.*')
            ->leftJoin(DB::raw('LATERAL (
                SELECT 
                    d.value as max_discount,
                    d.type as max_discount_type,
                    CASE 
                        WHEN d.type = \'nominal\' THEN d.value
                        WHEN d.type = \'percent\' THEN ((product_details.price + COALESCE(product_details.additional_price, 0)) * d.value / 100)
                        ELSE 0
                    END as discount_value
                FROM discount_products dp
                JOIN discounts d ON d.id = dp.discount_id
                WHERE dp.product_detail_id = product_details.id
                    AND d.deleted_at IS NULL
                    AND (d.start_date IS NULL OR d.start_date <= NOW())
                    AND (d.end_date   IS NULL OR d.end_date   >= NOW())
                ORDER BY discount_value DESC
                LIMIT 1
            ) disc'), DB::raw('true'), DB::raw('true'))
            ->addSelect([
                'disc.max_discount',
                'disc.max_discount_type',
                DB::raw('((product_details.price + COALESCE(product_details.additional_price, 0)) - COALESCE(disc.discount_value, 0)) as final_price')
            ]);
    }

    // Diskon terbaik yang masih aktif dan tidak soft deleted
    public function scopeWithBestActiveDiscountAndFinalPrice($query)
    {
        return $query
            ->addSelect('product_details.*')
            ->whereNull('product_details.deleted_at')
            ->leftJoin(
                DB::raw(
                    "LATERAL (
                        SELECT 
                            d.value,
                            d.type,
                            CASE 
                                WHEN d.type = 'nominal' THEN d.value
                                WHEN d.type = 'percent' THEN ((product_details.price + COALESCE(product_details.additional_price, 0)) * d.value / 100)
                                ELSE 0
                            END AS discount_value,
                            CASE WHEN d.type = 'percent' THEN d.value ELSE NULL END AS raw_percent_value,
                            CASE WHEN d.type = 'nominal' THEN d.value ELSE NULL END AS raw_nominal_value
                        FROM discount_products dp
                        JOIN discounts d ON d.id = dp.discount_id
                        WHERE dp.product_detail_id = product_details.id
                            AND d.deleted_at IS NULL
                            AND (d.start_date IS NULL OR d.start_date <= NOW())
                            AND (d.end_date   IS NULL OR d.end_date   >= NOW())
                        ORDER BY discount_value DESC
                        LIMIT 1
                    ) disc"
                ),
                DB::raw('true'),
                DB::raw('true')
            )
            ->addSelect([
                'disc.discount_value',
                'disc.raw_percent_value',
                'disc.raw_nominal_value',
                'disc.type AS best_discount_type',
                DB::raw('((product_details.price + COALESCE(product_details.additional_price, 0)) - COALESCE(disc.discount_value, 0)) AS final_price'),
            ]);
    }

}
