@extends('frontend.layouts.home')

@section('title', 'Wishlist')
@section('meta_robots', 'noindex, nofollow')

@section('content')

    <div class="md:px-0 md:px-[120px] md:mt-[32px] mb-[90px]">
        <div class="flex flex-col px-[16px] md:px-0 md:pb-[32px] w-full max-w-[1200px] justify-center mx-auto">
            <div class="flex items-center mb-[16px]">
                <p class="text-neutral-dark-400">Home</p>
                <iconify-icon icon="mdi:chevron-right" class="mx-[8px] text-neutral-dark-400"></iconify-icon>
                <p class="text-primary-dark-900">Wishlist</p>
            </div>
            <h1 class=" font-plush text-primary-dark-950 text-[30px] font-medium mb-[8px] leading-[1.2]">Wishlist</h1>
            <p>You've saved {{ $wishlist_count }} favorite pieces.</p>
            <div class=" mt-[32px] grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-[16px]">
                @foreach ($wishlist as $item)
                    <div
                        class="group relative border border-primary-light-200 w-full h-[231px] lg:w-[288px] md:h-[410px] bg-cover bg-center transition-all duration-300 ease-in-out overflow-hidden flex flex-col justify-between">
                        <div class="relative w-full h-[231px] md:h-[533px] overflow-hidden">
                            <img src="{{ $item->productDetail->product->main_pic ?? asset("assets/images/no-image.png") }}"
                                alt="{{ $item->productDetail->product->title }}"
                                class="w-full h-full object-cover transition-all duration-300 group-hover:opacity-0">
                            <img src="{{ $item->productDetail->product->hover_pic ?? $item->productDetail->product->main_pic ?? asset("assets/images/no-image.png") }}"
                                alt="{{ $item->productDetail->product->title }} hover"
                                class="w-full h-full object-cover absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-300">

                            <div class="flex absolute left-0 right-0 top-0 justify-between m-[14px] gap-2">
                                <div class="px-[8px] py-[2px] bg-secondary-terra-700 w-fit z-10">
                                    <p class=" text-[12px] md:text-[14px] font-cabinet text-white font-normal">
                                        Top Picks
                                    </p>
                                </div>
                                <div
                                    class="flex items-center justify-center gap-2 px-[8px] py-[2px] bg-primary-light-500 w-fit z-10">
                                    <p class="text-[12px] md:text-[14px] font-cabinet text-white font-normal">
                                        {{ $item->productDetail->variant->name }}
                                    </p>

                                    <div class="w-4 h-4 rounded-full border border-white"
                                        style="background-color: {{ $item->productDetail->color->hexa }};">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="overflow-hidden h-[0px] transition-[height] bg-transparent duration-300 ease-in-out group-hover:h-[50px]  md:group-hover:h-[60px]">
                            <a href="{{ route('frontend.product.detail', $item->productDetail->product->seo_url_param) }}"
                                class=" cursor-pointer">
                                <button
                                    class="w-full bg-primary-light-500 py-[6px] flex justify-center items-center text-white text-[14px] font-light transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                    Quick Shop
                                </button>
                            </a>
                        </div>
                        <!-- Bottom Content -->
                        <div class="z-10 px-[8px] md:p-[14px] bg-white transition-all duration-300 ease-in-out h-[80px] pt-[5px] md:h-[130px]">
                            <div class="flex justify-between items-start">
                                <div class="">
                                    <p class="font-cabinet text-[14px] md:text-[20px] font-bold leading-none mb-[5px]">
                                        {{ $item->productDetail->product->title }}
                                    </p>
                                    <p
                                        class="font-cabinet text-[12px] md:text-[16px] font-bold text-primary-light-500 leading-none">
                                        IDR {{ number_format($item->productDetail->product->final_price, 0, ',', '.') }}
                                    </p>
                                    @if ($item->productDetail->max_discount !== null)
                                        <div class="flex gap-2 mt-1">
                                            <p
                                                class="font-cabinet text-[12px] font-bold text-secondary-terra-700 leading-none">
                                                @if ($item->productDetail->max_discount_type === 'nominal')
                                                    IDR
                                                    {{ number_format($item->productDetail->max_discount, 0, ',', '.') }}
                                                    off
                                                @elseif($item->productDetail->max_discount_type === 'percent')
                                                    {{ rtrim(rtrim(number_format($item->productDetail->max_discount, 2, ',', '.'), '0'), ',') }}%
                                                    off
                                                @endif
                                            </p>
                                            <p
                                                class="font-cabinet text-[12px] font-bold text-neutral-dark-300 leading-none line-through">
                                                IDR {{ number_format($item->productDetail->price, 0, ',', '.') }}
                                            </p>
                                        </div>
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


@section('css')

    <link href="https://fonts.cdnfonts.com/css/cabinet-grotesk" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/css/intlTelInput.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/css/intlTelInput.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

@endsection


@section('script')

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

@endsection
