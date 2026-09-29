<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class RolePrivilege extends Model
{
    use Uuid;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}
