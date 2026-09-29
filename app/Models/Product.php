<?php
namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use Uuid;
    use HasFactory;
    protected $table      = 'products';
    protected $primaryKey = 'id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::forget('product_summary_stats');
        });

        static::deleted(function ($model) {
            Cache::forget('product_summary_stats');
        });
    }

    protected $fillable = [
        'id',
        'title',
        'category_id',
        'top_pick',
        'content_paths',
        'show_in_ecommerce',

        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
        'material_id',
    ];

    protected $casts = [
        'content_paths' => 'array',
        'top_pick'      => 'boolean',
    ];

    protected $appends = ['main_pic', 'hover_pic', 'pictures'];

    public function categories()
    {
        return $this->belongsTo('App\Models\Category', 'category_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class, 'product_id');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(Color::class, 'product_id');
    }

    public function productDetail(): HasMany
    {
        return $this->hasMany(ProductDetail::class, 'product_id');
    }

    /**
     * Get price from product detail
     */
    public function getPriceProduct(): ?float
    {
        return $this->productDetail()
            ->selectRaw('(price + COALESCE(additional_price, 0)) as total_price')
            ->orderBy('total_price', 'asc')
            ->first()?->total_price;
    }

    /**
     * Get product detail by color and product id
     */
    public function getProductDetailByColor($color_id, $product_id, $store_id)
    {

        return $this->productDetail()
            ->where('color_id', $color_id)
            ->where('product_id', $product_id)
            ->where('store_id', $store_id)
            ->with('variant')
            ->orderBy('price', 'asc')
            ->get();
    }

    /**
     * Getter untuk main_pic
     */
    protected function mainPic(): Attribute
    {
        return Attribute::get(fn() =>
            $this->makeFullUrl($this->getFromContentPaths('main_pic')['path'] ?? null)
        );
    }

    /**
     * Getter untuk hover_pic
     */
    protected function hoverPic(): Attribute
    {
        return Attribute::get(fn() =>
            $this->makeFullUrl($this->getFromContentPaths('hover_pic')['path'] ?? null)
        );
    }

    /**
     * Getter untuk pictures (pic_1 - pic_8)
     */
    protected function pictures(): Attribute
    {
        return Attribute::get(function () {
            // Skeleton pic_1 - pic_8
            $pictures = collect(range(1, 8))->map(fn($i) => [
                'name' => "pic_$i",
                'path' => null,
            ]);

            // Kalau ada data di content_paths, replace path yang match
            if (is_array($this->content_paths)) {
                $pictures = $pictures->map(function ($pic) {
                    $found = collect($this->content_paths)->firstWhere('name', $pic['name']);
                    if ($found) {
                        $pic['path'] = $this->makeFullUrl($found['path']);
                    }
                    return $pic;
                });
            }

            return $pictures->values()->toArray();
        });
    }

    /**
     * Helper ambil data dari content_paths
     */
    private function getFromContentPaths(string $name): ?array
    {
        if (! is_array($this->content_paths)) {
            return null;
        }

        return collect($this->content_paths)->firstWhere('name', $name);
    }

    /**
     * Helper untuk generate full url
     */
    private function makeFullUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return env('APP_URL') . Storage::url($path);
    }

    public function getSeoUrlParamAttribute()
    {
        return \Illuminate\Support\Str::slug($this->title) . '-p.' . $this->id;
    }

    public static function importProductRules(): array
    {
        return [
            'nama'                => 'Wajib diisi, format teks',
            'main_kategori'       => 'Wajib diisi, pilih dari daftar main kategori yang ada dan harus sama persis',
            'kategori'            => 'Wajib diisi, pilih dari daftar kategori yang ada dan harus sama persis',
            'material'            => 'Opsional, pilih dari daftar material yang ada dan harus sama persis',
            'tampil_di_ecommerce' => 'Opsional, isi dengan Ya atau Tidak, jika dikosongkan maka otomatis bernilai Tidak',
            'top_pick'            => 'Opsional, isi dengan Ya atau Tidak, jika dikosongkan maka otomatis bernilai Tidak',
            'varian'       => 'Wajib diisi minimal satu, jika lebih dari satu maka pisahkan dengan koma, misal: Kecil,Sedang,Besar',
            'hexa_warna'   => 'Wajib diisi minimal satu, jika lebih dari satu maka pisahkan dengan koma dan kode hexa harus valid, misal: #f3f3f3,#111111',
            'label_warna'  => 'Wajib diisi, jumlahnya sesuai dengan hexa_warna yang diinput, misal: Putih,Hitam,Biru',
            'gambar_utama' => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
            'gambar_hover' => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
            'gambar_1'     => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
            'gambar_2'     => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
            'gambar_3'     => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
            'gambar_4'     => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
            'gambar_5'     => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
            'gambar_6'     => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
            'gambar_7'     => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
            'gambar_8'     => 'Opsional, sesuaikan dengan nama file gambar yang sudah diupload seblumnya',
        ];
    }

    public static function importProductDetailRules($store_id = null): array
    {
        $rules = [
            'id_produk'        => 'Sudah terisi otomatis dari sistem, jangan diubah',
            'id_produk_detail' => 'Sudah terisi otomatis dari sistem, jangan diubah',
            'nama'             => 'Sudah terisi otomatis dari sistem, jangan diubah',
            'varian'           => 'Sudah terisi otomatis dari sistem, jangan diubah',
            'warna'            => 'Sudah terisi otomatis dari sistem, jangan diubah',
        ];

        if ($store_id === 'all') {
            $stores = \App\Models\Store::whereNull('deleted_at')->get();
            foreach ($stores as $store) {
                $rules['sku ' . $store->name] = 'Opsional, harus unik per toko jika diisi';
                $rules['harga ' . $store->name] = 'Wajib diisi, format angka, minimal 0';
                $rules['harga tambahan ' . $store->name] = 'Opsional, format angka, minimal 0';
                $rules['cogm ' . $store->name] = 'Opsional, format angka, minimal 0';
                $rules['stok ' . $store->name] = 'Wajib diisi, format angka, minimal 0';
                $rules['berat ' . $store->name] = 'Wajib diisi, format angka, minimal 0';
            }
        } else {
            $rules['stok']             = 'Wajib diisi, format angka, minimal 0';
            $rules['harga']            = 'Wajib diisi, format angka, minimal 0';
            $rules['harga tambahan']   = 'Opsional, format angka, minimal 0';
            $rules['cogm']             = 'Opsional, format angka, minimal 0';
            $rules['berat']            = 'Wajib diisi, format angka, minimal 0';
            $rules['sku']              = 'Opsional, harus unik per toko jika diisi';
        }

        $rules['deskripsi_produk'] = 'Opsional, format teks';
        $rules['dimensi_produk']   = 'Opsional, format teks';

        return $rules;
    }

    public function scopeWithPricesAndDiscounts($query)
    {
        return $query->selectRaw('products.*,
                pd.min_price,
                d.max_discount,
                d.max_discount_type,
                (pd.min_price - COALESCE(d.discount_value, 0)) as final_price
            ')
            ->join(DB::raw('LATERAL (
                SELECT MIN(price + COALESCE(additional_price, 0)) as min_price
                FROM product_details
                WHERE product_details.product_id = products.id
                AND product_details.price >= 1
            ) pd'), DB::raw('true'), DB::raw('true'))
            ->leftJoin(DB::raw('LATERAL (
                SELECT
                    d.value as max_discount,
                    d.type as max_discount_type,
                    CASE
                        WHEN d.type = \'nominal\' THEN d.value
                        WHEN d.type = \'percent\' THEN ((pd2.price + COALESCE(pd2.additional_price, 0)) * d.value / 100)
                        ELSE 0
                    END as discount_value
                FROM product_details pd2
                JOIN discount_products dp ON dp.product_detail_id = pd2.id
                JOIN discounts d ON d.id = dp.discount_id
                WHERE pd2.product_id = products.id
                AND pd2.price >= 1
                AND d.deleted_at IS NULL
                AND (d.start_date IS NULL OR d.start_date <= NOW())
                AND (d.end_date   IS NULL OR d.end_date   >= NOW())
                ORDER BY discount_value DESC
                LIMIT 1
            ) d'), DB::raw('true'), DB::raw('true'))
            ->whereNotNull('pd.min_price');
    }
}
