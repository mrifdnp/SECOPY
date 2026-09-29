<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Handle the chatbot conversation.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'messages' => 'required|array',
        ]);

        $userMessages = $request->input('messages');
        
        // Deteksi bahasa website saat ini (Indo / English)
        $locale = app()->getLocale();
        $languageName = ($locale == 'en') ? 'English' : 'Bahasa Indonesia';
        
        // Sisipkan instruksi rahasia ke AI di akhir pesan agar mengikuti bahasa website
        $userMessages[] = [
            'role' => 'system',
            'content' => "IMPORTANT RULE: You MUST reply and converse using exactly {$languageName} language, because the user's website interface is currently set to {$languageName}. Translate your tone and style accordingly."
        ];

        $n8nWebhookUrl = env('N8N_WEBHOOK_URL');
        $chatId = session()->getId() ?? uniqid('chat_');

        if (!$n8nWebhookUrl) {
            return response()->json(['error' => 'Webhook URL n8n belum dikonfigurasi di .env.'], 500);
        }

        try {
            // Bersihkan cache produk sebelumnya untuk chat ini
            \Illuminate\Support\Facades\Cache::forget('chatbot_products_' . $chatId);

            // Tembak webhook n8n dengan batas waktu (timeout) 15 detik
            $response = Http::timeout(15)->post($n8nWebhookUrl, [
                'messages' => $userMessages,
                'chat_id' => $chatId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Ambil produk dan info kategori dari cache yang disisipkan oleh searchAPI
                $cacheData = \Illuminate\Support\Facades\Cache::pull('chatbot_products_' . $chatId, []);
                $products = $cacheData['products'] ?? [];
                $categoryInfo = $cacheData['category_info'] ?? null;

                return response()->json([
                    'reply' => $data['reply'] ?? 'Maaf, saya tidak mengerti.',
                    'products' => $products,
                    'category_info' => $categoryInfo
                ]);
            }

            Log::error('n8n Webhook Error: ' . $response->body());
            return response()->json(['error' => 'Gagal menghubungi server AI (n8n).'], 500);

        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menghubungi server AI.'], 500);
        }
    }

    /**
     * Endpoint API internal yang dipanggil oleh n8n (HTTP Request Tool)
     */
    public function searchAPI(Request $request)
    {
        Log::info('N8N_SEARCH_API_PAYLOAD', $request->all());
        
        $keyword = $request->input('keyword', '');
        $maxPrice = $request->input('max_price');
        $minPrice = $request->input('min_price');
        $sort = $request->input('sort', 'latest'); // 'cheapest', 'expensive', atau 'latest'
        $chatId = $request->input('chat_id');

        // Pastikan numeric
        if ($maxPrice !== null && is_numeric($maxPrice)) $maxPrice = (int)$maxPrice;
        if ($minPrice !== null && is_numeric($minPrice)) $minPrice = (int)$minPrice;

        $searchData = $this->searchProducts($keyword, $maxPrice, $minPrice, $sort);

        // Jika ada chat_id, simpan produk dan info kategori ke cache
        if ($chatId) {
            $cacheData = [
                'products' => $searchData['products'],
                'category_info' => $searchData['category_info']
            ];
            \Illuminate\Support\Facades\Cache::put('chatbot_products_' . $chatId, $cacheData, 60);
        }

        return response()->json([
            'total_found_in_database' => $searchData['total_count'],
            'cheapest_price' => $searchData['cheapest_price'],
            'most_expensive_price' => $searchData['most_expensive_price'],
            'category_info' => $searchData['category_info'],
            'products' => $searchData['products']
        ]);
    }
    
        /**
         * Logic pencarian produk
         */
        private function searchProducts($keyword, $maxPrice = null, $minPrice = null, $sort = 'latest')
        {
            // Menggunakan scope WithPricesAndDiscounts untuk mendapatkan final_price
            $query = Product::withPricesAndDiscounts()
                ->where('show_in_ecommerce', true)
                ->whereNull('products.deleted_at')
                ->where(function ($q) use ($keyword) {
                    if ($keyword) {
                        // Pisahkan keyword menjadi beberapa kata
                        $words = explode(' ', trim($keyword));
                        $stopWords = ['rekomendasi', 'gaya', 'untuk', 'dengan', 'yang', 'dan', 'atau', 'buat', 'tolong', 'carikan', 'aku', 'saya', 'ingin', 'mau'];
                        
                        $validWords = [];
                        foreach ($words as $word) {
                            $cleanWord = strtolower(trim($word));
                            if (strlen($cleanWord) > 2 && !in_array($cleanWord, $stopWords)) {
                                $validWords[] = $cleanWord;
                            }
                        }
                        
                        // Batasi maksimal 3 kata agar database tidak meledak (Mencegah CPU 100% dan RAM Habis)
                        $validWords = array_slice($validWords, 0, 3);

                        foreach ($validWords as $cleanWord) {
                            $q->orWhere('title', 'ilike', '%' . $cleanWord . '%')
                              ->orWhereHas('categories', function ($catQ) use ($cleanWord) {
                                  $catQ->where('title', 'ilike', '%' . $cleanWord . '%')
                                       ->orWhereHas('mainCategory', function ($mainCatQ) use ($cleanWord) {
                                           $mainCatQ->where('title', 'ilike', '%' . $cleanWord . '%');
                                       });
                              });
                        }
                    }
                });
            
            $products = $query->take(50)->get();
    
            if ($maxPrice !== null) {
                $products = $products->where('final_price', '<=', $maxPrice);
            }
            if ($minPrice !== null) {
                $products = $products->where('final_price', '>=', $minPrice);
            }
            
            $totalFound = $products->count();
            $cheapest = $totalFound > 0 ? $products->min('final_price') : 0;
            $mostExpensive = $totalFound > 0 ? $products->max('final_price') : 0;
            
            // Lakukan pengurutan jika diminta oleh AI
            if ($sort === 'cheapest') {
                $products = $products->sortBy('final_price')->values();
            } elseif ($sort === 'expensive') {
                $products = $products->sortByDesc('final_price')->values();
            }
    
            // Ambil top 5
            $topProducts = $products->take(5);
            $topProducts->load('productDetail'); // Load details for Add to Cart
    
            $results = [];
            foreach ($topProducts as $product) {
                $mainPic = $product->main_pic ?? asset('assets/images/no-image.png');

                $detailId = $product->productDetail->first()?->id ?? null;

                $results[] = [
                    'id' => $product->id,
                    'name' => $product->title,
                    'price' => $product->final_price,
                    'detail_id' => $detailId,
                    'image' => $mainPic,
                    'url' => route('frontend.product.detail', ['id' => $product->id])
                ];
            }
            
            // Cari apakah keyword ini cocok dengan kategori tertentu (Main Category atau Sub Category)
            $categoryInfo = null;
            if ($keyword && isset($validWords)) {
                foreach ($validWords as $cleanWord) {
                    if (strlen($cleanWord) > 2) {
                        
                        // Cari di Main Category dulu
                        $mainCategory = \App\Models\MainCategory::where('title', 'ilike', '%' . $cleanWord . '%')->first();
                        if ($mainCategory) {
                            $categoryInfo = [
                                'name' => $mainCategory->title,
                                'url' => route('frontend.productByMainCategory', ['id' => $mainCategory->id])
                            ];
                            break;
                        }

                        // Kalau tidak ada di Main Category, cari di Category biasa (Sub)
                        $category = \App\Models\Category::where('title', 'ilike', '%' . $cleanWord . '%')->first();
                        if ($category) {
                            $categoryInfo = [
                                'name' => $category->title,
                                'url' => route('frontend.product', ['id' => $category->id])
                            ];
                            break; // Cukup temukan satu kategori yang cocok
                        }
                    }
                }
            }
    
            return [
                'total_count' => $totalFound,
                'cheapest_price' => $cheapest,
                'most_expensive_price' => $mostExpensive,
                'category_info' => $categoryInfo,
                'products' => $results
            ];
        }
}
