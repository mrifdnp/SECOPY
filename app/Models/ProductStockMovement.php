<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStockMovement extends Model
{
    use SoftDeletes,Uuid;
    use HasFactory;
    protected $table = 'product_stock_movements';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $keyType       = 'string';
    protected $guarded = ['id'];

    protected $fillable = [
        'id',
        'product_detail_id',
        'qty',
        'last_stock',
        'remark',
        'stockopname_id',
        'created_at',
        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];


    public function product_detail(): BelongsTo
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }
}