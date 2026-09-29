<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use Uuid;
    use HasFactory;
    protected $table      = 'stores';
    protected $primaryKey = 'id';
    public $incrementing  = false;
    public $keyType       = 'string';

    protected $fillable = [
        'id',
        'name',
        'address',
        'ecommerce',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

}
