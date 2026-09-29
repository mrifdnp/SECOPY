@extends('frontend.layouts.home')

@section('title', $product->title)

@section('content')
    <div class=" md:px-[50px] lg:px-[120px]" x-data="{
        selectedColorId: '{{ $selectedColorId }}',
        selectedSize: '{{ $selectedSize }}',
        productDetail: [],
        qty: 1,
        maxQty: 1,
        description: [],
        showToastError: false,
        toastErrorMessage: '',
        showToastSuccess: false,
        toastSuccessMessage: '',
        async loadProductDetail(colorId = null) {
            let id = colorId ?? this.selectedColorId;
            let res = await $store.api.get(`/api/product/price/${id}/{{ $product->id }}`);
            this.productDetail = res.productDetail;
    
            let selectedVariant = this.productDetail.find(v => v.variant.name === this.selectedSize);
            if (!selectedVariant && this.productDetail.length > 0) {
                selectedVariant = this.productDetail[0];
                this.selectedSize = selectedVariant.variant.name;
            }
    
            if (selectedVariant) {
                this.maxQty = selectedVariant.qty;
                this.qty = Math.min(this.qty, this.maxQty);
                this.description = selectedVariant.variant.json_description;
            }
        },
    
        setSelectedSize(sizeName) {
            this.selectedSize = sizeName;
            let selectedVariant = this.productDetail.find(v => v.variant.name === sizeName);
            if (selectedVariant) {
                this.maxQty = selectedVariant.qty;
                this.qty = 1;
                this.description = selectedVariant.variant.json_description;
            }
        },
        changeColor(colorId) {
            this.selectedColorId = colorId;
            this.loadProductDetail(colorId);
        },
        async addToWishlist() {
            try {
                let variant = this.productDetail.find(v => v.variant.name === this.selectedSize);
                if (!variant) {
                    this.toastErrorMessage = 'Please select a variant';
                    this.showToastError = true;
                    return;
                }
    
                let response = await $store.api.post('{{ route('frontend.wishlist.store') }}', {
                    detail_product_id: variant.id
                });
    
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
    
                this.toastSuccessMessage = response.message || 'Product added to cart successfully';
                this.showToastSuccess = true;
    
            } catch (err) {
                this.toastErrorMessage = err.response?.data?.message ||
                    err.message ||
                    'Something went wrong';
                this.showToastError = true;
            }
        },
        async addToCart() {
            try {
                let variant = this.productDetail.find(v => v.variant.name === this.selectedSize);
                if (!variant) {
                    this.toastErrorMessage = 'Please select a variant';
                    this.showToastError = true;
                    return;
                }
    
                let res = await $store.api.post('{{ route('frontend.product.addToCart') }}', {
                    qty: this.qty,
                    detail_product_id: variant ? variant.id : null,
                });
    
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
    
                this.toastSuccessMessage = res.message || 'Product added to cart successfully';
                this.showToastSuccess = true;
            } catch (err) {
                this.toastErrorMessage = err.response?.data?.message ||
                    err.message ||
                    'Something went wrong';
                this.showToastError = true;
            }
        }
    
    }" x-init="loadProductDetail()">
        <div class="flex flex-col lg:flex-row pt-[24px]">
            <div x-data="{
                pictures: @js($pictures),
                activeIndex: 0
            }" class="flex overflow-scroll lg:overflow-visible">
                <div class="hidden lg:block flex flex-col gap-2">
                    <template x-for="(picture, i) in pictures" :key="i">
                        <div class="w-[89px] h-[110px] cursor-pointer"
                            :class="activeIndex === i ? 'border-[4px] border-primary-dark-950' : ''"
                            @click="activeIndex = i">
                            <img :src="picture.path" alt="" class="w-full h-full object-cover">
                        </div>
                    </template>
                </div>

                <!-- Image utama -->
                <div class="lg:mx-[32px]">
                    <div class="overflow-x-auto">
                        <div class="flex flex-nowrap lg:flex-col gap-[5px] md:gap-[16px]">

                            <div class="w-[341px] md:w-[591px] md:h-[751px] flex-shrink-0">
                                <img :src="pictures[activeIndex].path" alt="" class="w-full h-full object-cover">
                            </div>

                            <template 
                                x-for="(picture, i) in pictures.filter((_, idx) => idx !== activeIndex).slice(0, 2)" 
                                :key="i"
                            >
                                <div class="w-[341px] md:w-[591px] md:h-[751px] flex-shrink-0">
                                    <img :src="picture.path" alt="" class="w-full h-full object-cover">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full px-[16px] md:px-0 mt-[16px] lg:mt-0">
                <div class="flex flex-col">
                    @if ($product->top_pick === true)
                        <p class=" text-secondary-terra-600 font-cabinet text-[16px] leading-[1.2]">Top Picks</p>
                    @endif
                    <h1 class=" text-primary-dark-950 text-[36px] font-plush font-medium">{{ $product->title }}
                    </h1>
                    <p class=" font-cabinet text-primary-dark-950">{{ $category->title }}</p>
                    <div class="flex gap-3">
                        <p class="font-cabinet text-[20px] font-bold leading-[1.2] text-ivory-700 my-[16px]">
                            <span
                                x-text="(productDetail.length > 0 && selectedSize) 
                                    ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', currencyDisplay: 'code' })
                                        .format(productDetail.find(v => v.variant.name === selectedSize)?.final_price || 0) 
                                    : 'IDR 0'">
                            </span>
                        </p>
                        <p x-show="productDetail.find(v => v.variant.name === selectedSize)?.max_discount !== null"
                            class="font-cabinet text-[16px] font-bold leading-[1.2] text-gray-400 my-[16px]">
                            <span class="line-through"
                                x-text="(productDetail.length > 0 && selectedSize) 
                                    ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', currencyDisplay: 'code' })
                                        .format(productDetail.find(v => v.variant.name === selectedSize)?.price || 0) 
                                    : 'IDR 0'">
                            </span>
                        </p>
                    </div>

                    <div class="flex flex-col">
                        <p class="text-[14px]">Color</p>
                        <div class="flex space-x-4 py-[8px]">
                            @foreach ($colors as $color)
                                <div @click="changeColor('{{ $color->id }}')"
                                    :class="{
                                        'p-1 border rounded-full box-border cursor-pointer': true,
                                        'border-[#BE7743]': selectedColorId === '{{ $color->id }}',
                                        'border-transparent': selectedColorId !== '{{ $color->id }}'
                                    }">
                                    <div class="w-[24px] h-[24px] rounded-full"
                                        style="background-color: {{ $color->hexa }};"></div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                    <div class="flex flex-col">
                        <p class="text-[14px] mb-2">Size</p>
                        <div class="flex space-x-4 py-[8px] overflow-scroll">
                            <template x-for="variant in productDetail" :key="variant.id">
                                <div @click="setSelectedSize(variant.variant.name)"
                                    :class="{
                                        'flex relative !ml-0 !mr-4 flex-col items-center justify-center w-fit p-6 h-[65px] border border-primary-dark-950 cursor-pointer': true,
                                        'bg-primary-light-100 border-primary-dark-950': selectedSize === variant.variant
                                            .name,
                                        'bg-primary-light-50 border-transparent': selectedSize !== variant.variant.name
                                    }">
                                    <p class="text-[16px] font-bold font-cabinet leading-[1.2]"
                                        x-text="variant.variant.name"></p>
                                    <p class="text-[13px] font-medium font-cabinet text-primary-light-500"
                                        x-text="`IDR ${(variant.price + (variant.additional_price ?? 0)).toLocaleString()}`">
                                    </p>
                                    <div x-show="variant.max_discount !== null"
                                        x-text="variant.max_discount_type === 'nominal' 
                                        ? `Save IDR ${variant.max_discount.toLocaleString()}` 
                                        : `Save ${variant.max_discount}%`"
                                        class="absolute -top-3 -right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                                    </div>
                                    {{-- <div 
                                    x-show="variant.max_discount !== null"
                                    x-text="variant.max_discount_type === 'nominal' 
                                        ? `Save IDR ${variant.max_discount.toLocaleString()}` 
                                        : `Save ${variant.max_discount}%`"
                                    class="absolute -top-3 -left-4 bg-yellow-400 text-primary-dark-950 text-[12px] font-black px-2 py-1 rounded-full border-2 border-yellow-600 transform -rotate-12 shadow-lg">
                                    </div> --}}
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <p class="text-[14px] pb-[8px]">Qty</p>
                        <div class="flex items-center border border-primary-light-100 w-fit">
                            <button @click="if (qty > 1) qty--" :disabled="qty <= 1"
                                :class="qty <= 1 ?
                                    'bg-[#E7E7E7] cursor-not-allowed' :
                                    'bg-primary-light-100 cursor-pointer'"
                                class="flex items-center justify-center px-2 py-1 w-[40px] h-[43px] transition-colors">
                                -
                            </button>
                            <span x-text="qty" class="px-2 w-[60px] h-[43px] flex items-center justify-center"></span>
                            <button @click="if (qty < maxQty) qty++" :disabled="qty >= maxQty"
                                :class="qty >= maxQty ?
                                    'bg-[#E7E7E7] cursor-not-allowed' :
                                    'bg-primary-light-100 cursor-pointer'"
                                class="flex items-center justify-center px-2 py-1 w-[40px] h-[43px] transition-colors">
                                +
                            </button>
                        </div>
                        <p class="text-[12px] text-gray-500 mt-1" x-show="selectedSize">
                            Max: <span x-text="maxQty"></span> pcs
                        </p>
                    </div>
                    <div class="flex w-full my-[16px]">
                        <button @click="addToCart()"
                            class="bg-primary-dark-950 w-11/12 rounded-[2px] py-[10px] px-[12px] text-white">
                            Add to Cart
                        </button>
                        <div @click="addToWishlist()"
                            class="ml-2 flex items-center justify-center w-[47px] cursor-pointer bg-primary-light-500 py-[10px] px-[12px]">
                            <img src="{{ asset('assets/prod-detail/icons/love.svg') }}" alt="favorite" />
                        </div>
                    </div>

                    <div class="w-full mx-auto overflow-y-auto max-h-[700px]">
                        <template x-if="description.length > 0">
                            <template x-for="(desc, i) in description" :key="i">
                                <div x-show="desc.title || desc.description">
                                    <button @click="toggleCollapse(`details-${i}`)"
                                        class="w-full text-left py-[16px] font-semibold text-lg mb-2 flex justify-between items-center font-plush text-primary-dark-950 text-[18px]">
                                        <span x-text="desc.title"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25"
                                            viewBox="0 0 24 25" fill="none">
                                            <path
                                                d="M12.0001 14.4226C11.8928 14.4226 11.7934 14.405 11.7021 14.3696C11.6108 14.3343 11.5218 14.273 11.4351 14.1856L7.0461 9.79763C6.95276 9.70429 6.90276 9.58963 6.8961 9.45363C6.88943 9.31763 6.93943 9.19629 7.0461 9.08963C7.15276 8.98296 7.27076 8.92963 7.4001 8.92963C7.52943 8.92963 7.64743 8.98296 7.7541 9.08963L12.0001 13.3356L16.2461 9.08963C16.3394 8.99629 16.4544 8.94629 16.5911 8.93963C16.7264 8.93296 16.8474 8.98296 16.9541 9.08963C17.0608 9.19629 17.1141 9.31429 17.1141 9.44363C17.1141 9.57296 17.0608 9.69096 16.9541 9.79763L12.5651 14.1856C12.4784 14.273 12.3894 14.3343 12.2981 14.3696C12.2074 14.405 12.1081 14.4226 12.0001 14.4226Z"
                                                fill="black" />
                                        </svg>
                                    </button>
                                    <div :id="`details-${i}`" 
                                        class="text-sm text-primary-dark-800 mb-4 custom-desc"
                                        x-html="desc.description">
                                    </div>
                                    <hr>
                                </div>
                            </template>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <div class=" mt-[50px] px-[16px] lg:px-[120px] lg:mt-[90px]">
            <div class=" flex flex-col justify-center text-start md:items-center">
                <div class="flex flex-col items-start md:items-center">
                    <h2 class="font-plush text-primary-dark-950 text-[24px] md:text-[60px]">
                        Maybe You’ll Love These Too
                    </h2>
                    <p class="font-plush text-primary-dark-950 text-[16px] md:text-[30px] text-start md:text-center">
                        Other quiet pieces you might love. Chosen to live well, just like the one before.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-[48px] pl-[16px] overflow-x-auto">
            <div
                class="flex gap-[8px] lg:grid lg:grid-cols-4 lg:overflow-visible lg:max-w-[1200px] mx-auto w-max lg:w-full">
                <!-- Card -->
                @foreach ($top_products as $product)
                    <div
                        class="group relative border border-primary-light-200 w-[294px] h-[402px] md:w-[235px] md:h-[462px] lg:w-[294px] bg-cover bg-center transition-all duration-300 ease-in-out overflow-hidden flex flex-col justify-between">
                        <div class="relative w-full  md:h-[533px] overflow-hidden">
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
                            class="overflow-hidden h-[0px] transition-[height] duration-300 ease-in-out group-hover:h-[50px]">
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
                                            <p
                                                class="font-cabinet text-[12px] font-bold text-secondary-terra-700 leading-none">
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
        @include('frontend.includes.toast', [
            'type' => 'error',
            'showVariable' => 'showToastError',
            'messageVariable' => 'toastErrorMessage',
        ])
        @include('frontend.includes.toast', [
            'type' => 'success',
            'showVariable' => 'showToastSuccess',
            'messageVariable' => 'toastSuccessMessage',
        ])
    </div>

@endsection

@section('script')
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "{{ $product->title }}",
  "image": [
    "{{ $product->main_pic ? asset($product->main_pic) : asset('assets/images/no-image.png') }}"
  ],
  "description": "{{ $product->meta_description ?? 'Beli ' . $product->title . ' kualitas premium dari Seco.id' }}",
  "sku": "{{ $product->id }}",
  "brand": {
    "@type": "Brand",
    "name": "Seco"
  },
  "offers": {
    "@type": "Offer",
    "url": "{{ url()->current() }}",
    "priceCurrency": "IDR",
    "price": "{{ $product->final_price }}",
    "itemCondition": "https://schema.org/NewCondition",
    "availability": "https://schema.org/InStock"
  }
}
</script>
    <script>
        function toggleCollapse(id) {
            const content = document.getElementById(id);
            content.classList.toggle("hidden");
        }
    </script>

@endsection
