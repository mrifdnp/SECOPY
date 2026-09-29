<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;

class HeadAccount extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'head_accounts';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'nature',
        'type',
        'expense_type',
        'kas',
        'aktifitas',
        'sn',
        'pos',
        'business_type',
        'users_id',
        'parent_user_id',
        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function general_journals()
    {
        return $this->hasMany(GeneralJournal::class, 'head_accounts_id');
    }
}
