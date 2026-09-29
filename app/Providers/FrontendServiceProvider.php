<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Wishlist;

class FrontendServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        View::composer('frontend.*', function ($view) {
            $wishlistCount = 0;
            $cartCount = 0;

            if (Auth::guard('customer')->check()) {
                $customerId = Auth::guard('customer')->id();

                $wishlistCount = Wishlist::where('customer_id', $customerId)->count();

                $cartKey = 'cart_' . $customerId;
            } else {
                $cartKey = 'cart_';
            }

            $cart = Session::get($cartKey, []);

            if (is_string($cart)) {
                $cart = json_decode($cart, true) ?? [];
            }
            if (!is_array($cart)) {
                $cart = [];
            }

            $cartCount = count($cart);

            $view->with([
                'wishlistCount' => $wishlistCount,
                'cartCount' => $cartCount,
            ]);
        });
    }
}
