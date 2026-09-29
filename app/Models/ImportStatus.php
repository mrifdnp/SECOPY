<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;

class ImportStatus extends Model
{
    use Uuid;

    protected $table     = 'import_statuses';
    public $incrementing = false;
    protected $keyType   = 'string';
    protected $fillable  = [
        'id',
        'task',
        'status',
        'success_row',
        'failed_row',
        'total_row',
        'source_url',
    ];
}
