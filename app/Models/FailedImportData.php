<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;

class FailedImportData extends Model
{
    use Uuid;

    protected $table     = 'failed_import_data';
    public $incrementing = false;
    protected $keyType   = 'string';
    protected $fillable  = [
        'id',
        'import_status_id',
        'row',
        'replace_action',
    ];

    public function importStatus()
    {
        return $this->belongsTo(ImportStatus::class, 'import_status_id');
    }
}
