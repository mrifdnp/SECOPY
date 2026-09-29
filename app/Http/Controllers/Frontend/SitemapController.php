<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\MainCategory;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $products = Product::where('show_in_ecommerce', true)->get();
        $mainCategories = MainCategory::whereNull('deleted_at')->get();
        $categories = Category::with('mainCategory')->whereNull('deleted_at')->get();

        return response()->view('frontend.sitemap', [
            'products' => $products,
            'mainCategories' => $mainCategories,
            'categories' => $categories
        ])->header('Content-Type', 'text/xml');
    }
}
