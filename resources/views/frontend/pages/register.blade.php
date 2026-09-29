@extends('frontend.layouts.home')

@section('title', 'Register')
@section('meta_robots', 'noindex, nofollow')

@section('content')

<div>
<div
        class="md:px-0 px-[24px] md:px-[120px] md:mt-[90px] mt-[50px] flex flex-col items-center justify-center font-cabinet text-primary-dark-950">
        <div x-data="{ 
            step: 1, 
            showPassword: false,
        }">
            <div class="flex justify-between items-center">
                <h1 class="font-plush text-[30px] md:text-[36px]"
                    x-text="step === 1 ? 'Create an Account' : step === 2 ? 'A Little About You' : 'Your Home Address'">
                </h1>

                <div class="flex gap-[6px] mx-[6px]">
                    <template x-for="i in 3" :key="i">
                        <div :class="step === i ? 'bg-primary-dark-950' : 'bg-primary-light-200'"
                            class="w-2 h-2 rounded-full transition-all duration-300">
                        </div>
                    </template>
                </div>
            </div>
            <p class=" text-[16px]" x-text="
            step === 1
                ? 'Start with an email and a secure password to keep it safe.'
                : step === 2
                ? 'So we can greet you by name and ensure a smooth delivery.'
                : 'Begin with your postal code to automatically find your area.'
            "></p>
            
            @if ($errors->any())
            <div class="mb-4 py-3 text-red-500 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="mt-[24px]">
                <form x-ref="form" action="{{ route('register.customer') }}" method="POST">
                    @csrf
                    <div class="w-[342px] md:w-[426px]">
                        <div x-show="step === 1" x-transition:enter.duration.300ms x-transition:leave.duration.300ms>
                            <div class=" w-full">
                                <label for="email" class=" text-[14px]">Your Email<span
                                        class="text-red-500">*</span></label>
                                <input x-ref="email" type="email" id="email" name="email" placeholder="you@example.com"
                                    class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                    required />
                            </div>
                            <div class=" w-full relative">
                                <label for="password" class=" text-[14px]">Create a Password<span
                                        class="text-red-500">*</span></label>

                                <input x-ref="password" :type="showPassword ? 'text' : 'password'" id="password"
                                    name="password" placeholder="Something secure and memorable"
                                    class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                    required />

                                <div class="absolute right-4 top-[30px] cursor-pointer text-gray-600"
                                    @click="showPassword = !showPassword">
                                    <template x-if="showPassword">
                                        <iconify-icon icon="material-symbols-light:visibility-outline"
                                            class="text-[23px]"></iconify-icon>
                                    </template>
                                    <template x-if="!showPassword">
                                        <iconify-icon icon="material-symbols-light:visibility-off-outline-rounded"
                                            class="text-[23px]"></iconify-icon>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div x-show="step === 2" x-transition:enter.duration.300ms x-transition:leave.duration.300ms>
                            <div class="flex flex-col w-full">
                                <div class="flex gap-4">
                                    <div class="w-full flex flex-col">
                                        <label for="first_name"
                                            class=" text-[14px] font-cabinet text-neutral-dark-900">First Name<span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="first_name" name="first_name" x-ref="first_name"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required />
                                    </div>
                                    <div class="w-full flex flex-col">
                                        <label for="last_name"
                                            class=" text-[14px] font-cabinet text-neutral-dark-900">Last Name<span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="last_name" name="last_name" x-ref="last_name"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required />

                                    </div>
                                </div>
                                <div class="w-full mb-[8px] flex flex-col">
                                    <label for="phone" class=" text-[14px] font-cabinet text-neutral-dark-900">
                                        Your Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="hidden" id="phone_full" name="phone_full">
                                    <input type="hidden" name="country_code">
                                    <!-- <input type="hidden" name="full_phone" x-ref="full_phone"> -->
                                    <input type="tel" id="phone" name="phone" x-ref="phone" required
                                        placeholder="For delivery coordination"
                                        class="border border-[#E7E7E7] p-4 w-full h-[20px] font-cabinet text-[14px] border border-primary-light-200 focus:border-primary-light-500 focus:outline-none" />
                                </div>
                                <div class="relative mt-[8px]" id="datepicker-wrapper">
                                    <label for="date_of_birth"
                                        class="text-[14px] font-cabinet text-neutral-dark-900">Your Birthday <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="date_of_birth" x-ref="date_of_birth"
                                        placeholder="So we can send a litte gift"
                                        class="w-full border border-primary-light-200 rounded-[2px] font-cabinet px-4 py-2 text-sm focus:outline-none focus:border-1 focus:border-primary-light-500"
                                        data-input required/>

                                    <iconify-icon icon="material-symbols:calendar-today-outline"
                                        class="absolute flex justify-center items-center bg-primary-light-100 right-0 h-[38px] w-[40px] top-[21px] transform translate-y-[3px] cursor-pointer text-gray-600"
                                        data-toggle>
                                    </iconify-icon>
                                </div>
                            </div>
                        </div>
                        <div x-show="step === 3" x-transition:enter.duration.300ms x-transition:leave.duration.300ms>
                            <div class="flex flex-col w-full">
                                <div class=" w-full">
                                    <label for="postal_code" class=" text-[14px]">Postal Code<span
                                            class="text-red-500">*</span></label>
                                    <input x-ref="postal_code" type="text" id="postal_code" name="postal_code"
                                        placeholder="Start here to find your area"
                                        class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                        required />
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-full flex flex-col mb-[16px]">
                                        <label for="sub_district"
                                            class=" text-[14px] font-cabinet text-neutral-dark-900">Sub-district<span
                                                class="text-red-500">*</span></label>
                                        <!-- <input type="text" id="sub_district" name="sub_district" x-ref="sub_district"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required /> -->
                                            <select name="sub_district" id="sub_district" x-ref="sub_district"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required >
                                                <option value="">Pilih kelurahan</option>
                                            </select>
                                        <p class="text-[12px] text-neutral-dark-400">Filled from your search</p>
                                    </div>
                                    <div class="w-full flex flex-col mb-[16px]">
                                        <label for="district"
                                            class=" text-[14px] font-cabinet text-neutral-dark-900">District<span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="district" name="district" x-ref="district"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required readonly/>

                                            <!-- <select name="district" id="district" x-ref="district"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required >
                                                <option value="">Pilih kecamatan</option>
                                            </select> -->
                                        <p class="text-[12px] text-neutral-dark-400">Filled from your search</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-full flex flex-col mb-[16px]">
                                        <label for="city_regency"
                                            class=" text-[14px] font-cabinet text-neutral-dark-900">City / Regency<span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="city_regency" name="city_regency" x-ref="city_regency"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required readonly/>

                                            <!-- <select name="city_regency" id="city_regency" x-ref="city_regency"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required >
                                                <option value="">Pilih Kabupaten</option>
                                            </select> -->
                                        <p class="text-[12px] text-neutral-dark-400">Filled from your search</p>
                                    </div>
                                    <div class="w-full flex flex-col mb-[16px]">
                                        <label for="province"
                                            class=" text-[14px] font-cabinet text-neutral-dark-900">Province<span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="province" name="province" x-ref="province"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required readonly/>
                                            <!-- <select name="province" id="province" x-ref="province"
                                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[6px] px-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                            required >
                                                <option value="">Pilih provinsi</option>
                                            </select> -->
                                        <p class="text-[12px] text-neutral-dark-400">Filled from your search</p>
                                    </div>
                                </div>
                                <div class="flex flex-col w-full">
                                    <div class=" w-full">
                                        <label for="full_location" class=" text-[14px]">Full Location / Area
                                            (optional)</label>
                                        <textarea x-ref="full_location" id="full_location" name="full_location"
                                            placeholder="Street name, house number, and details"
                                            class="w-full h-[80px] border border-primary-light-200 rounded-[2px] text-[14px] font-plush py-[6px] px-[16px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button"
                                class="w-full mt-[16px] text-primary-dark-700 font-plush text-[16px] py-[8px] px-[16px] rounded-[2px] border-[1px] border-primary-light-500 hover:bg-primary-light-50 transition-colors"
                                @click="
                                    if (step === 1) {
                                    window.location.href = 'login.html'
                                    } else {
                                    step = step - 1
                                    }
                                ">
                                Back
                            </button>
                            <!-- Button -->
                            <button type="button"
                                class="w-full mt-[16px] bg-primary-dark-950 text-white font-plush text-[16px] py-[8px] px-[16px] rounded-[2px] hover:bg-primary-dark-700 transition-colors"
                                x-text="step === 1 ? 'Next' : (step === 2 ? 'Next' : 'Create Account')"
                                @click="validateAndProceed(step, $refs, val => step = val, () => $refs.form?.submit())"></button>

                        </div>
                </form>
                <div class="flex items-center my-6">
                    <hr class="flex-grow border-t border-gray-300" />
                    <span class="px-4 text-gray-400 text-sm">Have we met before?</span>
                    <hr class="flex-grow border-t border-gray-300" />
                </div>
                <button type="button" onclick="window.location.href='{{route('loginForm.customer') }}'"
                    class="w-full bg-primary-light-50 font-plush text-[16px] py-[8px] px-[16px] rounded-[2px] hover:bg-primary-light-200 transition-colors">
                    Sign In
                </button>
                <a href="{{ route('customer.google.redirect') }}" 
                    class="w-full mt-[16px] bg-white border border-gray-300 text-gray-700 font-cabinet text-[16px] py-[8px] px-[16px] rounded-[2px] hover:bg-gray-50 transition-colors flex items-center justify-center gap-3">
                        <iconify-icon icon="logos:google-icon" class="text-[18px]"></iconify-icon>
                        Login or Register with Google
                    </a>
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
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script src="https://cdn.tailwindcss.com"></script>

