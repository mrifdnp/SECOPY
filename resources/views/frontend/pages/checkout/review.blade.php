@extends('frontend.layouts.home')

@section('title', 'Checkout Review')
@section('meta_robots', 'noindex, nofollow')

@section('content')
    <div class="lg:px-0 lg:px-[120px] lg:mt-[32px] font-cabinet" x-data="checkoutReviewPage()" 
    x-init="loadCart(); loadCustomer(); loadSelectedShipmentMethod();"
    >
        <div
            x-show="loading"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
            x-transition.opacity>
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12"></div>
        </div>
         <div class="flex justify-center">
            <div class="md:max-w-[1200px] w-full justify-center">
                <h1
                    class="mx-[16px] pt-[16px] md:mx-[50px] lg:mx-[16px] lg:pt-0 text-primary-dark-950 font-plush font-normal text-[30px] lg:text-[36px]">
                    Shipping Review</h1>
                <div class="flex flex-col lg:flex-row gap-[16px] justify-center lg:items-start md:items-center my-[16px] lg:my-[24px] w-full">
                    <div class=" w-full lg:ml-[16px] md:mx-0 lg:w-[782px] h-fit flex flex-col">
                        <div class="flex flex-col gap-[16px]">
                            <div class=" border border-[#E7E7E7] p-[16px] mx-[16px] md:mx-[50px] lg:mx-[0px] md:mx-0 flex flex-col gap-[16px]">
                                <div class="flex justify-between">
                                    <div>
                                        <p class=" font-cabinet text-[14px] md:text-[16px] text-primary-dark-950 font-bold">
                                            Contact
                                        </p>
                                        <div class="flex gap-[6px] md:gap-[12px] text-primary-dark-950 text-[14px] md:text-[16px]">
                                            <p x-text="customer.phone"></p>
                                            <p>&bull;</p>
                                            <p x-text="customer.email"></p>
                                        </div>
                                    </div>
                                    <div class=" w-[40px] h-[37px] py-[10px] px-[12px] bg-primary-light-50 rounded-[2px] cursor-pointer"
                                        @click="editModal = true">
                                        <iconify-icon icon="material-symbols-light:edit-square-outline"
                                            class="w-[24px] h-[24px] text-primary-dark-950 w-full h-full"></iconify-icon>
                                    </div>
                                    
                                    <!-- Modal Edit Confirmation -->
                                    <template x-if="editModal">
                                        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 ">
                                            <div class="bg-white p-6 rounded-[4px] w-[90%] max-w-md shadow-lg text-primary-dark-950 font-cabinet border border-primary-light-100">
                                                <h2 class=" text-[16px] md:text-[18px] text-center font-bold mb-4">Edit Your Information?</h2>
                                                <p class=" text-[12px] md:text-[14px] text-gray-700 mb-6 text-center">To change your details, we'll guide you back to the previous step. You may need to re-confirm your shipping choice after. You may need to re-confirm</p>
                                                <div class="flex justify-center gap-2">
                                                    <button @click="editModal = false"
                                                        class="px-4 py-2 text-sm rounded bg-primary-light-50 hover:bg-primary-light-100">Cancel</button>
                                                    <button @click="handleEdit()"
                                                        class="px-4 py-2 text-sm rounded bg-primary-dark-950 text-white hover:bg-primary-dark-700">Yes, Edit Details</button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div>
                                    <p class=" font-cabinet text-[14px] md:ext-[16px] text-primary-dark-950 font-bold">
                                        Ship to
                                    </p>
                                    <p class=" text-primary-dark-950 text-[14px] md:text-[16px]"   x-text="`${customer.address}, ${customer.sub_district}, ${customer.district}, ${customer.city}, ${customer.province} ${customer.postcode}`">
                                    </p>
                                </div>
                                <div
                                    class="flex items-center p-4 cursor-pointer border border-[#DCCEAC] rounded-none bg-primary-light-50">
                                    <div class="flex-1 flex justify-between items-center">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-[8px]">
                                                <span class="text-sm font-medium text-neutral-dark-900" x-text="selectedShipmentMethod.code.toUpperCase()">
                                                </span>
                                                <span class=" text-[#D1D1D1]">
                                                    I
                                                </span>
                                                <span class="text-sm font-medium text-neutral-dark-900" x-text="selectedShipmentMethod.service.toUpperCase()">
                                                </span>
                                                <span class="text-sm font-medium text-[#888]">
                                                    IDR <span x-text="selectedShipmentMethod.cost.toLocaleString('id-ID')"></span>
                                                </span>
                                            </div>
                                            <span class="text-xs text-gray-500"><span x-text="item.description"></span>
                                                Arrives in <span x-text="selectedShipmentMethod.etd"></span> days</span>
                                        </div>
                                        <div class="text-right">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class=" p-[16px] h-fit mx-[16px] md:mx-[50px] lg:mx-0 border border-[#E7E7E7]">
                                <template x-for="(item, index) in items" :key="item.id">
                                    <div class="flex justify-between gap-[12px] mb-[16px]">
                                        <div class="flex gap-[12px]">
                                            <div class=" h-[83px] w-[83px]">
                                                <img
                                                    class="w-full h-full object-cover" 
                                                    :src="item.product.main_pic ? item.product.main_pic : '{{ asset('assets/images/no-image.png') }}'" 
                                                    :alt="item.product.title"
                                                >
                                            </div>
                                            <div class="font-cabinet">
                                                <p class=" text-neutral-dark-900 text-[16px] font-medium" x-text="item.product.title"></p>
                                                <div class="flex gap-[2px]">
                                                    <p class=" text-[#888]">Size: <span
                                                            class="text-neutral-dark-900" x-text="item.variant.name"></span>
                                                    </p>
                                                    <p class="ml-[8px] text-[#888]">Color: <span
                                                            class="text-neutral-dark-900" x-text="item.color.label"></span></p>
                                                </div>
                                                <p x-show="item.max_discount !== null"  class="mt-[4px] text-neutral-dark-500 text-[12px] line-through">IDR <span class="font-bold"
                                                    x-text="(item.price * item.qty).toLocaleString('id-ID')"></span></p>
                                                <p class="text-secondary-terra-600 text-[14px]">IDR <span
                                                        class="font-bold" x-text="(item.final_price * item.qty).toLocaleString('id-ID')"></span></p>
                                            </div>
                                        </div>
                                        <div class="flex justify-end items-end md:w-[42px]">
                                            <p class="text-neutral-dark-900 text-[14px]" x-text="item.qty + 'x'"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-[16px] w-full lg:w-[370px]">
                        <div class=" lg:w-[370px] p-[16px] lg:border h-fit mx-[16px] md:mx-[50px] lg:mx-0 border border-[#E7E7E7] sticky top-[16px]">
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
                                <template x-if="!selectedShipmentMethod.cost">
                                    <div class="flex justify-between w-full">
                                        <p class="font-cabinet text-[16px] text-neutral-dark-900">Shipping</p>
                                        <p class="font-cabinet text-[12px] text-neutral-dark-900 text-end">
                                            Shipping costs are calculated during checkout.
                                        </p>
                                    </div>
                                </template>

                                <!-- Kalau shipping sudah dipilih -->
                                <template x-if="selectedShipmentMethod.cost">
                                    <div class="flex justify-between w-full">
                                        <p class="font-cabinet text-[16px] text-neutral-dark-900">
                                            <span x-text="selectedShipmentMethod.code.toUpperCase()"></span>
                                            <span x-show="selectedShipmentMethod.service"> - <span
                                                    x-text="selectedShipmentMethod.service"></span></span>
                                            (<span x-text="selectedShipmentMethod.etd"></span> hari)
                                        </p>
                                        <p class="font-cabinet text-[16px]  text-end">
                                            IDR <span x-text="selectedShipmentMethod.cost.toLocaleString()"></span>
                                            <span x-show="selectedShipmentMethod.etd"></span>
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
                                @click="createInvoice();"
                                class="bg-primary-dark-950 w-full hover:bg-primary-dark-700 text-white px-[12px] py-[10px] rounded-[2px] font-cabinet text-[16px] flex items-center justify-center"
                                >
                                Continue to Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    function checkoutReviewPage() {
        return {
            items: [],
            editModal: false,
            customer: {},
            loading: false,
            selectedShipmentMethod: {},
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
                return this.subtotal - this.totalDiscount +  (this.selectedShipmentMethod.cost || 0);
            },
           
            async loadCart() {
                let res = await Alpine.store("api").get('/api/cart');
                this.items = res.data;
            },
            async loadCustomer() {
                let res = await Alpine.store("api").get('/api/customer');
                this.customer = res.data;
            },
            async loadSelectedShipmentMethod() {
                let res = await Alpine.store("api").get('/api/selected-shipment-method');
                this.selectedShipmentMethod = res.data;
            },
            async createInvoice() {
                this.loading = true;
                try {
                    let res = await Alpine.store("api").post('/api/invoice/create');

                    if(res.status === 200) {
                        window.location.href = res.data.invoice.invoice_url;
                    }
                } catch (e) {
                    console.error(e);
                    alert('Failed to create invoice. Please try again.');
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endsection

@section('css')
<style>
.loader {
    border-top-color: #3498db;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection