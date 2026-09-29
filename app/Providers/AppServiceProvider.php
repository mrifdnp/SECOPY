<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
// use \Maatwebsite\Excel\Sheet;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {        
        Blade::directive('format_currency', function ($amount) {
            return "Rp. <?php echo number_format($amount, 0, ',', '.'); ?>";
        });
    }
}
