<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class TopPage extends Model
{
    use Uuid;
    use HasFactory;
    protected $table = 'top_pages';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
'title',
'icon',
'content',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at'
    ];

    
}