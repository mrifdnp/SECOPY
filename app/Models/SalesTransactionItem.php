<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class SalesTransactionItem extends Model
{
    use Uuid;
    use HasFactory;
    
    protected $table = 'sales_transaction_items';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'sales_transaction_id',
        'product_detail_id',
        'price',
        'discount_percent',
        'discount_nominal',
        'final_price',
        'product_detail',
        'qty',
        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at'
    ];

    protected $casts = [
        'product_detail' => 'array',
    ];

    public function getProductDetailAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Relationship to SalesTransaction
     */
    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id', 'id');
    }

    /**
     * Relationship to ProductDetail
     */
    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id')->withTrashed();
    }

    /**
     * Scope for non-deleted records
     */
    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get the total amount for this item (final_price * qty)
     */
    public function getTotalAmountAttribute()
    {
        return $this->final_price * $this->qty;
    }

    /**
     * Get discount amount for this item
     */
    public function getDiscountAmountAttribute()
    {
        if ($this->discount_nominal > 0) {
            return $this->discount_nominal;
        }
        
        if ($this->discount_percent > 0) {
            return ($this->price * $this->discount_percent / 100);
        }
        
        return 0;
    }
}