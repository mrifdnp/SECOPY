<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Courier extends Model
{
    use Uuid;
    use HasFactory;
    protected $table = 'couriers';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
'code',
'courier',
'status',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at'
    ];

    
}