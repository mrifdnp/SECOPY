<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LowStockNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products with low stock and send email notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thresholdSetting = \App\Models\Setting::where('key', 'low_stock_threshold')->first();
        $threshold = $thresholdSetting ? (int)$thresholdSetting->value : 5; // Default 5

        $emailSetting = \App\Models\Setting::where('key', 'low_stock_email')->first();
        $adminEmail = $emailSetting ? $emailSetting->value : env('MAIL_TO_ADDRESS', 'secobysekenliving.adm@gmail.com');

        $lowStockProducts = \App\Models\ProductDetail::with(['product', 'variant', 'color'])
            ->where('qty', '<=', $threshold)
            ->get();

        if ($lowStockProducts->count() > 0) {
            \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\LowStockMail($lowStockProducts));
            $this->info("Low stock email sent to {$adminEmail} for {$lowStockProducts->count()} products.");
        } else {
            $this->info('No low stock products found.');
        }
    }
}
