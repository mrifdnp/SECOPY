<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use Uuid;
    use SoftDeletes;

    protected $table = 'materials';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'is_consignment',
        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'is_consignment' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'material_id');
    }
}
