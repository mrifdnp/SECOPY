<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Stockopname extends Model
{
    use Uuid;
    use HasFactory;
    protected $table = 'stockopnames';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
'title',
'date_start',
'date_end',
'store_id',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at'
    ];

    

public function stores(){ 
		return $this->belongsTo('App\Models\Store', 'store_id');
}
}