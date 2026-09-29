@extends('frontend.layouts.home')

@section('title', 'Seco by Seken Living')

@section('content')

    <!-- Hero Section -->
    <div class="relative w-full pt-[50px] h-[calc(100vh-68px)] md:h-[calc(100vh-150px)] flex items-center justify-center
        @if ($hero['type'] == 1) bg-cover bg-[calc(100%_+_140px)_center] md:bg-center @endif"
        @if ($hero['type'] == 1) style="background-image: url('{{ asset('storage/hero/' . $hero['content']) }}');" @endif>
        <!-- Background untuk video -->
        @if ($hero['type'] == 2)
            <div class="video-bg">
                <div id="ytbg" data-ytbg-fade-in="true" data-youtube="{{ $hero['content'] }}"></div>
            </div>
        @endif

        <div class="relative z-20 md:text-center px-[20px] md:px-0">

            <h1 class="font-plush font-medium text-primary-light-50 text-[36px] md:text-[72px] leading-[1.3]">
                {{ $hero['title'] }}
            </h1>
            <p class="font-plush text-primary-light-50 text-[36px] md:text-[72px] md:italic md:mt-[-30px] leading-[1.3]">
                {{ $hero['subtitle'] }}
            </p>
            <a href="{{ $hero['link_url'] }}">
                <button
                    class="py-[8px] px-[16px] mt-[40px] rounded-[2px] bg-[rgba(255,255,255,0.15)] backdrop-blur-[8px] text-white border border-white">
                    {{ $hero['link_txt'] }}
                    <img src="assets/homepage/icons/arrow-down.svg" alt="Arrow Down"
                        class="inline-block ml-2 w-[24px] h-[24px]" />
                </button>
            </a>
        </div>

        <div class="absolute bottom-0 left-0 right-0 h-[36px] py-[8px] flex justify-center"
            style="background-color: rgba(72, 43, 27, 0.5)">
            <div class="flex items-center gap-[8px] justify-between w-full px-[16px] md:justify-center">
                <span class="text-primary-light-50 font-cabinet text-[16px]">Ready to bring something home?</span>
                <span class="text-primary-light-50 font-semibold font-cabinet underline"><a href="{{ route("frontend.newest") }}">Shop Now</a></span>
            </div>
        </div>
    </div>


    <div class="pt-[50px] lg:px-[120px] md:pt-[90px] overflow-hidden">
        <!-- Title -->
        <div class="px-[24px] flex flex-col justify-center text-start md:items-center">
            <div class="flex flex-col items-start md:items-center">
                <h2 class="font-plush text-primary-dark-950 text-[24px] md:text-[60px]">
                    Our Handpicked Collection
                </h2>
                <p class="font-plush text-primary-dark-500 text-[16px] md:text-[30px] text-start md:text-center">
                    Timeless pieces, thoughtfully chosen to inspire your space.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-[48px] pl-[24px] md:pl-[12px] lg:pl-[0px] overflow-x-auto">
        <div
            class="flex gap-[8px] lg:gap-[8px] lg:grid lg:grid-cols-4 lg:overflow-visible lg:max-w-[1200px] mx-auto w-max mlg:w-full">
            <!-- Card -->
            @foreach ($products as $product)
                <div
                    class="group relative border border-primary-light-200 w-[294px] h-[402px] md:w-[235px] md:h-[462px] lg:w-[294px] bg-cover bg-center transition-all duration-300 ease-in-out overflow-hidden flex flex-col justify-between">
                    <div class="relative h-full w-full overflow-hidden">
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
                    <div class="overflow-hidden h-[0px] transition-[height] duration-300 ease-in-out group-hover:h-[50px]">
                        <a href="{{ route('frontend.product.detail', $product->seo_url_param) }}">
                            <button
                                class="w-full bg-primary-light-500 py-[6px] flex justify-center items-center text-white text-[14px] font-light transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                Quick Shop
                            </button>
                        </a>
                    </div>

                    <!-- Bottom Content -->
                    <div class="z-10 p-[14px] transition-all duration-300 ease-in-out md:h-32 ">
                        <div class="flex justify-between items-start">
                            <div class="">
                                <p class="font-cabinet text-[20px] font-bold leading-none mb-[5px]">
                                    {{ $product->title }}
                                </p>
                                <p class="font-cabinet text-[16px] font-bold text-primary-light-500 leading-none">
                                    IDR {{ number_format($product->final_price, 0, ',', '.') }}
                                </p>
                                @if ($product->max_discount !== null)
                                    <div class="flex gap-2 mt-1">
                                        <p class="font-cabinet text-[12px] font-bold text-secondary-terra-700 leading-none">
                                            @if ($product->max_discount_type === 'nominal')
                                                IDR {{ number_format($product->max_discount, 0, ',', '.') }} off
                                            @elseif($product->max_discount_type === 'percent')
                                                {{ rtrim(rtrim(number_format($product->max_discount, 2, ',', '.'), '0'), ',') }}%
                                                off
                                            @endif
                                        </p>
                                        <p
                                            class="font-cabinet text-[12px] font-bold text-neutral-dark-300 leading-none line-through">
                                            IDR {{ number_format($product->getPriceProduct(), 0, ',', '.') }}
                                        </p>
                                    </div>
                                @else
                                    <div class="h-4"></div>
                                @endif
                            </div>
                            <img src="{{ asset('assets/homepage/icons/love-white.svg') }}" alt="favorite" />
                        </div>

                        <!-- Button with slide-up animation -->
                    </div>

                    <!-- Gradient Overlay -->
                    {{-- <div class="absolute bottom-0 left-0 w-full h-[270px] pointer-events-none z-0"
                        style="
                            background: linear-gradient(
                                180deg,
                                rgba(255, 255, 255, 0) 31.85%,
                                #fff 79.07%
                            );
                        ">
                    </div> --}}
                </div>
            @endforeach


            <div class="hidden md:absolute top-[calc(50%-20px)] left-[-24px] flex justify-between z-10 nav-buttons">
                <button
                    class="bg-primary-dark-950 hover:bg-primary-dark-700 border-[2px] border-white rounded-full p-[10px] shadow-md transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
                        fill="none">
                        <path
                            d="M16.2989 20.1011L10.1977 14L16.2989 7.8988L17.1127 8.7126L11.8253 14L17.1127 19.2873L16.2989 20.1011Z"
                            fill="white" />
                    </svg>
                </button>
            </div>
            <div class="hidden md:absolute top-[calc(50%-20px)] right-[-24px] flex justify-between z-10 nav-buttons">
                <button
                    class="bg-primary-dark-950 hover:bg-primary-dark-700 border-[2px] border-white rounded-full p-[10px] shadow-md transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
                        fill="none">
                        <path
                            d="M15.485 14L10.1976 8.7126L11.0114 7.8988L17.1126 14L11.0114 20.1011L10.1976 19.2873L15.485 14Z"
                            fill="white" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Signature Creations -->
    <div class="mt-[50px] md:mt-[90px]">
        <div class="px-[24px] md:px-0 flex flex-col justify-center items-center mb-[24px] md:mb-[56px]">
            <div class="flex flex-col items-start md:items-center">
                <h2 class="font-plush text-primary-dark-950 text-[24px] md:text-[60px]">
                    Signature Creations
                </h2>
                <p class="font-plush text-primary-dark-500 text-[16px] md:text-[20px] md:text-center md:w-[700px]">
                    A selection of our most defining works. Crafted to reflect beauty,
                    soul, and the quiet power of honest design.
                </p>
            </div>
        </div>
        <div class="px-[16px] md:px-[50px] lg:px-[120px]">
            <div class="grid grid-cols-2 gap-2 lg:gap-0">
                @foreach ($signature as $item)
                    <div class="w-full h-[200px] md:h-auto relative group overflow-hidden hover:border hover:border-white">
                        <img src="{{ asset('storage/signature/' . $item->picture) }}" alt=""
                            class="w-full md:group-hover:opacity-0 md:transition-opacity md:duration-300" />
                        <img src="{{ asset('storage/signature/' . $item->picture_hover) }}" alt=""
                            class="w-full absolute top-0 left-0 lg:opacity-0 lg:group-hover:opacity-100 lg:transition-opacity lg:duration-300" />
                        <div class="absolute px-[10px] py-[6px] md:px-[24px] md:py-[20px] flex justify-between items-center bottom-0 left-[14px] right-[14px] lg:opacity-0 lg:group-hover:opacity-100 lg:translate-y-4 lg:group-hover:translate-y-0 transition-all duration-300 mb-4"
                            style="background: rgba(255, 255, 255, 0.9)">
                            <div>
                                <p class="text-[10px] md:text-[16px] font-cabinet text-primary-light-500">
                                    {{ $item->category }}
                                </p>
                                <p class="text-[14px] md:text-[24px] font-plush">
                                    {{ $item->title }}
                                </p>
                            </div>
                            <button onclick="window.location.href='{{ $item->link_url }}'"
                                class="hidden lg:block bg-primary-light-500 rounded-[2px] font-[400] hover:bg-primary-dark-700 text-white font-cabinet text-[16px] h-[40px] px-[12px] shadow-md">
                                View Details
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Category -->
    <div class="mt-[90px]">
        <div class="px-[24px] md:px-0 flex flex-col justify-center items-center mb-[56px]">
            <div class="flex flex-col md:items-center">
                <h2 class="font-plush text-primary-dark-950 text-[24px] md:text-[60px]">
                    Shop By Category
                </h2>
                <p class="font-plush text-primary-dark-500 text-[16px] md:text-[20px] md:text-center md:w-[700px]">
                    Discover thoughtfully curated categories. Each one grounded in
                    natural textures and timeless craftsmanship to bring harmony into
                    your home.
                </p>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-0">
            @foreach ($featuredCategory as $item)
                <a href="{{ url($item->link_url) }}">
                    <div class="relative group w-full cursor-pointer">

                        <!-- Bungkus gambar -->
                        <div class="w-full h-[300px] lg:h-[900px] overflow-hidden">
                            <img src="{{ asset('storage/featured_category/' . $item->content_path) }}" alt=""
                                class="w-full h-full object-cover" />
                        </div>

                        <div
                            class="absolute inset-0 bg-gradient-to-b from-transparent to-black lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-300">
                        </div>

                        <p style="background: rgba(0, 0, 0, 0.3)"
                            class="hidden md:absolute font-bold bottom-0 left-0 right-0 px-[16px] py-[8px] text-white text-[32px] font-cabinet lg:opacity-100 lg:group-hover:opacity-0 transition-opacity duration-300">
                            {{ $item->title }}
                        </p>
                        <div
                            class="absolute bottom-0 left-0 right-0 p-[16px] lg:p-[24px] text-white lg:opacity-0 lg:translate-y-full lg:group-hover:opacity-100 lg:group-hover:translate-y-0 transition-all duration-300 ease-out">
                            <h3
                                class="text-[24px] lg:text-[48px] font-cabinet font-bold {{ $loop->last ? 'underline' : '' }}">
                                {{ $item->title }}
                            </h3>
                            <p class="text-[12px] lg:text-[16px] font-cabinet">
                                {{ $item->sub_title }}
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Pieces for the Everyday -->
    <div class="my-[50px] lg:px-[120px] lg:my-[90px] w-full overflow-hidden md:overflow-visible">
        <div class="flex flex-col justify-center items-center md:mb-[56px]">
            <div class="flex flex-col md:items-center">
                <h2 class="font-plush text-primary-dark-950 text-[24px] md:text-[60px]">
                    Pieces for the Everyday
                </h2>
                <p class="font-plush text-primary-dark-500 text-[16px] md:text-[20px] md:text-center md:w-[700px]">
                    Inspiration for moments you live in, not just look at.
                </p>
            </div>

            <!-- Desktop -->
            <div class="hidden lg:flex gap-2 mt-[48px]">
                <div>
                    @if (isset($inspirations[0]))
                        <div class="w-[708px] h-[759px] bg-cover bg-center relative group"
                            style="background-image: url('{{ asset('storage/inspiration/' . $inspirations[0]->picture) }}')">
                            <div
                                class="absolute inset-0 backdrop-blur-sm md:opacity-0 md:group-hover:opacity-100 md:transition-opacity duration-300">
                            </div>
                            <div
                                class="absolute border border-white bottom-[8px] left-[8px] right-[8px] px-[24px] py-[20px] text-[#141414] bg-white/60 opacity-0 md:translate-y-full md:group-hover:opacity-100 md:group-hover:translate-y-0 transition-all duration-300 ease-out">
                                <p class="font-plush text-[24px]">
                                    {{ $inspirations[0]->title }}
                                </p>
                                <button
                                    class="mt-[16px] bg-primary-light-500 hover:bg-primary-dark-700 text-white font-cabinet rounded-[2px] px-[12px] py-[10px] flex items-center justify-center">
                                    <a href="{{ $inspirations[0]->link_url }}"><span
                                            class="text-[16px] font-cabinet text-white">{{ $inspirations[0]->link_txt }}</span></a>
                                    <img src="{{ asset('assets/homepage/icons/arrow-up-double.svg') }}" alt="Arrow Right"
                                        class="inline-block ml-2 w-[24px] h-[24px]" />
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="flex flex-col gap-2">
                    @if (isset($inspirations[1]))
                        <div class="w-[482px] h-[503px] bg-cover bg-center relative group"
                            style="background-image: url('{{ asset('storage/inspiration/' . $inspirations[1]->picture) }}')">
                            <div
                                class="absolute inset-0 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                            <div
                                class="absolute border border-white bottom-[8px] left-[8px] right-[8px] px-[16px] py-[12px] text-[#141414] bg-white/60 opacity-0 translate-y-full group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 ease-out">
                                <p class="font-plush text-[18px]">
                                    {{ $inspirations[1]->title }}
                                </p>
                                <button
                                    class="mt-[16px] bg-primary-light-500 hover:bg-primary-dark-700 text-white font-cabinet rounded-[2px] px-[8px] py-[6px] flex items-center justify-center">
                                    <a href="{{ $inspirations[1]->link_url }}"><span
                                            class="text-[16px] font-cabinet text-white">{{ $inspirations[1]->link_txt }}</span></a>
                                    <img src="{{ asset('assets/homepage/icons/arrow-up-double.svg') }}" alt="Arrow Right"
                                        class="inline-block ml-2 w-[16px] h-[16px]" />
                                </button>
                            </div>
                        </div>
                    @endif

                    @if (isset($inspirations[2]))
                        <div class="w-[482px] h-[248px] bg-cover bg-center relative group"
                            style="background-image: url('{{ asset('storage/inspiration/' . $inspirations[2]->picture) }}')">
                            <div
                                class="absolute inset-0 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                            <div
                                class="absolute border border-white bottom-[8px] left-[8px] right-[8px] px-[16px] py-[12px] text-[#141414] bg-white/60 opacity-0 translate-y-full group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 ease-out">
                                <p class="font-plush text-[18px]">
                                    {{ $inspirations[2]->title }}
                                </p>
                                <button
                                    class="mt-[16px] bg-primary-light-500 hover:bg-primary-dark-700 text-white font-cabinet rounded-[2px] px-[8px] py-[6px] flex items-center justify-center">
                                    <a href="{{ $inspirations[2]->link_url }}"><span
                                            class="text-[16px] font-cabinet text-white">{{ $inspirations[2]->link_txt }}</span></a>
                                    <img src="{{ asset('assets/homepage/icons/arrow-up-double.svg') }}" alt="Arrow Right"
                                        class="inline-block ml-2 w-[16px] h-[16px]" />
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Mobile -->
            <div class="lg:hidden mt-[24px] flex gap-3 w-full overflow-hidden">
                <div class="w-full md:px-4 overflow-x-visible relative">
                    <div class="swiper overflow-visible relative">
                        <div class="swiper-wrapper !h-[241px] !md:h-[402px]">
                            @foreach ($inspirations as $item)
                                <div class="swiper-slide !w-[85%] bg-gray-900 text-white rounded-xl">
                                    <div class="w-full h-full bg-cover relative bg-center"
                                        style="background-image: url('{{ asset('storage/inspiration/' . $item->picture) }}')">
                                        <div
                                            class="absolute border border-white bottom-[8px] left-[8px] right-[8px] px-[16px] py-[12px] text-[#141414] bg-white/60">
                                            <p class="font-plush text-[14px] md:text-[18px]">
                                                {{ $item->title }}
                                            </p>
                                            <button
                                                class="mt-[16px] bg-primary-light-500 hover:bg-primary-dark-700 text-white font-cabinet rounded-[2px] px-[8px] py-[6px] flex items-center justify-center">
                                                <a href="{{ $item->link_url }}"><span
                                                        class="text-[16px] font-cabinet text-white">{{ $item->link_txt }}</span></a>
                                                <img src="./assets/homepage/icons/arrow-up-double.svg" alt="Arrow Right"
                                                    class="inline-block ml-2 w-[16px] h-[16px]" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="swiper-slide !w-[85%] bg-gray-900 text-white rounded-xl">
                                <div class="w-full h-full bg-cover relative bg-center"
                                    style="background-image: url('{{ asset('assets/homepage/images/pieces/pieces-1.svg') }}')">
                                    <div
                                        class="absolute border border-white bottom-[8px] left-[8px] right-[8px] px-[16px] py-[12px] text-[#141414] bg-white/60">
                                        <p class="font-plush text-[14px] md:text-[18px]">
                                            Little trays, quiet plants, and decor that make your
                                            shelf feel intentional—not just pretty.
                                        </p>
                                        <button
                                            class="mt-[16px] bg-primary-light-500 hover:bg-primary-dark-700 text-white font-cabinet rounded-[2px] px-[8px] py-[6px] flex items-center justify-center">
                                            <span class="text-[14px] font-cabinet text-white">See Home Accents</span>
                                            <img src="./assets/homepage/icons/arrow-up-double.svg" alt="Arrow Right"
                                                class="inline-block ml-2 w-[16px] h-[16px]" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button id="custom-swiper-prev"
                            class="absolute flex pl-[5px] justify-center items-center left-[-7px] top-1/2 -translate-y-1/2 z-50 border-2 border-white bg-primary-dark-950 w-[50px] h-[47px] rounded-full">
                            <img src="assets/homepage/icons/arrow-left-white.svg" alt="">
                        </button>
                        <button id="custom-swiper-next"
                            class="absolute flex pl-[4px] right-[-7px] top-1/2 -translate-y-1/2 z-50 border-2 border-white justify-center items-center bg-primary-dark-950 w-[50px] h-[47px] rounded-full">
                            <img src="assets/homepage/icons/arrow-right-white.svg" alt="">
                        </button>
                    </div>

                    <div id="swiper-pagination" class="flex justify-center mt-[20px]"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- GIF -->
    <div>
        <div class="flex justify-center w-full relative">
            @if ($setting)
                <div class="video_footer h-fit w-full relative overflow-hidden">
                    <div id="ytbg" data-ytbg-fade-in="true" data-youtube="{{ $setting['value'] }}"></div>

                    <div class="absolute top-0 left-0 right-0 h-15 bg-transparent z-10 pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 right-0 h-15 bg-transparent z-10 pointer-events-none"></div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('css')
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-3.4.1/jquery-3.4.1.slim.min.js') }}"></script>
    <style>
        .video_footer {
            position: relative;
            width: 100vw;
            height: 56.25vw;
            overflow: hidden;
        }

        .video_footer iframe {
            width: 100vw;
            height: 56.25vw;
            pointer-events: none;
            border: none;
            transform: scale(1.10);
            transform-origin: center;
        }

        .video-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .video-bg iframe {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100vw;
            height: 56.25vw;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .video-bg iframe {
                width: 177.78vh;
                height: 100vh;
            }
        }
    </style>

@endsection

@section('script')

    <script src="{{ asset('assets/js/jquery.youtube-background.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('[data-youtube], [data-vbg]').youtube_background();
        });
    </script>
    <script>
        /*jQuery(document).ready(function() {
                        jQuery('[data-youtube]').youtube_background();

                        
                    });*/
        $('#modalImport').modal('show');
        /*if ($(window).width() < 767) {
                $(".slick-product").slick();
            }
            else {
                $(".slick-product").slick({
                    variableWidth: true,
                    slidesToShow: 1,
                });
            }*/
        $('.exampleSlider').multislider({
            slideAll: false,
            interval: 0,

        });

        $('.exampleSliderCol').multislider({
            slideAll: false,
            interval: 0,

        });

        $("#video_profile").videoPopup({
            // autoplay on open
            autoplay: true,

            // shows video controls
            showControls: false,

            // colors of controls
            controlsColor: null,

            // infinite loop
            loopVideo: true,

            // shows video information
            showVideoInformations: false,

            // width
            width: null
        });
    </script>
@endsection
