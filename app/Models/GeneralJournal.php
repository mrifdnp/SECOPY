<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;

class GeneralJournal extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'general_journals';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'date',
        'head_accounts_id',
        'store_id',
        'naration',
        'generated_source',
        'debit',
        'kredit',
        'source_type',
        'source_id',
        'users_id',
        'parent_user_id',
        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

    public function head_account()
    {
        return $this->belongsTo(HeadAccount::class, 'head_accounts_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
