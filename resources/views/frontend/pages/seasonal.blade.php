@extends('frontend.layouts.home')

@section('title')
    {{ $seasonal->name }}
@endsection

@section('css')
    <style>
        .text-shadow {
            text-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }
    </style>
@endsection

@section('content')

    <!-- Hero Section -->
    <div class="relative w-full pt-[50px] h-[calc(100vh-68px)] md:h-[calc(100vh-150px)] flex items-center justify-center bg-cover bg-[calc(100%_+_140px)_center] md:bg-center"
        style="background-image: url('{{ $seasonal->banner_image ? asset('storage/seasonal/' . $seasonal->banner_image) : asset('assets/images/no-image.png') }}');">

        <div class="relative z-20 md:text-center px-[20px] md:px-0">
            <h1 class="font-plush font-medium text-primary-light-50 text-[36px] md:text-[72px] leading-[1.3] text-shadow">
                {{ $seasonal->name }}
            </h1>
            <p class="font-plush text-primary-light-50 text-[24px] md:text-[48px] md:italic md:mt-[-10px] leading-[1.3] text-shadow">
                {{ $seasonal->description }}
            </p>
            @if($seasonal->button_text)
            <a href="{{ !empty($seasonal->button_link) ? $seasonal->button_link : '#collection' }}">
                <button
                    class="py-[8px] px-[16px] mt-[40px] rounded-[2px] bg-[rgba(255,255,255,0.15)] backdrop-blur-[8px] text-white border border-white">
                    {{ $seasonal->button_text }}
                    <img src="{{ asset('assets/homepage/icons/arrow-down.svg') }}" alt="Arrow Down"
                        class="inline-block ml-2 w-[24px] h-[24px]" />
                </button>
            </a>
            @endif
        </div>

        <div class="absolute bottom-0 left-0 right-0 h-[36px] py-[8px] flex justify-center"
            style="background-color: rgba(72, 43, 27, 0.5)">
            <div class="flex items-center gap-[8px] justify-between w-full px-[16px] md:justify-center">
                <span class="text-primary-light-50 font-cabinet text-[16px]">Ready to bring something home?</span>
                <span class="text-primary-light-50 font-semibold font-cabinet underline"><a href="#collection">Shop Now</a></span>
            </div>
        </div>
    </div>


    <div id="collection" class="pt-[50px] lg:px-[120px] md:pt-[90px] overflow-hidden">
        <!-- Title -->
        <div class="px-[24px] flex flex-col justify-center text-start md:items-center">
            <div class="flex flex-col items-start md:items-center">
                <h2 class="font-plush text-primary-dark-950 text-[24px] md:text-[60px]">
                    {{ $seasonal->collection_title ?? 'Seasonal Collection' }}
                </h2>
                <p class="font-plush text-primary-dark-500 text-[16px] md:text-[30px] text-start md:text-center">
                    {{ $seasonal->collection_subtitle ?? ($seasonal->hover_description ?? 'Timeless pieces, thoughtfully chosen to inspire your space.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Products Grid Section -->
    <div class="mt-[40px] px-[16px] md:px-[24px] lg:px-[0px] mb-[100px] max-w-[1200px] mx-auto">
        
        <!-- Pagination & Filter Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-[24px] gap-[16px]">
            <div class="flex flex-col md:flex-row md:items-center gap-[16px] md:gap-[24px]">
                <p class="text-[14px] md:text-[18px] font-cabinet leading-[1.2] text-primary-dark-950 font-medium whitespace-nowrap">
                    {{ $products_count }} Products
                </p>
            </div>
            
            @if($products_count > 0)
            <div class="flex flex-col md:flex-row justify-end items-start md:items-center gap-[16px] md:gap-[24px]">
                <!-- Sort Dropdown -->
                <div class="flex items-center gap-[8px]">
                    <span class="text-[14px] md:text-[16px] text-primary-dark-950 font-cabinet">Sort By:</span>
                    <div class="relative">
                        <select onchange="window.location.href=this.value + '#collection'" class="appearance-none border border-primary-light-500 rounded-[2px] px-[12px] py-[6px] pr-[32px] font-cabinet text-[14px] md:text-[16px] text-primary-dark-950 bg-white focus:outline-none cursor-pointer">
                            <option value="{{ request()->fullUrlWithQuery(['filter' => 'featured', 'page' => null]) }}" {{ request('filter') == 'featured' || !request()->has('filter') ? 'selected' : '' }}>Featured</option>
                            <option value="{{ request()->fullUrlWithQuery(['filter' => 'newest', 'page' => null]) }}" {{ request('filter') == 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="{{ request()->fullUrlWithQuery(['filter' => 'oldest', 'page' => null]) }}" {{ request('filter') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                            <option value="{{ request()->fullUrlWithQuery(['filter' => 'price-low', 'page' => null]) }}" {{ request('filter') == 'price-low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ request()->fullUrlWithQuery(['filter' => 'price-high', 'page' => null]) }}" {{ request('filter') == 'price-high' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-[8px] pointer-events-none">
                            <svg class="w-[16px] h-[16px] text-primary-dark-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Pagination Top -->
                <div class="flex items-center gap-[8px]">
                    {{-- Previous Button --}}
                    @if ($products->onFirstPage())
                        <button class="p-2 md:p-3 text-primary-light-600 border border-primary-light-200 disabled:opacity-50" disabled>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    @else
                        <a href="{{ $products->previousPageUrl() }}#collection" class="p-2 md:p-3 text-primary-light-600 border border-primary-light-200 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    {{-- Current Page / Total Pages --}}
                    <div class="bg-white text-sm text-gray-700 text-center font-cabinet px-2">
                        {{ $products->currentPage() }} / {{ $products->lastPage() }}
                    </div>

                    {{-- Next Button --}}
                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}#collection" class="p-2 md:p-3 text-primary-light-600 border border-primary-light-200 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <button class="p-2 md:p-3 text-primary-light-600 border border-primary-light-200 disabled:opacity-50" disabled>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-[8px] lg:gap-[16px] w-full">
            @forelse ($products as $product)
                <!-- Card -->
                <div class="group relative border border-primary-light-200 w-full bg-cover bg-center transition-all duration-300 ease-in-out overflow-hidden flex flex-col justify-between">
                    <div class="relative h-[220px] md:h-[300px] lg:h-[350px] w-full overflow-hidden">
                        <img src="{{ $product->main_pic ?? asset('assets/images/no-image.png') }}" alt="{{ $product->title }}"
                            class="w-full h-full object-cover transition-all duration-300 group-hover:opacity-0">
                        <img src="{{ $product->hover_pic ?? $product->main_pic ?? asset('assets/images/no-image.png') }}" alt="{{ $product->title }} hover"
                            class="w-full h-full object-cover absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-300">

                        @if ($product->top_pick)
                            <div class="absolute top-[10px] left-[10px] px-[8px] py-[2px] bg-secondary-terra-700 w-fit z-10">
                                <p class="text-[10px] md:text-[14px] font-cabinet text-white font-normal">
                                    Top Picks
                                </p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="overflow-hidden h-[0px] transition-[height] duration-300 ease-in-out group-hover:h-[40px] md:group-hover:h-[50px]">
                        <a href="{{ route('frontend.product.detail', $product->seo_url_param) }}">
                            <button class="w-full h-full bg-primary-light-500 py-[6px] flex justify-center items-center text-white text-[12px] md:text-[14px] font-light transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                Quick Shop
                            </button>
                        </a>
                    </div>

                    <!-- Bottom Content -->
                    <div class="z-10 p-[10px] md:p-[14px] transition-all duration-300 ease-in-out h-[100px] md:h-[120px]">
                        <div class="flex justify-between items-start">
                            <div class="w-[85%]">
                                <p class="font-cabinet text-[14px] md:text-[20px] font-bold leading-tight mb-[5px] truncate">
                                    {{ $product->title }}
                                </p>
                                <p class="font-cabinet text-[14px] md:text-[16px] font-bold text-primary-light-500 leading-none">
                                    IDR {{ number_format($product->final_price, 0, ',', '.') }}
                                </p>
                                @if ($product->max_discount !== null)
                                    <div class="flex flex-col md:flex-row md:gap-2 mt-1">
                                        <p class="font-cabinet text-[11px] md:text-[12px] font-bold text-secondary-terra-700 leading-none">
                                            @if ($product->max_discount_type === 'nominal')
                                                IDR {{ number_format($product->max_discount, 0, ',', '.') }} off
                                            @elseif($product->max_discount_type === 'percent')
                                                {{ rtrim(rtrim(number_format($product->max_discount, 2, ',', '.'), '0'), ',') }}% off
                                            @endif
                                        </p>
                                        <p class="font-cabinet text-[11px] md:text-[12px] font-bold text-neutral-dark-300 leading-none line-through mt-1 md:mt-0">
                                            IDR {{ number_format($product->getPriceProduct(), 0, ',', '.') }}
                                        </p>
                                    </div>
                                @else
                                    <div class="h-4"></div>
                                @endif
                            </div>
                            <img src="{{ asset('assets/homepage/icons/love-white.svg') }}" alt="favorite" class="w-[20px] md:w-[24px]" />
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 md:col-span-3 lg:col-span-4 py-[100px] flex flex-col items-center justify-center opacity-60">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-primary-dark-500">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <p class="font-cabinet text-[20px] text-primary-dark-500">Produk untuk event ini belum tersedia.</p>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination Bottom -->
        @if($products->hasPages())
        <div class="mt-[40px] flex justify-center w-full">
            <div class="flex items-center gap-[16px]">
                {{-- Previous Button --}}
                @if ($products->onFirstPage())
                    <button class="p-3 text-primary-light-600 border border-primary-light-200 disabled:opacity-50" disabled>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                @else
                    <a href="{{ $products->previousPageUrl() }}#collection" class="p-3 text-primary-light-600 border border-primary-light-200 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @endif

                {{-- Current Page / Total Pages --}}
                <div class="bg-white text-[16px] text-gray-700 text-center font-cabinet px-4">
                    Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
                </div>

                {{-- Next Button --}}
                @if ($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}#collection" class="p-3 text-primary-light-600 border border-primary-light-200 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <button class="p-3 text-primary-light-600 border border-primary-light-200 disabled:opacity-50" disabled>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>

@endsection

@section('script')
@endsection