<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#datepicker-wrapper", {
        wrap: true,
        dateFormat: "Y-m-d",
    });

    document.addEventListener("DOMContentLoaded", function() {
    const input = document.querySelector("#phone");
    const phoneFull = document.querySelector("#phone_full");
    const countryCodeInput = document.querySelector("input[name='country_code']");
    const form = document.querySelector("form[x-ref='form']");

    const iti = window.intlTelInput(input, {
        separateDialCode: true,
        initialCountry: "id",
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/utils.js"
    });

    // Set nilai awal saat load
    const setPhoneValues = () => {
    const countryData = iti.getSelectedCountryData();

    // ambil raw angka user
    let raw = input.value.replace(/\D/g, ''); // cuma angka

    phoneFull.value = `${countryData.dialCode}${raw}`;
    countryCodeInput.value = countryData.dialCode;

    console.log("input.value:", input.value);
    console.log("phoneFull (final):", phoneFull.value);
    console.log("countryData:", countryData);
};

    input.addEventListener("input", setPhoneValues);
    input.addEventListener("countrychange", setPhoneValues);

    // Update sebelum submit form
    form.addEventListener("submit", function() {
        setPhoneValues();
    });
    window.updatePhoneBeforeSubmit = setPhoneValues;
});

const postalCodeInput = document.querySelector("#postal_code");
const subDistrictInput = document.querySelector("#sub_district");
const districtInput = document.querySelector("#district");
const cityInput = document.querySelector("#city_regency");
const provinceInput = document.querySelector("#province")
const fullLocationInput = document.querySelector("#full_location");

