@extends('frontend.layouts.home')

@section('title', 'Shopping Cart')
@section('meta_robots', 'noindex, nofollow')

@section('content')
    <div class="md:px-0 lg:px-[120px] md:mt-[32px] font-cabinet" x-data="shippingPage()" x-init="loadCart();
    loadShippingMethod();">
        <div class="flex justify-center">
            <div class="md:max-w-[1200px] w-full justify-center">
                <h1
                    class="mx-[16px] md:mx-[50px] lg:mx-[16px] pt-[16px] md:pt-0 text-primary-dark-950 font-plush font-normal text-[30px] md:text-[36px]">
                    Shipping Method</h1>
                <div class="flex flex-col lg:flex-row gap-[16px] justify-center my-[16px] lg:my-[24px] w-full">
                    <form action="">
                        <div class=" w-full lg:ml-[16px] lg:mx-0 lg:w-[782px] h-fit flex flex-col">
                            <div class="flex flex-col gap-[16px]">
                                <div x-data="{ activeTab: 'shipping' }">
                                    <div class=" border border-[#E7E7E7] p-[16px] mx-[16px] md:mx-[50px] lg:mx-0">
                                        {{-- <h2 class=" font-cabinet text-[18px] text-primary-dark-950 font-bold mb-[10px]">
                                            How would you like to receive your pieces?</h2>

                                        <!-- Tabs -->
                                        <div class="grid grid-cols-2 gap-[16px] mb-[16px]">
                                            <button type="button" @click="activeTab = 'shipping'" :class="activeTab === 'shipping' 
                                                ? 'bg-primary-light-500 text-white' 
                                                : 'bg-[#FAF9F7] text-primary-dark-950'"
                                                class="flex items-center font-cabinet justify-center gap-2 py-[10px] px-[12px] md:py-[12px] md:px-[16px] rounded-[2px]">
                                                <iconify-icon class="tabs-icon"
                                                    icon="material-symbols-light:delivery-truck-speed-outline"></iconify-icon>
                                                Shipping
                                            </button>

                                            <button type="button" @click="activeTab = 'pickup'" :class="activeTab === 'pickup' 
                                                ? 'bg-primary-light-500 text-white' 
                                                : 'bg-[#FAF9F7] text-primary-dark-950'"
                                                class="flex items-center font-cabinet justify-center gap-2 py-[10px] px-[12px] md:py-[12px] md:px-[16px] rounded-[2px]">
                                                <iconify-icon class="tabs-icon"
                                                    icon="material-symbols-light:storefront-outline"></iconify-icon>
                                                In-Store Pickup
                                            </button>
                                        </div> --}}

                                        <!-- Tab Content -->
                                        <div x-show="activeTab === 'shipping'"
                                            class="w-full mx-auto bg-white overflow-hidden">
                                            <p>Available Shipping Options</p>
                                            <!-- Shipping Options -->
                                            <div class="pt-[8px] flex flex-col gap-[10px]">
                                                <template x-for="(item, index) in shippingMethod"
                                                    :key="item.service + '-' + item.code">
                                                    <div @click="selectedShipping = item"
                                                        :class="selectedShipping.code === item.code && selectedShipping
                                                            .service === item.service ?
                                                            'bg-primary-light-50 border-primary-light-200 border-1' :
                                                            'border border-[#E7E7E7]'"
                                                        class="flex items-center p-4 cursor-pointer rounded-none">
                                                        <div class="flex-1 flex justify-between items-center">
                                                            <div class="flex flex-col">
                                                                <div class="flex items-center gap-[8px]">
                                                                    <span class="text-sm font-medium text-neutral-dark-900"
                                                                        x-text="item.code.toUpperCase()"></span>
                                                                    <span class="text-[#D1D1D1]">|</span>
                                                                    <span class="text-sm font-medium text-neutral-dark-900"
                                                                        x-text="item.service.toUpperCase()"></span>
                                                                    <span class="text-sm font-medium text-[#888]">
                                                                        IDR <span
                                                                            x-text="item.cost.toLocaleString('id-ID')"></span>
                                                                    </span>
                                                                </div>
                                                                <span class="text-xs text-gray-500">
                                                                    <span x-text="item.description"></span> • Arrives in
                                                                    <span x-text="item.etd"></span> days
                                                                </span>
                                                            </div>

                                                            <template
                                                                x-if="selectedShipping.code === item.code && selectedShipping.service === item.service">
                                                                <div class="flex items-center ml-3">
                                                                    <div
                                                                        class="w-5 h-5 rounded-full border-2 border-primary-dark-600 flex items-center justify-center">
                                                                        <div
                                                                            class="w-3 h-3 rounded-full bg-primary-dark-600">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>

                                            </div>

                                        </div>

                                        {{-- <div x-show="activeTab === 'pickup'" class="mt-[16px]">
                                            <p class=" text-primary-dark-950 text-[18px] font-bold">Pickup Location and
                                                Hours</p>
                                            <div class="flex flex-col my-[16px] gap-[16px]">
                                                <div class="flex gap-[8px] items-center">
                                                    <div
                                                        class=" h-[56px] min-w-[56px] rounded-[16px] bg-primary-light-50 flex items-center justify-center">
                                                        <img src="assets/checkout-shipping/location.svg" alt="">
                                                    </div>
                                                    <div class=" flex flex-col">
                                                        <p
                                                            class=" font-medium text-[16px] md:text-[20px] font-plush text-primary-dark-950">
                                                            SECO by Seken Living</p>
                                                        <p class=" text-[14px] md:text-[16px] text-primary-dark-950 ">
                                                            Jl. Ngaglik,
                                                            Karanglo, Sukoharjo, Kec. Ngaglik, YOGYAKARTA, DIY 55581</p>
                                                    </div>
                                                </div>
                                                <div class="flex gap-[8px] items-center">
                                                    <div
                                                        class=" h-[56px] w-[56px] rounded-[16px] bg-primary-light-50 flex items-center justify-center">
                                                        <img src="assets/checkout-shipping/clock.svg" alt="">
                                                    </div>
                                                    <div class=" flex flex-col">
                                                        <p
                                                            class=" font-medium text-[16px] md:text-[20px] font-plush text-primary-dark-950">
                                                            Open Daily</p>
                                                        <p class=" text-[14px] md:text-[16px] text-primary-dark-950 ">
                                                            10.00 am - 9.00
                                                            pm</p>
                                                    </div>
                                                </div>
                                                <button
                                                    class=" border md:w-1/3 border-primary-light-500 py-[10px] px-[12px] rounded-[2px] flex justify-center items-center text-primary-dark-700">
                                                    Get Directions to the store
                                                    <iconify-icon
                                                        style="color: #482B1B; font-size: 20px; margin-left: 4px;"
                                                        icon="material-symbols-light:directions-outline"></iconify-icon>
                                                </button>
                                            </div>
                                        </div> --}}
                                    </div>
                                    {{-- <div x-show="activeTab === 'pickup'"
                                        class="mt-[16px] border border-[#E7E7E7] p-[16px] mx-[16px] md:mx-0">
                                        <h2 class=" font-cabinet text-[18px] text-primary-dark-950 font-bold mb-[10px]">
                                            How Your Pickup Works
                                        </h2>
                                        <div class="grid md:grid-cols-2 gap-[8px]">
                                            <div class=" p-[24px] flex flex-col border borde-[#E7E7E7]">
                                                <div
                                                    class="flex items-center justify-center border border-primary-dark-950 text-primary-dark-950 h-[32px] w-[32px] mb-2 ">
                                                    1
                                                </div>
                                                <p class=" text-primary-dark-950 text-[16px] font-bold">Wait for Our
                                                    "Ready" Email</p>
                                                <p class=" text-primary-dark-950 text-[16px]">We are now preparing your
                                                    pieces with care. You will receive a separate email notification
                                                    once your order is ready for collection.</p>
                                            </div>
                                            <div class=" p-[24px] flex flex-col border borde-[#E7E7E7]">
                                                <div
                                                    class="flex items-center justify-center border border-primary-dark-950 text-primary-dark-950 h-[32px] w-[32px] mb-2 ">
                                                    2
                                                </div>
                                                <p class=" text-primary-dark-950 text-[16px] font-bold">Bring Your
                                                    Confirmation</p>
                                                <p class=" text-primary-dark-950 text-[16px]">When you arrive at our
                                                    studio, simply show our team your 'Ready for Pickup' email. A
                                                    digital copy on your phone is perfect.</p>
                                            </div>
                                            <div class=" p-[24px] flex flex-col border borde-[#E7E7E7]">
                                                <div
                                                    class="flex items-center justify-center border border-primary-dark-950 text-primary-dark-950 h-[32px] w-[32px] mb-2 ">
                                                    3
                                                </div>
                                                <p class=" text-primary-dark-950 text-[16px] font-bold">Meet Our Team
                                                </p>
                                                <p class=" text-primary-dark-950 text-[16px]">Upon arrival, feel free to
                                                    approach any of our team members. They will happily greet you and
                                                    retrieve your order promptly.</p>
                                            </div>
                                            <div class=" p-[24px] flex flex-col border borde-[#E7E7E7]">
                                                <div
                                                    class="flex items-center justify-center border border-primary-dark-950 text-primary-dark-950 h-[32px] w-[32px] mb-2 ">
                                                    4
                                                </div>
                                                <p class=" text-primary-dark-950 text-[16px] font-bold">Pickup Window &
                                                    Support</p>
                                                <p class=" text-primary-dark-950 text-[16px]">We'll happily hold your
                                                    order for 7 days after you receive your 'Ready' email. If you need
                                                    more time or have any questions, kindly contact us.</p>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="flex flex-col gap-[16px] sticky top-[16px] md:px-[50px] lg:px-[0px] h-fit">
                        <div class=" lg:w-[370px] p-[16px] h-fit mx-[16px] md:mx-0 border border-[#E7E7E7] ">
                            <template x-for="(item, index) in visibleItems" :key="item.id">
                                <div class="flex justify-between gap-[12px] mb-[16px]">
                                    <div class="flex gap-[16px]">
                                        <div class=" h-[83px] w-[83px] overflow-hidden">
                                            <img class="w-full h-full object-cover"
                                                :src="item.product.main_pic ? item.product.main_pic :
                                                    '{{ asset('assets/images/no-image.png') }}'"
                                                :alt="item.product.title">
                                        </div>
                                        <div class="font-cabinet">
                                            <p class=" text-neutral-dark-900 text-[16px] font-medium"
                                                x-text="item.product.title"></p>
                                            <div class="flex gap-[2px]">
                                                <p class="text-[#888]">
                                                    Size: <span class="text-neutral-dark-900"
                                                        x-text="item.variant.name"></span>
                                                </p>
                                                <p class="ml-[8px] text-[#888]">
                                                    Color: <span class="text-neutral-dark-900"
                                                        x-text="item.color.label"></span>
                                                </p>
                                            </div>
                                            <p x-show="item.max_discount !== null"
                                                class="text-gray-400 text-[12px] line-through">
                                                IDR <span class="font-bold"
                                                    x-text="(item.price * item.qty).toLocaleString('id-ID')"></span>
                                            </p>
                                            <p class="text-secondary-terra-600 text-[14px]">
                                                IDR <span class="font-bold"
                                                    x-text="(item.final_price * item.qty).toLocaleString('id-ID')"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex justify-end items-end md:w-[42px]">
                                        <p class="text-neutral-dark-900 text-[14px]" x-text="item.qty + 'x'"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="items.length > 2">
                                <button @click="expanded = !expanded"
                                    class="text-primary-dark-950 cursor-pointer w-full px-[12px] py-[10px] rounded-[2px] font-cabinet text-[16px] flex items-center justify-center">
                                    <span x-show="!expanded" x-text="'View All (' + (items.length - 2) + ')'"></span>
                                    <span x-show="expanded">Collapse</span>
                                </button>
                            </template>
                        </div>
                        <div class=" lg:w-[370px] p-[16px] md:border h-fit mx-[16px] md:mx-0 border border-[#E7E7E7]">
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
                                <!-- Default kalau belum ada shipping -->
                                <template x-if="!selectedShipping.cost">
                                    <div class="flex justify-between w-full">
                                        <p class="font-cabinet text-[16px] text-neutral-dark-900">Shipping</p>
                                        <p class="font-cabinet text-[12px] text-neutral-dark-900 text-end">
                                            Shipping costs are calculated during checkout.
                                        </p>
                                    </div>
                                </template>

                                <!-- Kalau shipping sudah dipilih -->
                                <template x-if="selectedShipping.cost">
                                    <div class="flex justify-between w-full">
                                        <p class="font-cabinet text-[16px] text-neutral-dark-900">
                                            <span x-text="selectedShipping.code.toUpperCase()"></span>
                                            <span x-show="selectedShipping.service"> - <span
                                                    x-text="selectedShipping.service"></span></span>
                                            (<span x-text="selectedShipping.etd"></span> hari)
                                        </p>
                                        <p class="font-cabinet text-[16px]  text-end">
                                            IDR <span x-text="selectedShipping.cost.toLocaleString()"></span>
                                            <span x-show="selectedShipping.etd"></span>
                                        </p>
                                    </div>
                                </template>
                            </div>

                            <div class="flex justify-between border-b py-[16px] gap-[16px]">
                                <p class="font-cabinet text-[24px] text-neutral-dark-900 font-bold">Total</p>
                                <p class="font-cabinet text-[24px] text-neutral-dark-900 font-bold text-end">
                                    IDR <span x-text="total.toLocaleString('id-ID')"></span>
                                </p>
                            </div>

                            <button
                                class="bg-primary-dark-950 w-full hover:bg-primary-dark-700 text-white px-[12px] py-[10px] rounded-[2px] font-cabinet text-[16px] flex items-center justify-center"
                                @click="storeSelectedShipment()">
                                Review Your Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('frontend.includes.toast', [
            'type' => 'error',
            'showVariable' => 'showToastError',
            'messageVariable' => 'toastErrorMessage',
        ])
    </div>
@endsection

@section('script')
    <script>
        function shippingPage() {
            return {
                items: [],
                shippingMethod: [],
                showToastError: false,
                toastErrorMessage: '',
                selectedShipping: {
                    name: '',
                    code: '',
                    service: '',
                    description: '',
                    cost: 0,
                    etd: ''
                },
                expanded: false,
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
                    return this.subtotal - this.totalDiscount + (this.selectedShipping.cost || 0);
                },
                get visibleItems() {
                    if (this.expanded) return this.items;
                    return this.items.slice(0, 2);
                },
                async loadCart() {
                    let res = await Alpine.store("api").get('/api/cart');
                    this.items = res.data;
                },
                async loadShippingMethod() {
                    let res = await Alpine.store('api').post('/api/shipping/calculate');
                    this.shippingMethod = res.data;
                },

                async storeSelectedShipment() {
                    try {
                        const payload = {
                            'name': this.selectedShipping.name,
                            'code': this.selectedShipping.code,
                            'service': this.selectedShipping.service,
                            'description': this.selectedShipping.description,
                            'cost': this.selectedShipping.cost,
                            'etd': this.selectedShipping.etd
                        }

                        let res = await Alpine.store('api').post('/api/shipping/store', payload);

                        if (res.status == 200) {
                            window.location.href = res.data.redirect_url;
                        }
                    } catch (err) {
                        this.toastErrorMessage = err.response?.data?.message || err.message || 'Failed to update cart';
                        this.showToastError = true;
                    }
                }
            }
        }
    </script>
@endsection
