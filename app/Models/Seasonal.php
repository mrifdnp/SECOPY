<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seasonal extends Model
{
    use Uuid;
    use HasFactory;

    protected $table      = 'seasonals';
    protected $primaryKey = 'id';
    public $incrementing  = false;
    public $keyType       = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'description',
        'hover_description',
        'collection_title',
        'collection_subtitle',
        'button_text',
        'button_link',
        'banner_image',
        'hover_image',
        'start_date',
        'end_date',
        'is_active',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * Scope untuk seasonal yang sedang aktif (dalam rentang tanggal dan is_active = true)
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();
        return $query->whereNull('deleted_at')
            ->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    /**
     * Relasi many-to-many ke Product via tabel seasonal_products
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'seasonal_products', 'seasonal_id', 'product_id')
            ->withPivot('sort_order')
            ->orderBy('seasonal_products.sort_order', 'asc')
            ->withTimestamps();
    }
}
