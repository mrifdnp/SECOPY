@extends('frontend.layouts.home')

@section('title', $category->meta_title ?? $category->title)
@section('meta_title', $category->meta_title ?? $category->title)
@section('meta_description', $category->meta_description ?? 'Temukan koleksi ' . $category->title . ' terbaik di Seco by Seken Living. Belanja furniture dan dekorasi rumah berkualitas dengan harga terbaik.')
@section('canonical', route('frontend.productByMainCategory', $category->slug))

@section('content')
    <div class="md:px-0 max-w-[1420px] mx-auto">
        <div class="sticky top-0 z-20 bg-white px-[16px] pb-[7px] pd:mb-0">
            <div class="flex justify-between pt-[8px] md:pt-[12px] md:px-[50px] lg:px-[100px]">
                <div class="flex flex-col">
                    <h1 class="font-plush text-[24px] md:text-[36px] font-medium text-primary-dark-950">
                        {{ $category->title }}
                    </h1>
                    <p class="text-[12px] md:text-[16px] font-cabinet leading-[1.2] text-primary-dark-950 mt-[-5px]">
                        {{ $products_count }} Products</p>
                </div>
                <div class="hidden md:flex justify-end items-center gap-[16px]">
                    <div class="flex items-center gap-[16px]">
                        <span class="text-primary-dark-950 font-normal font-cabinet text-[18px]">Sort By:</span>
                        <div class="relative">
                            <form method="GET" action="{{ route('frontend.productByMainCategory', $category->slug) }}">
                                <select
                                    name="filter"
                                    class="appearance-none bg-white font-cabinet text-[18px] border border-primary-light-200 rounded px-4 py-2 pr-8 text-primary-dark-950 focus:outline-none focus:ring-1 focus:ring-primary-light-500 focus:border-primary-light-500"
                                    onchange="this.form.submit()"
                                >
                                    <option value="featured" {{ request('filter') == 'featured' ? 'selected' : '' }}>Featured</option>
                                    <option value="newest" {{ request('filter') == 'newest' ? 'selected' : '' }}>Newest</option>
                                    <option value="oldest" {{ request('filter') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                                    <option value="price-low" {{ request('filter') == 'price-low' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price-high" {{ request('filter') == 'price-high' ? 'selected' : '' }}>Price: High to Low</option>
                                </select>
                            </form>

                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-primary-dark-950" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-[8px]">
                        {{-- Previous Button --}}
                        @if ($products->onFirstPage())
                            <button class="p-3 text-primary-light-600 border border-primary-light-200 disabled:opacity-50"
                                disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                        @else
                            <a href="{{ $products->previousPageUrl() }}"
                                class="p-3 text-primary-light-600 border border-primary-light-200 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </a>
                        @endif

                        {{-- Current Page / Total Pages --}}
                        <div class="bg-white text-sm text-gray-700 text-center">
                            {{ $products->currentPage() }}/{{ $products->lastPage() }}
                        </div>

                        {{-- Next Button --}}
                        @if ($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}"
                                class="p-3 text-primary-light-600 border border-primary-light-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @else
                            <button class="p-3 text-primary-light-600 border border-primary-light-200 disabled:opacity-50"
                                disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        @endif
                    </div>

                </div>
            </div>
            {{-- <div class="hidden md:flex justify-between md:px-[50px] lg:px-[100px] my-[8px]">
                <div class="flex gap-[16px]">
                    <div class="relative">
                        <select
                            class="appearance-none font-cabinet text-[18px] w-fit bg-white rounded py-2 pr-8 text-primary-dark-950 focus:outline-none">
                            <option value="featured">Product Type</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-primary-dark-950" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="relative">
                        <select
                            class="appearance-none w-fit font-cabinet text-[18px] bg-white rounded py-2 pr-8 text-primary-dark-950 focus:outline-none">
                            <option value="featured">Item Type</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-primary-dark-950" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="relative">
                        <select
                            class="appearance-none w-fit font-cabinet text-[18px] bg-white rounded py-2 pr-8 text-primary-dark-950 focus:outline-none">
                            <option value="featured">Price</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-primary-dark-950" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="relative">
                        <select
                            class="appearance-none w-fit font-cabinet text-[18px] bg-white rounded py-2 pr-8 text-secondary-terra-600 focus:outline-none">
                            <option value="featured">Reset All Filters</option>
                        </select>
                        <div class="absolute inset-y-0 right-[-10px] flex items-center px-2 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25"
                                fill="none">
                                <path
                                    d="M19.479 20.9436L3.09396 4.56464C2.98396 4.45464 2.92896 4.33564 2.92896 4.20764C2.92896 4.07964 2.98396 3.96097 3.09396 3.85164C3.20396 3.74097 3.32296 3.68564 3.45096 3.68564C3.57896 3.68564 3.69796 3.74097 3.80796 3.85164L20.192 20.2356C20.3026 20.3463 20.358 20.4643 20.358 20.5896C20.358 20.715 20.3026 20.833 20.192 20.9436C20.0813 21.0543 19.9623 21.1096 19.835 21.1096C19.7076 21.1096 19.589 21.0543 19.479 20.9436ZM15.539 12.5436C15.399 12.5436 15.2796 12.4963 15.181 12.4016C15.0823 12.3083 15.033 12.1916 15.033 12.0516C15.033 11.9203 15.0803 11.803 15.175 11.6996C15.269 11.5956 15.39 11.5436 15.538 11.5436H16.673C16.815 11.5436 16.9336 11.5916 17.029 11.6876C17.1243 11.7836 17.1723 11.9026 17.173 12.0446C17.1736 12.1866 17.1256 12.3053 17.029 12.4006C16.9323 12.496 16.8136 12.5436 16.673 12.5436H15.539ZM11.039 8.04364C10.899 8.04364 10.7796 7.9963 10.681 7.90164C10.5823 7.8083 10.533 7.69164 10.533 7.55164C10.533 7.42097 10.5803 7.30364 10.675 7.19964C10.769 7.09564 10.89 7.04364 11.038 7.04364H19.5C19.642 7.04364 19.7606 7.09164 19.856 7.18764C19.9513 7.28364 19.9993 7.40264 20 7.54464C20.0006 7.68664 19.9526 7.80531 19.856 7.90064C19.7593 7.99597 19.6406 8.04364 19.5 8.04364H11.039ZM11.058 17.0436C10.916 17.0436 10.797 16.9956 10.701 16.8996C10.605 16.8036 10.5573 16.6846 10.558 16.5426C10.5586 16.4006 10.6063 16.282 10.701 16.1866C10.7956 16.0913 10.9146 16.0436 11.058 16.0436H12.923C13.0643 16.0436 13.183 16.0916 13.279 16.1876C13.375 16.2836 13.423 16.4026 13.423 16.5446C13.423 16.6866 13.375 16.8053 13.279 16.9006C13.183 16.996 13.0643 17.0436 12.923 17.0436H11.058ZM7.30796 12.5436C7.16596 12.5436 7.04696 12.4956 6.95096 12.3996C6.85496 12.3036 6.80729 12.1846 6.80796 12.0426C6.80862 11.9006 6.85629 11.782 6.95096 11.6866C7.04562 11.5913 7.16462 11.5436 7.30796 11.5436H11.073V12.5436H7.30796ZM4.49996 8.04364C4.35796 8.04364 4.23929 7.99564 4.14396 7.89964C4.04862 7.80364 4.00062 7.68464 3.99996 7.54264C3.99929 7.40064 4.04729 7.28197 4.14396 7.18664C4.24062 7.09131 4.35929 7.04364 4.49996 7.04364H6.57296V8.04364H4.49996Z"
                                    fill="#D85640" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="relative">
                        <select
                            class="appearance-none w-fit font-cabinet text-[18px] bg-white rounded py-2 pr-8 text-primary-dark-950 focus:outline-none">
                            <option value="featured">More Filters</option>
                        </select>
                        <div class="absolute inset-y-0 right-[-10px] flex items-center px-2 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25"
                                fill="none">
                                <path
                                    d="M11.058 17.0436C10.916 17.0436 10.7973 16.9956 10.702 16.8996C10.6067 16.8036 10.5587 16.6846 10.558 16.5426C10.5573 16.4006 10.6053 16.282 10.702 16.1866C10.7987 16.0913 10.9173 16.0436 11.058 16.0436H12.923C13.065 16.0436 13.1837 16.0916 13.279 16.1876C13.3743 16.2836 13.4223 16.4026 13.423 16.5446C13.4237 16.6866 13.3757 16.8053 13.279 16.9006C13.1823 16.996 13.0637 17.0436 12.923 17.0436H11.058ZM7.30801 12.5436C7.16601 12.5436 7.04734 12.4956 6.95201 12.3996C6.85667 12.3036 6.80867 12.1846 6.80801 12.0426C6.80734 11.9006 6.85534 11.782 6.95201 11.6866C7.04867 11.5913 7.16734 11.5436 7.30801 11.5436H16.673C16.815 11.5436 16.9337 11.5916 17.029 11.6876C17.1243 11.7836 17.1723 11.9026 17.173 12.0446C17.1737 12.1866 17.1257 12.3053 17.029 12.4006C16.9323 12.496 16.8137 12.5436 16.673 12.5436H7.30801ZM4.50001 8.04364C4.35801 8.04364 4.23934 7.99564 4.14401 7.89964C4.04867 7.80364 4.00067 7.68464 4.00001 7.54264C3.99934 7.40064 4.04734 7.28197 4.14401 7.18664C4.24067 7.09131 4.35934 7.04364 4.50001 7.04364H19.5C19.642 7.04364 19.7607 7.09164 19.856 7.18764C19.9513 7.28364 19.9993 7.40264 20 7.54464C20.0007 7.68664 19.9527 7.80531 19.856 7.90064C19.7593 7.99597 19.6407 8.04364 19.5 8.04364H4.50001Z"
                                    fill="#482B1B" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div class=" md:hidden flex w-full my-[16px]">
                <button class="text-primary-dark-700 w-11/12 border border-primary-light-500 rounded py-2 px-4">
                    Filter & Sort
                </button>
                <div class="ml-2 flex items-center justify-center w-[40px] h-[40px] border border-primary-light-500 ">
                    <img src="{{ asset('assets/product/icons/menu-icon.svg') }}" alt="">
                </div>
            </div>
        </div>
        <div class="px-[16px] md:px-[50px] lg:px-[120px] md:mb-[80px] mt-[20px]">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-[10px] lg:gap-[20px]">
                @foreach ($products as $product)
                    <div
                        class="group relative border bg-primary-light-500 border-primary-light-200 w-full h-[301px] lg:w-[384px] md:h-[533px] bg-cover bg-center transition-all duration-300 ease-in-out overflow-hidden flex flex-col justify-between">
                        <div class="relative w-full h-[231px] md:h-[533px] overflow-hidden">
                            <img src="{{ $product->main_pic ?? asset("assets/images/no-image.png") }}" alt="{{ $product->title }}"
                                class="w-full h-full object-cover transition-all duration-300 group-hover:opacity-0">
                            <img src="{{ $product->hover_pic ?? $product->main_pic ?? asset("assets/images/no-image.png") }}" alt="{{ $product->title }} hover"
                                class="w-full h-full object-cover absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-300">

                            @if ($product->top_pick)
                                <div
                                    class="absolute top-[14px] left-[14px] px-[8px] py-[2px] bg-secondary-terra-700 w-fit md:w-[73px] z-10">
                                    <p class="text-[12px] md:text-[14px] font-cabinet text-white font-normal">
                                        Top Picks
                                    </p>
                                </div>
                            @endif
                        </div>
                        <div
                            class="overflow-hidden h-[0px] transition-[height] duration-300 ease-in-out group-hover:h-[40px]">
                            <a href="{{ route('frontend.product.detail', $product->seo_url_param) }}" class=" cursor-pointer">
                                <button
                                    class="w-full bg-primary-light-500 py-[6px] flex justify-center items-center text-white text-[14px] font-light transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                    Quick Shop
                                </button>
                            </a>
                        </div>
                        <!-- Bottom Content -->
                        <div class="z-10 px-[8px] h-[90px] md:p-[14px] py-[12px] bg-white transition-all duration-300 ease-in-out">
                            <div class="flex justify-between items-start">
                                <div class="">
                                    <p class="md:hidden font-cabinet text-[14px] md:text-[20px] font-bold leading-none mb-[5px]">
                                        {{ \Illuminate\Support\Str::limit($product->title, 15) }}
                                    </p>
                                    <p class="hidden md:block font-cabinet text-[14px] md:text-[20px] font-bold leading-none mb-[5px]">
                                        {{ $product->title }}
                                    </p>
                                    <p
                                        class="font-cabinet text-[12px] md:text-[16px] font-bold text-primary-light-500 leading-none">
                                        IDR {{ number_format($product->final_price, 0, ',', '.') }}
                                    </p>
                                    @if ($product->max_discount !== null)
                                        <div class="flex gap-2 mt-1">
                                            <p
                                                class="font-cabinet text-[10px] md:text-[12px] font-bold text-secondary-terra-700 leading-none">
                                                @if ($product->max_discount_type === 'nominal')
                                                    IDR {{ number_format($product->max_discount, 0, ',', '.') }} off
                                                @elseif($product->max_discount_type === 'percent')
                                                    {{ rtrim(rtrim(number_format($product->max_discount, 2, ',', '.'), '0'), ',') }}%
                                                    off
                                                @endif
                                            </p>
                                            <p
                                                class="font-cabinet text-[10px] md:text-[12px] font-bold text-neutral-dark-300 leading-none line-through">
                                                IDR {{ number_format($product->getPriceProduct(), 0, ',', '.') }}
                                            </p>
                                        </div>
                                    @else
                                        <div class="mt-4"></div>
                                    @endif
                                </div>

                                <img src="{{ asset('assets/homepage/icons/love-white.svg') }}" alt="favorite" />
                            </div>

                            <!-- Button with slide-up animation -->
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
