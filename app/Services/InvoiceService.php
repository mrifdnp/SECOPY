<?php
namespace App\Services;

use App\Models\SalesTransaction;

class InvoiceService
{
    public function generateInvoiceId(): string
    {
        $date = now()->format('Ymd');
        $countToday = SalesTransaction::whereDate('created_at', now()->toDateString())->count() + 1;
        $number = str_pad($countToday, 5, '0', STR_PAD_LEFT);

        return "INV-{$date}-{$number}";
    }
}
