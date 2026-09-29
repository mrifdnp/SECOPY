<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class LogGenerator extends Model
{
    use HasFactory;
    use Uuid;
    protected $table = 'generator_logs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'module','log_data','created_by','updated_by'
    ];
}
