<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wishlist extends Model
{
    use Uuid;
    use HasFactory;
    protected $table = 'wishlist';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'product_detail_id',
        'customer_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }
}
