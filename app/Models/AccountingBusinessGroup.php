<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;

class AccountingBusinessGroup extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'accounting_business_groups';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'parent_user_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'restored_by',
        'restored_at',
    ];
}
