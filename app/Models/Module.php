<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Module extends Model
{
    use Uuid;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name','icon','url','parent','support','only_super','no_delete','table_module','is_generated',
        'created_by','updated_by','restored_by','restored_at','deleted_by','deleted_at',
    ];
}
