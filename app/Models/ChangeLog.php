<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class ChangeLog extends Model
{
    use HasFactory;
    use Uuid;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'change_log';

    protected $fillable = [
        'type',
        'created_date',
        'version',
        'description',
    ];
}

