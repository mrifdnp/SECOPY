<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class UserPrivilege extends Model
{
    use Uuid;

    protected $primaryKey = 'id';
    public $incrementing = false;
}
