<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AssetInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asset_inventories';

    protected $fillable = [
        'parent_user_id',
        'store_id',
        'code',
        'category',
        'name',
        'merk',
        'tgl',
        'jumlah_unit',
        'harga_per_unit',
        'umur_manfaat',
        'depreciation_year',
        'masa',
        'nilai_residu',
        'last_condition',
        'purchase_price',
        'image',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getNilaiPerolehanAttribute()
    {
        if (!empty($this->purchase_price) && (float) $this->purchase_price > 0) {
            return (float) $this->purchase_price;
        }
        return (float) ($this->jumlah_unit ?? 1) * (float) ($this->harga_per_unit ?? 0);
    }

    public function getPresentaseSusutAttribute()
    {
        $umur = (float) ($this->umur_manfaat ?? 0);
        return $umur > 0 ? (1 / $umur) : 0;
    }

    public function getBatasSusutAttribute()
    {
        $umur = (float) ($this->umur_manfaat ?? 0);
        return $umur > 0 ? (int) round($umur * 12) : (int) ($this->masa ?? 0);
    }

    public function getJumlahSusutAttribute()
    {
        if (!$this->tgl) return 0;
        $tglBeli = \Carbon\Carbon::parse($this->tgl);
        $now = \Carbon\Carbon::now();
        $diffMonths = ($now->year - $tglBeli->year) * 12 + ($now->month - $tglBeli->month);
        return max(0, $diffMonths);
    }

    public function getPenyusutanPerBulanAttribute()
    {
        $M = $this->jumlah_susut;
        $N = $this->batas_susut;
        $K = $this->nilai_perolehan;
        $L = $this->presentase_susut;

        if ($M <= 0 || $N <= 0) return 0;
        $tarifBulan = ($K * $L) / 12;
        if ($M == $N) return max(0, $tarifBulan - 1);
        if ($M > $N) return 0;
        return $tarifBulan;
    }

    public function getAkumulasiPenyusutanAttribute()
    {
        $M = $this->jumlah_susut;
        $N = $this->batas_susut;
        $K = $this->nilai_perolehan;
        $L = $this->presentase_susut;

        if ($M <= 0 || $N <= 0) return 0;
        $tarifBulan = ($K * $L) / 12;
        if ($M == $N) return max(0, ($M * $tarifBulan) - 1);
        if ($M > $N) return max(0, ($N * $tarifBulan) - 1);
        return $M * $tarifBulan;
    }

    public function getNilaiBukuAttribute()
    {
        return max(0, $this->nilai_perolehan - $this->akumulasi_penyusutan);
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public $incrementing = false;
    protected $keyType = 'uuid';

    // Automatically generate a UUID when creating a new model instance
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
