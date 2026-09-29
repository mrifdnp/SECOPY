<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Inspiration extends Model
{
    use Uuid;
    use HasFactory;
    protected $table = 'inspirations';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
'title',
'link_txt',
'link_url',
'picture',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at'
    ];

    
}