<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Signature extends Model
{
    use Uuid;
    use HasFactory;
    protected $table = 'signatures';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
'category',
'title',
'link_url',
'picture',
'picture_hover',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at'
    ];

    
}