<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class SalePerson extends Model
{
    use Uuid;
    use HasFactory;
    protected $table = 'sale_people';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
'name',
'note',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at'
    ];

    
}