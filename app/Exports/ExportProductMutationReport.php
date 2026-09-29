<?php
namespace App\Exports;

use App\Models\ProductStockMovement;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportProductMutationReport implements FromCollection, WithHeadings, WithMapping
{
    protected $from         = null;
    protected $to           = null;
    protected $mutationType = null;
    protected $storeIds     = [];

    public function __construct($from = null, $to = null, $mutationType = null, $storeIds = [])
    {
        $this->from         = $from;
        $this->to           = $to;
        $this->mutationType = $mutationType;
        $this->storeIds     = $storeIds;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ProductStockMovement::with([
            'product_detail',
            'product_detail.product',
            'product_detail.variant',
            'product_detail.color',
            'product_detail.store',
        ])
            ->whereHas('product_detail.store', function ($query) {
                $query->whereIn('id', $this->storeIds);
            })
            ->when($this->from, function ($query) {
                $query->where('created_at', '>=', $this->from . ' 00:00:00');
            })
            ->when($this->to, function ($query) {
                $query->where('created_at', '<=', $this->to . ' 23:59:59');
            })
            ->when($this->mutationType, function ($query) {
                if ($this->mutationType === 'in') {
                    $query->where('qty', '>', 0);
                } elseif ($this->mutationType === 'out') {
                    $query->where('qty', '<', 0);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Tipe',
            'Jumlah',
            'Sisa Stok',
            'Nama Produk',
            'Varian',
            'Warna',
            'Toko',
            'Catatan',
        ];
    }

    public function map($stockMovement): array
    {
        return [
            $stockMovement->created_at ? Carbon::parse($stockMovement->created_at)->translatedFormat('d F Y H:i') : '-',
            $stockMovement->qty > 0 ? 'Masuk' : 'Keluar',
            $stockMovement->qty,
            $stockMovement->last_stock ?? 0,
            $stockMovement->product_detail?->product?->title ?? '-',
            $stockMovement->product_detail?->variant?->name ?? '-',
            $stockMovement->product_detail?->color?->label ?? '-',
            $stockMovement->product_detail?->store?->name ?? '-',
            $stockMovement->remark ?? '-',
        ];
    }
}
