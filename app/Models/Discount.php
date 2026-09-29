<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use Uuid;
    use HasFactory;
    protected $table      = 'discounts';
    protected $primaryKey = 'id';
    public $incrementing  = false;
    public $keyType       = 'string';

    protected $fillable = [
        'id',
        'name',
        'type',
        'start_date',
        'end_date',
        'value',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

    public function scopeById(Builder $query, string $id): Builder
    {
        return $query->where('discounts.id', $id);
    }

    public function products()
    {
        return $this->belongsToMany(ProductDetail::class, 'discount_products', 'discount_id', 'product_detail_id');
    }
}
