<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class FeaturedCategory extends Model
{
    use Uuid;
    use HasFactory;
    protected $table = 'featured_categories';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
'title',
'sub_title',
'link_url',
'content_path',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at'
    ];

    
}