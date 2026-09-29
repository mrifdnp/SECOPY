<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Uuid;

class InventoryMovement extends Model
{
    use Uuid;
    use HasFactory;
    protected $table      = 'inventory_movements';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'source_party_id',
        'destination_party_id',
        'movement_date',
        'movement_type',
        'remark',
        'status',
        'sale_people_id',
        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
    ];

    public function sourceParty()
    {
        return $this->belongsTo(Party::class, 'source_party_id');
    }

    public function destinationParty()
    {
        return $this->belongsTo(Party::class, 'destination_party_id');
    }

    public function salePerson()
    {
        return $this->belongsTo(SalePerson::class, 'sale_people_id');
    }
}
