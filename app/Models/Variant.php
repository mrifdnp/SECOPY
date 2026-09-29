<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use SoftDeletes, Uuid;

    protected $primaryKey = 'id';
    public $incrementing  = false;
    public $keyType       = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'json_description' => 'array',
    ];

    public function product_detail(): HasOne
    {
        return $this->hasOne(ProductDetail::class, 'variant_id', 'id');
    }
}
