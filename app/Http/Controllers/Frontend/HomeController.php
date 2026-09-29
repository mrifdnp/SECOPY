<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Core\FrontendController;
use App\Models\FeaturedCategory;
use App\Models\Inspiration;
use App\Models\Signature;
use App\Models\TopPage;
use Illuminate\Http\Request;
use App\Models\Hero;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Store;
use App\Models\MainCategory;
use App\Models\Category;
use App\Models\Setting;
use App\Models\BottomPage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use App\Models\Wishlist;
use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class HomeController extends FrontendController
{
    public function index()
    {
        $this->data['signature'] = Signature::whereNull('deleted_at')
            ->orderBy('created_at', 'asc')
            ->limit(6)
            ->get();

        $this->data['featuredCategory'] = FeaturedCategory::whereNull('deleted_at')
            ->orderBy('created_at', 'asc')
            ->limit(6)
            ->get();

        $this->data['products'] = Product::withPricesAndDiscounts()
            ->whereNull('products.deleted_at')
            ->where('products.show_in_ecommerce', true)
            ->where('products.top_pick', true)
            ->orderBy('products.created_at', 'asc')
            ->limit(4)
            ->get();

        $this->data['inspirations'] = Inspiration::whereNull('deleted_at')
            ->orderBy('created_at', 'asc')
            ->limit(3)
            ->get();

        $this->data['hero'] = Hero::whereNull('deleted_at')
            ->where('status', 1)
            ->first();

        $this->data['setting'] = Setting::whereNull('deleted_at')
            ->where('key', 'video_footer')
            ->first();

        return view('frontend.pages.index', $this->data);
    }

    public function seasonal($slug)
    {
        $seasonal = \App\Models\Seasonal::where('slug', $slug)
            ->active()
            ->firstOrFail();
            
        $this->data['seasonal'] = $seasonal;
        
        // Dapatkan produk-produk dari seasonal ini
        // Kita bisa ambil dengan relasi products yang sudah dibuat di model Seasonal
        $query = $seasonal->products()->withPricesAndDiscounts()
            ->whereNull('products.deleted_at')
            ->where('products.show_in_ecommerce', true);
            
        // Jika ada filter (optional, mirip dengan method product)
        $request = request();
        switch ($request->get('filter')) {
            case 'newest':
                $query->orderBy('products.created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('products.created_at', 'asc');
                break;
            case 'price-low':
                $query->orderBy('min_price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('min_price', 'desc');
                break;
            case 'discount-high':
                $query->orderBy('max_discount', 'desc');
                break;
            case 'discount-low':
                $query->orderBy('max_discount', 'asc');
                break;
            default:
                // Urutkan berdasarkan sort_order dari pivot
                $query->orderBy('seasonal_products.sort_order', 'asc');
                break;
        }

        $this->data['products'] = $query->paginate(18)->appends($request->query());
        $this->data['products_count'] = $query->count();

        return view('frontend.pages.seasonal', $this->data);
    }

    public function topPages($_, $id)
    {
        $this->data['page'] = TopPage::where('id', $id)->first();
        $this->data['nav_page'] = TopPage::all();

        return view('frontend.pages.top.index', $this->data);
    }

    public function bottomPages($_, $id)
    {
        $this->data['page'] = BottomPage::where('id', $id)->first();
        $this->data['nav_page'] = BottomPage::where('parent', $this->data['page']['parent'])->whereNull('deleted_at')->get();

        return view('frontend.pages.bottom.index', $this->data);
    }

    public function redirectOldCategory($id)
    {
        $category = Category::with('mainCategory')->findOrFail($id);
        return redirect(route('frontend.product', [
            'main_slug' => $category->mainCategory->slug,
            'sub_slug' => $category->slug
        ]), 301);
    }

    public function redirectOldMainCategory($id)
    {
        $mainCategory = MainCategory::findOrFail($id);
        return redirect(route('frontend.productByMainCategory', [
            'main_slug' => $mainCategory->slug
        ]), 301);
    }

    public function product($main_slug, $sub_slug, Request $request)
    {
        $mainCategory = MainCategory::where('slug', $main_slug)->firstOrFail();
        $category = Category::where('slug', $sub_slug)->where('main_categories_id', $mainCategory->id)->firstOrFail();
        $id = $category->id;

        $query = Product::withPricesAndDiscounts()
            ->where('products.category_id', $id)
            ->where('products.show_in_ecommerce', true);

        switch ($request->get('filter')) {
            case 'newest':
                $query->orderBy('products.created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('products.created_at', 'asc');
                break;
            case 'price-low':
                $query->orderBy('min_price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('min_price', 'desc');
                break;
            case 'discount-high':
                $query->orderBy('max_discount', 'desc');
                break;
            case 'discount-low':
                $query->orderBy('max_discount', 'asc');
                break;
            default:
                $query->latest('products.created_at');
                break;
        }

        $this->data['products'] = $query->paginate(18)->appends($request->query());
        $this->data['products_count'] = $query->count();
        $this->data['category'] = $category;

        return view('frontend.pages.product.index', $this->data);
    }

    public function productByMainCategory($main_slug, Request $request)
    {
        $mainCategory = MainCategory::where('slug', $main_slug)->firstOrFail();
        $id = $mainCategory->id;

        $query = Product::withPricesAndDiscounts()
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('main_categories', 'categories.main_categories_id', '=', 'main_categories.id')
            ->where('main_categories.id', $id)
            ->where('products.show_in_ecommerce', true);

        switch ($request->get('filter')) {
            case 'newest':
                $query->orderBy('products.created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('products.created_at', 'asc');
                break;
            case 'price-low':
                $query->orderBy('min_price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('min_price', 'desc');
                break;
            case 'discount-high':
                $query->orderBy('max_discount', 'desc');
                break;
            case 'discount-low':
                $query->orderBy('max_discount', 'asc');
                break;
            default:
                $query->latest('products.created_at');
                break;
        }

        $this->data['products'] = $query->paginate(18)->appends($request->query());

        $this->data['products_count'] = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->join('main_categories', 'categories.main_categories_id', '=', 'main_categories.id')
            ->where('main_categories.id', $id)
            ->where('products.show_in_ecommerce', true)
            ->count();

        $this->data['category'] = $mainCategory;

        return view('frontend.pages.product.bymaincategory', $this->data);
    }

    public function newestProduct(Request $request)
    {
        $query = Product::withPricesAndDiscounts()
            ->orderBy('created_at', 'desc')
            ->where('products.show_in_ecommerce', true);

        $this->data['products'] = $query->paginate(18)->appends($request->query());
        $this->data['products_count'] = Product::where('show_in_ecommerce', true)->count();

        return view('frontend.pages.product.newest', $this->data);
    }

    public function searchProduct(Request $request)
    {
        $keyword = $request->get('search');

        $query = Product::withPricesAndDiscounts()->where('products.show_in_ecommerce', true);

        if ($keyword) {
            $query->where('products.title', 'ILIKE', '%' . $keyword . '%');
        }

        $this->data['products'] = $query->paginate(18)->appends($request->query());
        $this->data['products_count'] = $query->count();

        return view('frontend.pages.product.newest', $this->data);
    }

    public function redirectOldProductDetail($id, $productDetailId = null)
    {
        $product = Product::where('id', $id)->firstOrFail();
        
        return redirect()->route('frontend.product.detail', [
            'slug_and_id' => $product->seo_url_param,
            'productDetailId' => $productDetailId
        ], 301);
    }

    public function productDetail($slug_and_id, $productDetailId = null)
    {
        $parts = explode('-p.', $slug_and_id);
        $id = end($parts);

        // If it's just a UUID, redirect to proper SEO URL
        if (count($parts) === 1 && \Illuminate\Support\Str::isUuid($id)) {
            return $this->redirectOldProductDetail($id, $productDetailId);
        }

        $product = Product::where('id', $id)->firstOrFail();

        // Produk tidak ditampilkan di ecommerce → 404
        if (!$product->show_in_ecommerce) {
            abort(404);
        }

        // Enforce the correct slug (redirects if title changed or user typed wrong slug)
        if ($slug_and_id !== $product->seo_url_param) {
            return redirect()->route('frontend.product.detail', [
                'slug_and_id' => $product->seo_url_param,
                'productDetailId' => $productDetailId
            ], 301);
        }

        $this->data['colors'] = $product->colors;
        $this->data['variants'] = $product->variants;
        $this->data['product'] = $product;
        $this->data['category'] = Category::where('id', $product->category_id)->first();

        $this->data['selectedColorId'] = $this->data['colors']->first()?->id ?? null;
        $this->data['selectedSize'] = null;

        if ($productDetailId) {
            $productDetail = ProductDetail::with(['color', 'variant'])
                ->findOrFail($productDetailId);

            if ($productDetail->color) {
                $this->data['selectedColorId'] = $productDetail->color->id;
            }

            if ($productDetail->variant) {
                $this->data['selectedSize'] = $productDetail->variant->name;
            }
        }

        $pictures = collect($product->content_paths ?? [])
            ->filter(fn($item) =>
                !empty($item['path']) && Storage::disk('public')->exists($item['path'])
            )
            ->map(function ($item) {
                $item['path'] = asset('storage/' . $item['path']);
                return $item;
            })
            ->values();

        if ($pictures->isEmpty()) {
            $pictures = collect([[
                'name' => 'placeholder',
                'path' => asset('assets/images/no-image.png'),
            ]]);
        }

        $this->data['pictures'] = $pictures->toArray();

        $this->data['top_products'] = Product::withPricesAndDiscounts()
            ->whereNull('deleted_at')
            ->where('top_pick', true)
            ->whereNot('id', $id)
            ->where('show_in_ecommerce', true)
            ->orderBy('created_at', 'asc')
            ->limit(4)
            ->get();

        return view('frontend.pages.product.detail', $this->data);
    }

    public function getProductPriceByColor($color_id, $product_id)
    {
        $storeId = Store::select('id')->where('ecommerce', true)->first()->id;

        $productDetails = ProductDetail::withPricesAndDiscounts()
            ->where('color_id', $color_id)
            ->where('product_id', $product_id)
            ->where('store_id', $storeId)
            ->with('variant')
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'productDetail' => $productDetails,
        ]);
    }


    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'detail_product_id' => 'required|exists:product_details,id',
            'qty' => 'required|integer|min:1',
        ]);

        $product = ProductDetail::where('id', $request->detail_product_id)->first();

        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product variant not found',
                'data' => []
            ], 404);
        }

        if ($validated['qty'] > $product->qty) {
            return response()->json([
                'status' => 400,
                'message' => 'Quantity exceeds available stock',
                'data' => []
            ], 400);
        }

        $customerId = Auth::guard('customer')->id();
        $cartKey = 'cart_' . $customerId;

        $cart = Session::get($cartKey, []);

        if (is_string($cart)) {
            $cart = json_decode($cart, true) ?? [];
        }
        if (!is_array($cart)) {
            $cart = [];
        }

        $productId = $product->id;
        
        if (isset($cart[$productId])) {
            $newQty = $cart[$productId]['qty'] + $request->qty;

            if ($newQty > $product->qty) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Quantity exceeds available stock',
                    'data' => []
                ], 400);
            }

            $cart[$productId]['qty'] = $newQty;
        } else {
            $cart[$productId] = [
                'detail_product_id' => $productId,
                'qty' => $request->qty,
            ];
        }

        session([$cartKey => $cart]);

        return response()->json([
            'status' => 200,
            'message' => 'Product added to cart successfully',
            'data' => $cart[$productId]
        ]);
    }

    public function cart()
    {

        $this->data['top_products'] = Product::withPricesAndDiscounts()
            ->whereNull('deleted_at')
            ->where('top_pick', true)
            ->where('show_in_ecommerce', true)
            ->orderBy('created_at', 'asc')
            ->limit(4)
            ->get();

        return view('frontend.pages.cart.index', $this->data);
    }

    public function getCartData() {

        $customerId = Auth::guard('customer')->id();
        $cartKey = 'cart_' . $customerId;
        $cart = Session::get($cartKey, []);

        if (!$cart || count($cart) === 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Cart is empty',
                'data' => []
            ]);
        }

        if (is_string($cart)) {
            $cart = json_decode($cart, true) ?? [];
        }
        if (!is_array($cart)) {
            $cart = [];
        }

        $ids = collect($cart)->pluck('detail_product_id');

        $productDetails = ProductDetail::withPricesAndDiscounts()->whereIn('id', $ids)->get();

        $detailsWithQty = $productDetails->map(function ($product) use ($cart) {
            $qty = $cart[$product->id]['qty'] ?? 0;
            return [
                'id' => $product->id,
                'qty' => $qty,
                'stock' => $product->qty,
                'price' => $product->price,
                'additional_price' => $product->additional_price,
                'max_discount_type' => $product->max_discount_type,
                'max_discount' => $product->max_discount,
                'final_price' => $product->final_price,
                'product' => $product->product()->first(),
                'color' => $product->color()->first(),
                'variant' => $product->variant()->first(),
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Cart retrieved successfully',
            'data' => $detailsWithQty
        ]);
    }

    public function removeFromCart(Request $request)
    {
        if (!Auth::guard('customer')->check()) {
            return response()->json([
                'status' => 401,
                'message' => 'Please login to remove items from cart',
                'data' => []
            ], 401);
        }

        $validated = $request->validate([
            'detail_product_id' => 'required|exists:product_details,id',
        ]);

        $customerId = Auth::guard('customer')->id();
        $cartKey = 'cart_' . $customerId;
        $cart = Session::get($cartKey, []);

        if (isset($cart[$validated['detail_product_id']])) {
            unset($cart[$validated['detail_product_id']]);
            session([$cartKey => $cart]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Item removed from cart',
            'data' => array_values($cart)
        ]);
    }

    public function updateCart(Request $request)
    {
        if (!Auth::guard('customer')->check()) {
            // session(['url.intended' => url()->previous()]);
            session(['url.intended' => route("checkout.contact")]);
            return response()->json([
                'status' => 200,
                'message' => 'You have to login first',
                'data' => [
                    'redirect_url' => route('loginForm.customer')
                ]
            ]);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.detail_product_id' => 'required|exists:product_details,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $customerId = Auth::guard('customer')->id();
        $cartKey = 'cart_' . $customerId;

        $cart = Session::get($cartKey, []);

        if (is_string($cart)) {
            $cart = json_decode($cart, true) ?? [];
        }

        if (!is_array($cart)) {
            $cart = [];
        }

        foreach ($validated['items'] as $item) {
            $product = ProductDetail::where('id', $item['detail_product_id'])->first();

            if ($product && $item['qty'] <= $product->qty) {
                $cart[$item['detail_product_id']] = [
                    'detail_product_id' => $item['detail_product_id'],
                    'qty' => $item['qty'],
                ];
            }
        }

        session([$cartKey => $cart]);

        return response()->json([
            'status' => 200,
            'message' => 'Cart updated successfully',
            'data' => [
                'cart' => $cart,
                'redirect_url' => route('checkout.contact')
            ]
        ]);
    }

    public function checkoutContact()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('loginForm.customer')->with('error', 'Please login first.');
        }

        $customerId = Auth::guard('customer')->id();
        $cartKey    = 'cart_' . $customerId;

        $cart   = Session::get($cartKey, []);

        if (empty($cart)) {
            return redirect()->route('frontend.index');
        }

        return view('frontend.pages.checkout.contact', $this->data);
    }

    public function getCustomerData()
    {
        if(!Auth::guard('customer')->check()){
            return response()->json([
                'status' => 401,
                'message' => 'Please login to update cart',
                'data' => []
            ], 401);
        }

        $customerId = Auth::guard('customer')->id();
        $profileKey = 'profile_' . $customerId;
        $customerDataFromSession = Session::get($profileKey, []);
        $customerData;

        if(empty($customerDataFromSession)) {
            $customerData = Customer::where("id", $customerId)->first();
        } else {
            $customerData = $customerDataFromSession;
        }

        return response()->json([
            'status' => 200,
            'message' => 'Success get customer data',
            'data' => $customerData
        ], 200);
    }

    public function checkoutStoreCustomerProfile(Request $request)
    {
        if(!Auth::guard('customer')->check()){
            return response()->json([
                'status' => 401,
                'message' => 'Please login to store customer profile',
                'data' => []
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'save' => 'required|boolean',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'postcode' => 'required',
            'sub_district' => 'required',
            'district' => 'required',
            'city' => 'required',
            'province' => 'required',
            'address' => 'nullable',
            'districtId' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => "input yang diharapkan tidak sesuai",
                'data' => []
            ], 400);
        }

        $customerId = Auth::guard('customer')->id();
        $sessionKey = 'profile_' . $customerId;

        if($request->save === true) {
            Customer::where('id', $customerId)
            ->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'postcode' => $request->postcode,
                'sub_district' => $request->sub_district,
                'district' => $request->district,
                'city' => $request->city,
                'province' => $request->province,
                'address' => $request->address,
                'districtId' => $request->districtId,
            ]);
        }

        session([$sessionKey => $request->all()]);

        $data = Session::get($sessionKey, []);

        return response()->json([
            'status' => 200,
            'message' => 'data customer berhasil disimpan',
            'data' => [
                'profile' => $data,
                'redirect_url' => route('checkout.shipping')
            ]
        ], 200);
    }

    public function checkoutShipping()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('loginForm.customer')->with('error', 'Please login first.');
        }

        $customerId = Auth::guard('customer')->id();
        $cartKey    = 'cart_' . $customerId;

        $cart   = Session::get($cartKey, []);

        if (empty($cart)) {
            return redirect()->route('frontend.index');
        }

        return view('frontend.pages.checkout.shipping', $this->data);
    }

    public function checkoutReview()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('loginForm.customer')->with('error', 'Please login first.');
        }

        $customerId = Auth::guard('customer')->id();
        $cartKey    = 'cart_' . $customerId;
        $methodKey  = 'shipment_method_' . $customerId;
        $profilKey   = 'profile_' . $customerId;

        $cart   = Session::get($cartKey, []);
        $method = Session::get($methodKey, []);
        $profile = Session::get($profilKey, []);

        if (empty($cart) || empty($method) || empty($profile)) {
            return redirect()->route('frontend.index');
        }


        return view('frontend.pages.checkout.review', $this->data);
    }

    public function getSelectedShipmentMethodData(Request $request)
    {
        if(!Auth::guard('customer')->check()){
            return response()->json([
                'status' => 401,
                'message' => 'Please login to update cart',
                'data' => []
            ], 401);
        }

        $customerId = Auth::guard('customer')->id();
        $sessionKey = 'shipment_method_' . $customerId;

        $data = Session::get($sessionKey, []);

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil ditampilkan',
            'data' => $data
        ]);
    }

    public function wishlist()
    {  
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('loginForm.customer')->with('error', 'Please login to access the cart');
        }
        $customerId = Auth::guard('customer')->id();

        $count = Wishlist::where('customer_id', $customerId)->count();

        $this->data['wishlist'] = Wishlist::where('customer_id', $customerId)
            ->with([
                'productDetail' => function($query) {
                    $query->withPricesAndDiscounts()
                        ->with(['color', 'variant', 'store', 'product', 'discounts']);
                }
            ])->get();

        $this->data['wishlist_count'] = $count;

        return view('frontend.pages.customer.wishlist', $this->data);
    }

    public function storeWishlist(Request $request)
    {
        if (!Auth::guard('customer')->check()) {
            return response()->json([
                'status' => 401,
                'message' => 'Please login to store wishlist',
                'data' => []
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'detail_product_id' => 'required|exists:product_details,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => "Input yang diharapkan tidak sesuai",
                'errors' => $validator->errors(),
                'data' => []
            ], 400);
        }

        $customerId = Auth::guard('customer')->id();
        $detailProductId = $request->detail_product_id;

        $exists = Wishlist::where('customer_id', $customerId)
            ->where('product_detail_id', $detailProductId)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 409,
                'message' => 'Product is already in wishlist',
                'data' => []
            ], 409);
        }

        $wishlist = Wishlist::create([
            'customer_id'       => $customerId,
            'product_detail_id' => $detailProductId,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Product added to wishlist',
            'data' => [
                'wishlist' => $wishlist,
                'redirect_url' => route('frontend.wishlist')
            ]
        ], 200);
    }

    public function BlogPage(Request $request)
    {
        $query = Blog::whereNull('deleted_at');

        switch ($request->get('filter')) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->latest('created_at');
                break;
        }

        $blogs = $query->paginate(18)->appends($request->query());

        $blogs->getCollection()->transform(function ($blog) {
            $blog->excerpt = Str::limit(strip_tags($blog->content), 120);
            return $blog;
        });

        $this->data['blogs'] = $blogs;

        return view('frontend.pages.blog.index', $this->data);
    }

    public function detailBlog($id)
    {
        $blog = Blog::where('id', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $otherBlogs = Blog::whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $this->data['blog'] = $blog;
        $this->data['otherBlogs'] = $otherBlogs;

        return view('frontend.pages.blog.detail', $this->data);
    }

}