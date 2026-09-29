<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use Uuid;
    use HasFactory;
    protected $table      = 'categories';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'main_categories_id',
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

    

    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class, 'main_categories_id', 'id');
    }

}
