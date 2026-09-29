<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Color extends Model
{
    use SoftDeletes, Uuid;

    protected $primaryKey = 'id';
    public $incrementing  = false;
    public $keyType       = 'string';

    protected $guarded = ['id'];
}
