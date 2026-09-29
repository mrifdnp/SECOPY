<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;

class Refund extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'refunds';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'sales_transaction_id',
        'refund_invoice_id',
        'refund_date',
        'total_refund',
        'reason',
        'store_id',
        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(RefundItem::class, 'refund_id', 'id');
    }
}
