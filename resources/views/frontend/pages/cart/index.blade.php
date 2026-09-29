@extends('frontend.layouts.home')

@section('title', 'Shopping Cart')
@section('meta_robots', 'noindex, nofollow')

@section('content')
    <div class="md:px-0 md:px-[50px] lg:px-[120px] md:mt-[32px]" x-data="cartPage()" x-init="loadCart()">
        <div class="flex justify-center" x-show="items.length > 0">
            <div class="md:max-w-[1200px] w-full justify-center">
                <h1
                    class="mx-[16px] pt-[16px] md:pt-0 text-primary-dark-950 font-plush font-normal text-[30px] md:text-[36px]">
                    Your Cart</h1>
                <p class="mx-[16px] hidden md:block text-[16px] font-cabinet ">Take a moment to review the pieces you're
                    considering</p>
                <div class="flex flex-col lg:flex-row gap-[16px] justify-center my-[16px] lg:my-[24px] w-full">
                    <div class=" w-full lg:ml-[16px] lg:mx-0 lg:w-[782px] h-fit flex flex-col lg:border md:border-[#E7E7E7]">
                        <hr class="md:hidden" />
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="flex mx-[16px] lg:mx-0 py-[16px] py-0 lg:p-[16px] relative">
                                <div class=" w-[83px] h-[83px] md:w-[125px] md:h-[125px] mr-[16px]">
                                    <img :src="item.product.main_pic ? item.product.main_pic :
                                        '{{ asset('assets/images/no-image.png') }}'"
                                        :alt="item.product.title" class="w-full h-full object-cover rounded-[2px]" />
                                </div>
                                <div class=" flex flex-col justify-between w-full">
                                    <div class="flex w-full justify-between">
                                        <div class="flex flex-col w-[132px] md:w-[200px]">
                                            <p class=" text-neutral-dark-900 font-cabinet font-medium text-[16px] md:text-[24px]"
                                                x-text="item.product.title">
                                            </p>
                                            <div class="flex flex-row md:flex-col gap-[4px]">
                                                <p class=" font-cabinet text-[#88888] text-[12px]">Size : <span
                                                        class=" font-cabinet text-[14px] text-neutral-dark-900"
                                                        x-text="item.variant.name">
                                                    </span>
                                                </p>
                                                <p class=" font-cabinet text-[#88888] text-[12px]">Color :
                                                    <span class=" font-cabinet text-[14px] text-neutral-dark-900"
                                                        x-text="item.color.label">
                                                    </span>
                                                </p>
                                            </div>

                                            <div class="flex w-[200px] flex-col md:hidden absolute bottom-[16px]">
                                                <p x-show="item.max_discount !== null"
                                                    class=" leading-[1.2] text-neutral-dark-600 font-cabinet text-[12px] md:text-[10px]">
                                                    IDR
                                                    <span class=" font-bold text-[14px] line-through"
                                                        x-text="(item.price * item.qty).toLocaleString('id-ID')">
                                                    </span>
                                                </p>
                                                <p class="leading-[1.2] text-[#D85640] font-cabinet text-[16px]">
                                                    IDR <span class="font-bold text-[18px]"
                                                        x-text="(item.final_price * item.qty).toLocaleString('id-ID')"></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class=" md:space-x-4 md:py-[8px] ">
                                            <div class="flex items-center hidden md:flex border border-primary-light-100">
                                                <button @click="if (item.qty > 1) item.qty--" :disabled="item.qty <= 1"
                                                    :class="item.qty <= 1 ?
                                                        'bg-[#E7E7E7] cursor-not-allowed' :
                                                        'bg-primary-light-100 cursor-pointer'"
                                                    class="flex items-center justify-center px-2 py-1 w-[40px] h-[43px] transition-colors">
                                                    -
                                                </button>
                                                <span x-text="item.qty"
                                                    class="px-2 w-[60px] h-[43px] flex items-center justify-center"></span>
                                                <button @click="if (item.qty < item.stock) item.qty++"
                                                    :disabled="item.qty >= item.stock"
                                                    :class="item.qty >= item.stock ?
                                                        'bg-[#E7E7E7] cursor-not-allowed' :
                                                        'bg-primary-light-100 cursor-pointer'"
                                                    class="flex items-center justify-center px-2 py-1 w-[40px] h-[43px] transition-colors">
                                                    +
                                                </button>
                                            </div>
                                            <button @click="removeItem(item.id, index)"
                                                class=" cursor-pointer underline text-neutral-dark-500 font-cabinet text-[16px] md:hidden">
                                                Remove
                                            </button>
                                        </div>
                                        <div class="hidden md:flex w-[200px] items-end flex-col">
                                            <p
                                                class="hidden md:block leading-[1.2] text-[#D85640] font-cabinet text-[16px]">
                                                IDR
                                                <span class=" font-bold text-[18px]"
                                                    x-text="(item.final_price * item.qty).toLocaleString('id-ID')">
                                                </span>
                                            </p>
                                            <p x-show="item.max_discount !== null"
                                                class=" leading-[1.2] text-neutral-dark-600 font-cabinet text-[12px] md:text-[10px]">
                                                IDR
                                                <span class=" font-bold text-[14px] line-through"
                                                    x-text="(item.price * item.qty).toLocaleString('id-ID')">
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class=" hidden md:flex justify-end gap-[16px]">
                                        <p class=" underline text-neutral-dark-500 font-cabinet text-[16px]">Move to
                                            wishlist</p>
                                        <p @click="removeItem(item.id, index)"
                                            class="cursor-pointer underline text-neutral-dark-500 font-cabinet text-[16px]">
                                            Remove</p>
                                    </div>
                                    <div class=" space-x-4 py-[8px] flex md:hidden justify-end">
                                        <div class="flex items-center hidden md:flex border border-primary-light-100">
                                            <button @click="if (item.qty > 1) item.qty--" :disabled="item.qty <= 1"
                                                :class="item.qty <= 1 ?
                                                    'bg-[#E7E7E7] cursor-not-allowed' :
                                                    'bg-primary-light-100 cursor-pointer'"
                                                class="flex items-center justify-center px-2 py-1 w-[40px] h-[43px] transition-colors">
                                                -
                                            </button>
                                            <span x-text="item.qty"
                                                class="px-2 w-[60px] h-[43px] flex items-center justify-center"></span>
                                            <button @click="if (item.qty < item.stock) item.qty++"
                                                :disabled="item.qty >= item.stock"
                                                :class="item.qty >= item.stock ?
                                                    'bg-[#E7E7E7] cursor-not-allowed' :
                                                    'bg-primary-light-100 cursor-pointer'"
                                                class="flex items-center justify-center px-2 py-1 w-[40px] h-[43px] transition-colors">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="absolute bottom-0 left-0 right-0 md:left-[16px] md:right-[16px] h-px bg-[#E7E7E7]">
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class=" w-full lg:w-[370px] p-[16px] lg:border h-fit sticky top-[16px]">
                        <div class="flex justify-between border-b pb-[12px] gap-[16px]">
                            <p class="font-cabinet text-[16px] text-neutral-dark-900">Subtotal</p>
                            <p class="font-cabinet text-[16px] text-neutral-dark-900 font-bold">
                                IDR <span x-text="subtotal.toLocaleString('id-ID')"></span>
                            </p>
                        </div>

                        <div class="flex justify-between border-b py-[12px] gap-[16px]">
                            <p class="font-cabinet text-[16px] text-neutral-dark-900">Discount</p>
                            <p class="font-cabinet text-[16px] text-neutral-dark-900 font-bold">
                                - IDR <span x-text="totalDiscount.toLocaleString('id-ID')"></span>
                            </p>
                        </div>

                        <div class="flex justify-between border-b py-[12px] gap-[16px]">
                            <p class="font-cabinet text-[16px] text-neutral-dark-900">Shipping</p>
                            <p class="font-cabinet text-[12px] text-neutral-dark-900 text-end">
                                IDR <span x-text="shippingCost.toLocaleString('id-ID')"></span>
                            </p>
                        </div>

                        <div class="flex justify-between border-b py-[16px] gap-[16px]">
                            <p class="font-cabinet text-[24px] text-neutral-dark-900 font-bold">Total</p>
                            <p class="font-cabinet text-[24px] text-neutral-dark-900 font-bold text-end">
                                IDR <span x-text="total.toLocaleString('id-ID')"></span>
                            </p>
                        </div>

                        <button
                            class="bg-primary-dark-950 w-full hover:bg-primary-dark-700 text-white px-[12px] py-[10px] rounded-[2px] font-cabinet text-[16px] flex items-center justify-center"
                            @click="updateCart()">
                            Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex w-full justify-center items-center mt-[32px] flex-col" x-show="items.length === 0">
            <h2 class=" text-[36px] font-plush text-primary-dark-950 text-center font-medium leading-[1.2]">Your cart is
                empty. <br> Need a starting point?</h2>
            <p class=" text-[20px] font-cabinet text-primary-dark-950 pt-[8px]">Our picked collections are a great
                place.</p>
            <a href="{{ route('frontend.newest') }}"
                class=" mt-[32px] flex gap-2 justify-between font-cabinet bg-primary-light-500 hover:bg-primary-light-700 text-white px-[8px] py-[6px] rounded-[2px] text-[14px]">
                Shop Picked Collections
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewBox="0 0 16 17" fill="none">
                    <path
                        d="M4.41083 14.5436C4.10372 14.5436 3.8475 14.441 3.64216 14.2356C3.43683 14.0303 3.33394 13.7741 3.3335 13.467V6.28697C3.3335 5.98031 3.43638 5.72431 3.64216 5.51897C3.84794 5.31364 4.10416 5.21075 4.41083 5.21031H5.66683V4.87697C5.66683 4.22897 5.89372 3.67808 6.3475 3.22431C6.80127 2.77053 7.35216 2.54364 8.00016 2.54364C8.64816 2.54364 9.19905 2.77053 9.65283 3.22431C10.1066 3.67808 10.3335 4.22897 10.3335 4.87697V5.21031H11.5902C11.8968 5.21031 12.1528 5.3132 12.3582 5.51897C12.5635 5.72475 12.6664 5.98097 12.6668 6.28764V13.467C12.6668 13.7736 12.5639 14.0299 12.3582 14.2356C12.1524 14.4414 11.8964 14.5441 11.5902 14.5436H4.41083ZM4.41083 13.877H11.5902C11.6924 13.877 11.7864 13.8343 11.8722 13.749C11.9579 13.6636 12.0006 13.5694 12.0002 13.4663V6.28764C12.0002 6.18497 11.9575 6.09075 11.8722 6.00497C11.7868 5.9192 11.6926 5.87653 11.5895 5.87697H10.3335V7.54364C10.3335 7.63875 10.3017 7.71808 10.2382 7.78164C10.1746 7.8452 10.0953 7.87697 10.0002 7.87697C9.90505 7.87697 9.82572 7.8452 9.76216 7.78164C9.69861 7.71808 9.66683 7.63875 9.66683 7.54364V5.87697H6.3335V7.54364C6.3335 7.63875 6.30172 7.71808 6.23816 7.78164C6.17461 7.8452 6.09527 7.87697 6.00016 7.87697C5.90505 7.87697 5.82572 7.8452 5.76216 7.78164C5.69861 7.71808 5.66683 7.63875 5.66683 7.54364V5.87697H4.41083C4.30816 5.87697 4.21394 5.91964 4.12816 6.00497C4.04238 6.09031 3.99972 6.18453 4.00016 6.28764V13.467C4.00016 13.5692 4.04283 13.6632 4.12816 13.749C4.2135 13.8348 4.3075 13.8774 4.41016 13.877M6.3335 5.21031H9.66683V4.87697C9.66683 4.40764 9.50639 4.01253 9.1855 3.69164C8.86461 3.37075 8.4695 3.21031 8.00016 3.21031C7.53083 3.21031 7.13572 3.37075 6.81483 3.69164C6.49394 4.01253 6.3335 4.40764 6.3335 4.87697V5.21031Z"
                        fill="white" />
                </svg>
            </a>
        </div>
        <div class=" mt-[50px] px-[16px] md:px-[50px] lg:px-[120px] md:mt-[90px]" x-show="items.length > 0">
            <div class=" flex flex-col justify-center text-start md:items-center">
                <div class="flex flex-col items-start md:items-center">
                    <h2 class="font-plush text-primary-dark-950 text-[24px] md:text-[40px] lg:text-[60px]">
                        Maybe You’ll Love These Too
                    </h2>
                    <p class="font-plush text-primary-dark-950 text-[16px] md:text-[30px] text-start md:text-center">
                        Other quiet pieces you might love. Chosen to live well, just like the one before.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-[48px] pl-[16px] overflow-x-auto" x-show="items.length > 0">
            <div
                class="flex gap-[8px] lg:grid lg:grid-cols-4 lg:overflow-visible lg:max-w-[1200px] mx-auto w-max lg:w-full">
                <!-- Card -->
                @foreach ($top_products as $product)
                    <div
                        class="group relative border border-primary-light-200 w-[294px] h-[402px] md:w-[235px] md:h-[462px] lg:w-[294px] bg-cover bg-center transition-all duration-300 ease-in-out overflow-hidden flex flex-col justify-between">
                        <div class="relative w-full  md:h-[533px] overflow-hidden">
                            <img src="{{ $product->main_pic ?? asset('assets/images/no-image.png') }}"
                                alt="{{ $product->title }}"
                                class="w-full h-full object-cover transition-all duration-300 group-hover:opacity-0">
                            <img src="{{ $product->hover_pic ?? ($product->main_pic ?? asset('assets/images/no-image.png')) }}"
                                alt="{{ $product->title }} hover"
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
    </div>