const subDistrictSelect = document.querySelector("#sub_district");
let debounceTimeOut;

postalCodeInput.addEventListener("input", async () => {
    clearTimeout(debounceTimeOut); // Delay consume api ketika user mengetik

    debounceTimeOut = setTimeout(async () => {
        const zip = postalCodeInput.value;
    if (!zip) return;

    try {
        const res = await fetch(`/search-destination?search=${zip}`);
        const result = await res.json();
        const data = result.data || [];
        console.log(data)

        if (data.length > 0){

        subDistrictSelect.innerHTML = '';

        const subDistricts = data.map(d => d.subdistrict_name);
        
        subDistricts.forEach(name => {
            const optionSubDis = document.createElement("option");
            optionSubDis.value = name;
            optionSubDis.textContent = name;
            subDistrictSelect.appendChild(optionSubDis);
        });

        districtInput.value = data[0].district_name;

        cityInput.value = data[0].city_name;

        provinceInput.value = data[0].province_name;

        subDistrictSelect.onchange = () => {
                    const selected = subDistrictSelect.value;
                    const fullLocationFiltered = data.filter(
                        d => d.subdistrict_name === selected
                    );
                    fullLocationInput.value = fullLocationFiltered
                        .map(d => d.label)
                        .join(", ");
                };
}


    } catch (err) {
        console.error(err);
        alert("Failed to fetch address data");
    }
    }, 300)
    
});


    window.validateAndProceed = function validateAndProceed(step, refs, setStep, submitForm, errors) {
        const validations = {
            1: ['email', 'password'],
            2: ['first_name', 'last_name', 'phone', 'date_of_birth'],
            3: ['postal_code', 'sub_district', 'district', 'city_regency', 'province']
        };

        const customMessages = {
            email: 'Email is required and must be valid.',
            password: 'Password is required (min 6 characters).',
            first_name: 'First name is required.',
            last_name: 'Last name is required.',
            phone: 'Phone number is required.',
            date_of_birth: 'Date of birth is required.',
            postal_code: 'Postal code is required.',
            sub_district: 'Sub-district is required.',
            district: 'District is required.',
            city_regency: 'City/Regency is required.',
            province: 'Province is required.',
        };

        const fields = validations[step] || [];
        for (const fieldName of fields) {
            const field = refs[fieldName];
            if (!field?.checkValidity()) {
                field?.setCustomValidity(customMessages[fieldName] || 'This field is required.');
                field?.reportValidity();
                if (errors && errors[`${fieldName}Error`] !== undefined) {
                    errors[`${fieldName}Error`] = customMessages[fieldName];
                }
                console.error(`Validation failed for ${fieldName}: ${customMessages[fieldName]}`);
                return;
            } else {
                field?.setCustomValidity('');
            }
        }

        if (step < 3) {
            setStep(step + 1);
        } else {
            submitForm();
        }
    }
</script>


@endsection