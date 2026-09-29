<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class RefundItem extends Model
{
    use HasFactory, Uuid;

    protected $table = 'refund_items';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'refund_id',
        'sales_transaction_item_id',
        'product_detail_id',
        'qty',
        'price',
        'refund_amount',
    ];

    public function refund()
    {
        return $this->belongsTo(Refund::class, 'refund_id', 'id');
    }

    public function salesTransactionItem()
    {
        return $this->belongsTo(SalesTransactionItem::class, 'sales_transaction_item_id', 'id');
    }

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }
}
