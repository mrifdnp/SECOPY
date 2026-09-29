<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Role extends Model
{
    use Uuid;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name','guard_name','description','modules_schema','only_super',
        'no_delete','created_by','updated_by','restored_by','restored_at',
        'deleted_by','deleted_at','theme_setting'
    ];
}
