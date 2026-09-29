<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainCategory extends Model
{
    use Uuid;
    use HasFactory;
    protected $table      = 'main_categories';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'sub_title',
        'link_txt',
        'link_url',
        'picture',
        'slug',
        'meta_title',
        'meta_description',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class, 'main_categories_id', 'id');
    }
    
}
