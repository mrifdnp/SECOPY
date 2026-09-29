@extends('frontend.layouts.home')

@section('title', 'Address')
@section('meta_robots', 'noindex, nofollow')

@section('content')

<div class="md:px-0 md:px-[120px] md:mt-[32px]">
        <div
            class="flex flex-col items-center justify-center pt-[24px] px-[16px] pb-0 md:pt-[40px] md:px-[120px] md:pb-[16px] w-full">
            <div class="w-full">
                <div class="flex md:gap-3 items-center mb-[16px]">
                    <p class=" font-cabinet text-[16px]">Account</p>
                    <iconify-icon icon="material-symbols-light:chevron-right" class="text-[16px]"></iconify-icon>
                    <p class=" text-[16px]">Address</p>
                </div>
                <div class="flex">
                    <div class="hidden md:flex flex-col w-[364px] mb-[16px] justify-center">
                        <p class=" text-[24px] font-bold ">{{ Auth::guard('customer')->user()->first_name }} {{ Auth::guard('customer')->user()->last_name }}</p>
                        <p>{{ Auth::guard('customer')->user()->email }}</p>
                    </div>
                    <div class="w-full flex items-center mb-[16px] text-primary-dark-950 md:pl-[16px] font-plush">
                        <p class=" text-[30px] font-medium">Address</p>
                    </div>
                </div>
                <div class="flex justify-center md:border md:border-[#F6F6F6] md:w-[800px]">
                    <div class="hidden w-[364px] border-r border-[#F6F6F6] md:flex flex-col items-center p-[16px] ">
                        <nav class="w-full">
                            <ul class="flex flex-col">
                                <li class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:home-storage-outline-rounded"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('customer.account') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">My
                                        Account</a>
                                </li>
                                <li
                                    class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:article-person-outline-rounded"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('customer.profile') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">Profile</a>
                                </li>
                                <li class=" px-[16px] py-[14px] rounded-[2px] bg-primary-light-50 flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:map-pin-review-outline"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('customer.address') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">Address</a>
                                </li>
                                <li class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:lock-open-outline"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('customer.changePassword') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">Password</a>
                                </li>
                                <li class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:receipt-long-outline"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('customer.orderHistory') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">Order
                                        History</a>
                                </li>
                                <li class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:favorite-outline"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('frontend.wishlist') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">Wishlist</a>
                                </li>
                                <li class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:logout-rounded"
                                        class="text-[20px]"></iconify-icon>
                                    <form action="{{route('logout.customer')}}" method="POST">
                                        @csrf
                                    <button type="submit" class="font-plush text-[16px]">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="w-full ">
                        <div class=" md:px-[24px] md:py-[16px]">
                            <form action="{{ route('customer.updateAddress') }}" method="POST">
                                @csrf
                                <div class="mb-[16px]">
                                    <label for="postal_code" class="block text-[14px] font-cabinet  mb-[8px]">Postal Code <span class="text-red-500">*</span></label>
                                    <input type="text" id="postal_code" name="postal_code"
                                        class="w-full border border-primary-light-200 rounded-[2px] px-[12px] py-[8px] text-[14px] font-cabinet  focus:outline-none focus:border-2 focus:border-primary-light-500"
                                        value="{{ old('postcode', $customer->postcode) }}" />
                                </div>
                                <div class="mb-[16px]">
                                    <label for="sub_district" class="block text-[14px] font-cabinet  mb-[8px]"> Sub district <span class="text-red-500">*</span>
                                        </label>
                                    <!-- <input type="text" id="sub_district" name="sub_district"
                                        class="w-full border border-primary-light-200 rounded-[2px] px-[12px] py-[8px] text-[14px] font-cabinet  focus:outline-none focus:border-2 focus:border-primary-light-500"
                                        value="{{ old('sub_district', $customer->sub_district) }}" /> -->
                                        <select name="sub_district" id="sub_district" x-ref="sub_district"
                                            class="w-full border border-primary-light-200 rounded-[2px] px-[12px] py-[8px] text-[14px] font-cabinet  focus:outline-none focus:border-2 focus:border-primary-light-500"
                                            required >
                                                <option value="">Pilih kelurahan</option>
                                        </select>
                                </div>
                                <div class="mb-[16px]">
                                    <label for="district" class="block text-[14px] font-cabinet  mb-[8px]">District <span class="text-red-500">*</span>
                                        </label>
                                    <input type="text" id="district" name="district"
                                        class="w-full border border-primary-light-200 rounded-[2px] px-[12px] py-[8px] text-[14px] font-cabinet  focus:outline-none focus:border-2 focus:border-primary-light-500"
                                        value="{{ old('district', $customer->district) }}" readonly/>

                                        <!-- <select name="district" id="district" x-ref="district"
                                            class="w-full border border-primary-light-200 rounded-[2px] px-[12px] py-[8px] text-[14px] font-cabinet  focus:outline-none focus:border-2 focus:border-primary-light-500"
                                            required >
                                                <option value="">Pilih kecamatan</option>
                                        </select> -->
                                </div>
                                <div class="flex flex-col mb-[16px]">
                                    <label for="city" class=" text-[14px] font-cabinet text-neutral-dark-900 mb-1">
                                        City / Regency <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="city" name="city" value="Sleman" required
                                        class="border border-primary-light-200 p-4 w-full h-[20px] font-cabinet text-[16px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" 
                                        value="{{ old('city', $customer->city) }}" readonly/>
                                        <!-- <select name="city_regency" id="city_regency" x-ref="city_regency"
                                            class="w-full border border-primary-light-200 rounded-[2px] px-[12px] py-[8px] text-[14px] font-cabinet  focus:outline-none focus:border-2 focus:border-primary-light-500"
                                            required >
                                                <option value="">Pilih Kabupaten</option>
                                        </select> -->
                                </div>
                                <div class="mb-[16px]">
                                    <label for="province" class="block text-[14px] font-cabinet  mb-[8px]">Province <span class="text-red-500">*</span>
                                        </label>
                                    <input type="text" id="province" name="province"
                                        class="w-full border border-primary-light-200 rounded-[2px] px-[12px] py-[8px] text-[14px] font-cabinet  focus:outline-none focus:border-2 focus:border-primary-light-500"
                                        value="{{ old('province', $customer->province) }}" readonly/>

                                        <!-- <select name="province" id="province" x-ref="province"
                                            class="w-full border border-primary-light-200 rounded-[2px] px-[12px] py-[8px] text-[14px] font-cabinet  focus:outline-none focus:border-2 focus:border-primary-light-500"
                                            required >
                                                <option value="">Pilih provinsi</option>
                                        </select> -->
                                </div>
                                <div class="mb-[16px]">
                                    <label for="full_location" class="block text-[14px] font-cabinet  mb-[8px]">Full Location / Area <span class="ml-[4px] text-[#9C9896]">(optional)</span>
                                        </label>
                                    <textarea id="full_location" name="full_location"
                                        class="w-full h-[100px] border border-primary-light-200 rounded-[2px] px-[12px] py-[8px] text-[14px] font-cabinet focus:outline-none focus:border-2 focus:border-primary-light-500"
                                        value="{{ old('address', $customer->address) }}">{{ $customer->address }}</textarea>
                                </div>
                            
                                <div class="flex justify-end gap-[8px]">
                                    <button type="reset"
                                        class="mt-[16px] ml-[8px] px-[16px] py-[8px] bg-primary-light-50 text-primary-dark-950 rounded-[2px] hover:bg-primary-light-100">
                                        Cancel
                                    </button>
                                    <button
                                        class="mt-[16px] flex items-center gap-[8px] px-[16px] py-[8px] bg-primary-dark-950 text-white rounded-[2px] hover:bg-primary-dark-700 hover:text-white">
                                        <iconify-icon icon="material-symbols:save-outline"
                                            class="text-[16px]"></iconify-icon>
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