@endsection

@section('script')
    <script>
        function cartPage() {
            return {
                items: [],
                shippingCost: 0,

                get subtotal() {
                    return this.items.reduce((sum, i) => {
                        let basePrice = (i.price || 0) + (i.additional_price || 0);
                        return sum + (i.qty * basePrice);
                    }, 0);
                },

                get totalDiscount() {
                    return this.items.reduce((sum, i) => {
                        let basePrice = (i.price || 0) + (i.additional_price || 0);
                        return sum + (i.qty * (basePrice - (i.final_price || basePrice)));
                    }, 0);
                },

                get total() {
                    return this.subtotal - this.totalDiscount + this.shippingCost;
                },

                async loadCart() {
                    let res = await Alpine.store("api").get('/api/cart');
                    this.items = res.data;

                    this.shippingCost = res.shipping_cost || 0;
                },

                async removeItem(productId, index) {
                    await Alpine.store("api").post(`/api/cart/remove`, {
                        detail_product_id: productId
                    });
                    this.items.splice(index, 1);
                },

                async updateCart() {
                    let res = await Alpine.store("api").post(`/api/cart/update`, {
                        items: this.items.map(i => ({
                            detail_product_id: i.id,
                            qty: i.qty
                        }))
                    });

                    this.loadCart();

                    if (res.status === 200) {
                        window.location.href = res.data.redirect_url;
                    } else if (res.status === 401) {
                        window.location.href = res.data.redirect_url;
                    }
                }
            }
        }
    </script>
@endsection
