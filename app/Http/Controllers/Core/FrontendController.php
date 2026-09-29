<?php

namespace App\Http\Controllers\Core;

use App\Models\MainCategory;
use App\Models\Seasonal;
use App\Models\TopPage;
use App\Models\BottomPage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Wishlist;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class FrontendController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $data = [];

    public function __construct()
    {
        $this->data['top_page'] = TopPage::whereNull('deleted_at')->get();
        $this->data['navigation'] = MainCategory::with('categories')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $this->data['help'] = BottomPage::whereNull('deleted_at')
            ->where('parent', 'help')
            ->get();
        $this->data['about'] = BottomPage::whereNull('deleted_at')
            ->where('parent', 'aboutus')
            ->get();
        $this->data['service'] = BottomPage::whereNull('deleted_at')
            ->where('parent', 'service')
            ->get();

        $this->data['footer'] = BottomPage::whereNull('deleted_at')
            ->where('parent', 'footer')
            ->get();

        $youtube = Setting::where("key", "youtube")->whereNull('deleted_at')->first();
        $instagram = Setting::where("key", "instagram")->whereNull('deleted_at')->first();

        $this->data['youtube_url'] = $youtube->value ?? null;
        $this->data['instagram_url'] = $instagram->value ?? null;

        $this->data['address_title'] = Setting::where("key", "address_title")->whereNull('deleted_at')->first()->value ?? "Seken | Beauty in imperfections";
        $this->data['address'] = Setting::where("key", "address")->whereNull('deleted_at')->first()->value ?? "Jl. Besi-Jangkang Km.3, Sukoharjo, Ngaglik, Sleman, Indonesia";
        $this->data['address_phone'] = Setting::where("key", "address_phone")->whereNull('deleted_at')->first()->value ?? "0274-4293533";

        $this->data['active_seasonals'] = Seasonal::active()
            ->orderBy('start_date', 'asc')
            ->get();

    }
}