@endsection

@section('css')

<link href="https://fonts.cdnfonts.com/css/cabinet-grotesk" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/css/intlTelInput.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

@endsection

@section('script')

<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/intlTelInput.min.js"></script>
<script>
    flatpickr("#datepicker-wrapper", {
        wrap: true,
        dateFormat: "Y-m-d",
    });

const postalCodeInput = document.querySelector("#postal_code");
const subDistrictSelect = document.querySelector("#sub_district"); // pastikan ini
const subDistrictInput = document.querySelector("#sub_district");
const districtInput = document.querySelector("#district");
const cityInput = document.querySelector("#city_regency");
const provinceInput = document.querySelector("#province")
const fullLocationInput = document.querySelector("#full_location");

const oldSubDistrict = "{{ old('sub_district', $customer->sub_district) }}";
const oldPostalCode = "{{ old('postcode', $customer->postcode) }}";

postalCodeInput.value = oldPostalCode;

let addressData = [];

const renderAddress = (data) => {
    if (!data || data.length === 0) return;

    // Reset sub-district
    subDistrictSelect.innerHTML = '<option value="">Pilih kelurahan</option>';

    // Tambahkan option sub-district
    data.forEach(d => {
        const option = document.createElement("option");
        option.value = d.subdistrict_name;
        option.textContent = d.subdistrict_name;
        if(d.subdistrict_name === oldSubDistrict) option.selected = true;
        subDistrictSelect.appendChild(option);
    });

    // Set district/city/province (readonly)
    districtInput.value = data[0].district_name;
    cityInput.value = data[0].city_name;
    provinceInput.value = data[0].province_name;

    // Set full location
    const filteredOld = data.filter(d => d.subdistrict_name === oldSubDistrict);
    fullLocationInput.value = filteredOld.length ? filteredOld.map(d => d.label).join(", ") : "";

    // Update full location saat sub-district berubah
    subDistrictSelect.onchange = () => {
        const selected = subDistrictSelect.value;
        const filtered = data.filter(d => d.subdistrict_name === selected);
        fullLocationInput.value = filtered.map(d => d.label).join(", ");
    };
};

// Fungsi ambil data alamat berdasarkan postcode
const fetchAddress = async (zip) => {
    if (!zip) return;
    try {
        const res = await fetch(`/search-destination?search=${zip}`);
        const result = await res.json();
         addressData = result.data || []; 
        // const data = result.data || [];
        renderAddress(addressData);
    } catch (err) {
        console.error(err);
    }
};

let debounceTimeout;
postalCodeInput.addEventListener("input", () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        fetchAddress(postalCodeInput.value)
    }, 300);
    })

subDistrictSelect.addEventListener("change", () => {
    const selectedSubdistrict = subDistrictSelect.value;
    const filtered = addressData.filter(d => d.subdistrict_name === selectedSubdistrict);
    fullLocationInput.value = filtered.map(d => d.label).join(", ");
});

// Load data awal saat halaman dibuka (edit)
if(oldPostalCode){
    fetchAddress(oldPostalCode);
}

    const input = document.querySelector("#phone");
    window.intlTelInput(input, {
        separateDialCode: true,
        initialCountry: "id",
        loadUtils: () => import("https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/utils.js"),
    });
</script>

@endsection