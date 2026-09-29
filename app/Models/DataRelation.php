<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class DataRelation extends Model
{
    use Uuid;

    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'primary_table',
        'primary_id',
        'relation_table',
        'relation_id',
        'on_delete',
    ];
}
