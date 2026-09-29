<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;

class HeadAccountMapping extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'head_account_mappings';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'users_id',
        'store_id',
        'outlet_id',
        'key',
        'debit',
        'credit',
        'deleted_at',
    ];

    public function debitAccount()
    {
        return $this->belongsTo(HeadAccount::class, 'debit');
    }

    public function creditAccount()
    {
        return $this->belongsTo(HeadAccount::class, 'credit');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
