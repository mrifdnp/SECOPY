@extends('frontend.layouts.home')

@section('title', 'Shopping Contact')
@section('meta_robots', 'noindex, nofollow')

@section('content')
    <div class="md:px-0 md:px-[50px] lg:px-[120px] md:mt-[32px]" x-data="checkoutPage()" x-init="loadCart();
    loadCustomer();
    searchPostalCode();">
        <div class="flex justify-center">
            <div class="md:max-w-[1200px] w-full justify-center">
                <h1
                    class="mx-[16px] pt-[16px] md:pt-0 text-primary-dark-950 font-plush font-normal text-[30px] md:text-[36px]">
                    Your Contact & Address</h1>
                <div class="flex flex-col lg:flex-row gap-[16px] justify-center my-[16px] lg:my-[24px] w-full">
                    <form action="">
                        <div class=" w-full lg:ml-[16px] lg:mx-0 lg:w-[782px] h-fit flex flex-col">
                            <div class="flex flex-col gap-[16px]">
                                <div class=" border border-[#E7E7E7] p-[16px] mx-[16px] md:mx-0">
                                    <h2 class=" font-cabinet text-[18px] text-primary-dark-950 mb-[16px]">Contact</h2>
                                    <div class="grid md:grid-cols-2 gap-[16px]">
                                        <div>
                                            <label for="first_name"
                                                class=" font-cabinet text-[14px] text-neutral-dark-900">Your First Name
                                                <span class="text-red-500">*</span></label>
                                            <input type="text" id="first_name" name="first_name" required
                                                x-model="customer.first_name"
                                                class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-2 focus:border-primary-light-500 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label for="last_name"
                                                class=" font-cabinet text-[14px] text-neutral-dark-900">Your Last Name
                                                <span class="text-red-500">*</span></label>
                                            <input type="text" id="last_name" name="last_name" required
                                                x-model="customer.last_name"
                                                class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                        </div>
                                        <div class="flex flex-col">
                                            <label for="phone"
                                                class=" text-[14px] font-cabinet text-neutral-dark-900 mb-1">
                                                Your Phone Number <span class="text-red-500">*</span>
                                            </label>
                                            <input type="tel" id="phone" name="phone" required
                                                x-model="customer.phone"
                                                class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label for="email"
                                                class=" font-cabinet text-[14px] text-neutral-dark-900">Email <span
                                                    class="text-red-500">*</span></label>
                                            <input type="text" id="last_name" name="last_name" required
                                                x-model="customer.email"
                                                class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                        </div>
                                    </div>
                                </div>
                                <div class=" border border-[#E7E7E7] p-[16px] mx-[16px] md:mx-0">
                                    <h2 class=" font-cabinet text-[18px] text-primary-dark-950 mb-[16px]">Address</h2>
                                    <div class="grid grid-cols-1 gap-[16px]">
                                        <div>
                                            <label for="postal_code"
                                                class="font-cabinet text-[14px] text-neutral-dark-900">Postal Code
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="postal_code" name="postal_code" required
                                                x-model="customer.postcode" @input="debouncedSearchPostalCode"
                                                class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                        </div>

                                        <div class="grid grid-cols-2 gap-[16px]">
                                            <div>
                                                <label for="sub_district"
                                                    class="font-cabinet text-[14px] text-neutral-dark-900">Sub District
                                                    <span class="text-red-500">*</span>
                                                </label>
                                                <select id="sub_district" name="sub_district" required
                                                    x-model="customer.sub_district" @change="updateLocationFields"
                                                    class="border border-[#E7E7E7] p-2 w-full  font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none bg-white">
                                                    <option value="" disabled>Select Sub District</option>
                                                    <template x-for="location in postalResults" :key="location.id">
                                                        <option :value="location.subdistrict_name"
                                                            x-text="location.subdistrict_name">
                                                        </option>
                                                    </template>
                                                </select>

                                            </div>

                                            <div>
                                                <label for="district"
                                                    class="font-cabinet text-[14px] text-neutral-dark-900">District
                                                    <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" id="district" name="district" required
                                                    x-model="customer.district"
                                                    class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                            </div>

                                            <div>
                                                <label for="city_regency"
                                                    class="font-cabinet text-[14px] text-neutral-dark-900">City / Regency
                                                    <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" id="city_regency" name="city_regency" required
                                                    x-model="customer.city"
                                                    class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                            </div>

                                            <div>
                                                <label for="province"
                                                    class="font-cabinet text-[14px] text-neutral-dark-900">Province
                                                    <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" id="province" name="province" required
                                                    x-model="customer.province"
                                                    class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                            </div>
                                        </div>

                                        <div>
                                            <label for="address"
                                                class="font-cabinet text-[14px] text-neutral-dark-900">Full Location / Area
                                                (optional)</label>
                                            <textarea name="address" id="address" x-model="customer.address"
                                                class="border border-[#E7E7E7] p-4 w-full h-[100px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none"
                                                placeholder="Input Address"></textarea>
                                        </div>

                                        {{-- <div>
                                            <label for="notes"
                                                class="font-cabinet text-[14px] text-neutral-dark-900">Notes
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="notes" name="notes" required
                                                class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                        </div> --}}

                                        <div class="flex gap-[8px] items-center">
                                            <input type="checkbox" id="save_address" name="save_address"
                                                x-model="saveProvile"
                                                class="w-[20px] h-[20px] rounded-none border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                            <label for="save_address"
                                                class="font-cabinet text-[14px] text-neutral-dark-900">Save this address
                                                for future purchases</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="flex flex-col gap-[16px]">
                        <div class=" lg:w-[370px] p-[16px] mx-[16px] md:mx-0 border border-[#E7E7E7] h-fit ">
                            <template x-for="(item, index) in visibleItems" :key="item.id">
                                <div class="flex justify-between gap-[0px] lg:gap-[12px] mb-[16px]">
                                    <div class="flex gap-[12px]">
                                        <div class="h-[83px] w-[83px] overflow-hidden">
                                            <img class="w-full h-full object-cover"
                                                :src="item.product.main_pic ? item.product.main_pic :
                                                    '{{ asset('assets/images/no-image.png') }}'"
                                                :alt="item.product.title">
                                        </div>
                                        <div class="font-cabinet">
                                            <p class="text-neutral-dark-900 text-[16px]" x-text="item.product.title"></p>
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
                        <div class=" lg:w-[370px] p-[16px] mx-[16px] md:mx-0 border border-[#E7E7E7] h-fit">
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
                                <p class=" font-cabinet text-[16px] text-neutral-dark-900">Shipping</p>
                                <p class=" font-cabinet text-[12px] text-neutral-dark-900 text-end">Shipping costs are
                                    calculated
                                    during checkout.</p>
                            </div>
                            <div class="flex justify-between border-b py-[16px] gap-[16px]">
                                <p class="font-cabinet text-[24px] text-neutral-dark-900 font-bold">Total</p>
                                <p class="font-cabinet text-[24px] text-neutral-dark-900 font-bold text-end">
                                    IDR <span x-text="total.toLocaleString('id-ID')"></span>
                                </p>
                            </div>
                            <button
                                class="bg-primary-dark-950 w-full hover:bg-primary-dark-700 text-white px-[12px] py-[10px] rounded-[2px] font-cabinet text-[16px] flex items-center justify-center"
                                @click="updateCustomerProfile()">
                                Continue to Shipping
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
        function checkoutPage() {
            return {
                items: [],
                saveProvile: false,
                showToastError: false,
                toastErrorMessage: '',
                customer: {
                    postcode: '',
                    sub_district: '',
                    district: '',
                    city: '',
                    province: '',
                    address: '',
                    districtId: ''
                },
                postalResults: [],
                expanded: false,
                timeoutId: null,

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
                    return this.subtotal - this.totalDiscount;
                },

                get visibleItems() {
                    if (this.expanded) return this.items;
                    return this.items.slice(0, 2);
                },

                async loadCart() {
                    let res = await Alpine.store("api").get('/api/cart');
                    this.items = res.data;
                },

                async loadCustomer() {
                    let res = await Alpine.store("api").get('/api/customer');
                    this.customer = res.data;

                    if (this.customer.postcode && this.customer.postcode.length >= 3) {
                        await this.searchPostalCode();
                    }
                },

                debouncedSearchPostalCode(e) {
                    clearTimeout(this.timeoutId);
                    this.timeoutId = setTimeout(() => {
                        this.searchPostalCode();
                    }, 500);
                },

                async searchPostalCode() {
                    if (!this.customer.postcode || this.customer.postcode.length < 3) {
                        this.postalResults = [];
                        this.resetLocationFields();
                        return;
                    }

                    let res = await Alpine.store("api").get('/search-destination', {
                        params: {
                            search: this.customer.postcode
                        }
                    });

                    this.postalResults = res.data;

                    if (this.postalResults.length > 0) {
                        this.customer.sub_district = this.postalResults[0].subdistrict_name;

                        setTimeout(() => {
                            this.updateLocationFields();
                        }, 10);
                    } else {
                        this.resetLocationFields();
                    }
                },

                updateLocationFields() {
                    const selectedLocation = this.postalResults.find(
                        location => location.subdistrict_name === this.customer.sub_district
                    );

                    if (selectedLocation) {
                        this.customer.district = selectedLocation.district_name;
                        this.customer.city = selectedLocation.city_name;
                        this.customer.province = selectedLocation.province_name;
                        this.customer.districtId = selectedLocation.id;
                    }
                },

                resetLocationFields() {
                    this.customer.sub_district = '';
                    this.customer.district = '';
                    this.customer.city = '';
                    this.customer.province = '';
                    this.customer.districtId = '';
                },

                async updateCustomerProfile() {
                    try {
                        const updateCustomerData = {
                            'first_name': this.customer.first_name,
                            'last_name': this.customer.last_name,
                            'phone': this.customer.phone,
                            'email': this.customer.email,
                            'postcode': this.customer.postcode,
                            'sub_district': this.customer.sub_district,
                            'district': this.customer.district,
                            'city': this.customer.city,
                            'province': this.customer.province,
                            'address': this.customer.address,
                            'save': this.saveProvile,
                            'districtId': this.customer.districtId
                        }

                        let res = await Alpine.store("api").put('/api/customer', updateCustomerData);

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
