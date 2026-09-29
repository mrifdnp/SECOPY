<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Party extends Model
{
    use Uuid;
    use HasFactory;
    protected $table      = 'parties';
    protected $primaryKey = 'id';
    public $incrementing  = false;
    public $keyType       = 'string';

    protected $fillable = [
        'id',
        'store_id',
        'name',
        'tags',
        'telephone',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
