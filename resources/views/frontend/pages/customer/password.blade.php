@extends('frontend.layouts.home')

@section('title', 'Change Password')
@section('meta_robots', 'noindex, nofollow')

@section('content')

<div class="md:px-0 md:px-[120px] md:mt-[32px]">
        <div
            class="flex flex-col items-center justify-center pt-[24px] px-[16px] pb-0 md:pt-[40px] md:px-[120px] md:pb-[16px] w-full">
            <div class="w-full">
                <div class="flex gap-2 md:gap-3 items-center mb-[16px]">
                    <p class=" font-cabinet text-[16px]">Account</p>
                    <iconify-icon icon="material-symbols-light:chevron-right" class="text-[16px]"></iconify-icon>
                    <p class=" text-[16px]">Profile</p>
                </div>
                <div class="flex">
                    <div class="hidden md:flex flex-col w-[364px] mb-[16px] justify-center">
                        <p class=" text-[24px] font-bold ">{{ Auth::guard('customer')->user()->first_name }} {{ Auth::guard('customer')->user()->last_name }}</p>
                        <p>{{ Auth::guard('customer')->user()->email }}</p>
                    </div>
                    <div class="w-full flex items-center mb-[16px] text-primary-dark-950 md:pl-[16px] font-plush">
                        <p class=" text-[30px] font-medium">Change Password</p>
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
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">My Account</a>
                                </li>
                                <li class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:article-person-outline-rounded"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('customer.profile') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">Profile</a>
                                </li>
                                <li
                                    class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:map-pin-review-outline"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('customer.address') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">Address</a>
                                </li>
                                <li class=" px-[16px] py-[14px] bg-primary-light-50 rounded-[2px] flex items-center gap-[8px]">
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
                            <form action="{{ route('customer.updatePassword') }}" method="POST">
                                @if(session('success'))
                                <div class="text-green-700 py-2">
                                    {{ session('success') }}
                                </div>
                                @endif
                                
                                @if($errors->any())
                                <div class="text-red-700 py-2">
                                    <ul>
                                        @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                @csrf
                                <div x-data="{ show: false }" class=" w-full relative">
                                    <label for="old-password">Old Password</label>

                                    <input :type="show ? 'text' : 'password'" id="old-password" name="old-password"
                                        placeholder="Enter your password"
                                        class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[8px] px-[16px] mt-[8px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                        required />

                                    <!-- Toggle icon -->
                                    <div class="absolute right-4 top-[40px] cursor-pointer text-gray-600"
                                        @click="show = !show">
                                        <template x-if="show">
                                            <iconify-icon icon="material-symbols-light:visibility-outline"
                                                class="text-[23px]"></iconify-icon>
                                        </template>
                                        <template x-if="!show">
                                            <iconify-icon icon="material-symbols-light:visibility-off-outline-rounded"
                                                class="text-[23px]"></iconify-icon>
                                        </template>
                                    </div>
                                </div>
                                <div x-data="{ show: false }" class=" w-full relative">
                                    <label for="new-password">New Password</label>

                                    <input :type="show ? 'text' : 'password'" id="new-password" name="new-password"
                                        placeholder="Enter your password"
                                        class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[8px] px-[16px] mt-[8px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                        required />

                                    <!-- Toggle icon -->
                                    <div class="absolute right-4 top-[40px] cursor-pointer text-gray-600"
                                        @click="show = !show">
                                        <template x-if="show">
                                            <iconify-icon icon="material-symbols-light:visibility-outline"
                                                class="text-[23px]"></iconify-icon>
                                        </template>
                                        <template x-if="!show">
                                            <iconify-icon icon="material-symbols-light:visibility-off-outline-rounded"
                                                class="text-[23px]"></iconify-icon>
                                        </template>
                                    </div>
                                </div>
                                <div x-data="{ show: false }" class=" w-full relative">
                                    <label for="confirm-password">Confirm Password</label>

                                    <input :type="show ? 'text' : 'password'" id="confirm-password" name="confirm-password"
                                        placeholder="Enter your password"
                                        class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[8px] px-[16px] mt-[8px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                                        required />

                                    <!-- Toggle icon -->
                                    <div class="absolute right-4 top-[40px] cursor-pointer text-gray-600"
                                        @click="show = !show">
                                        <template x-if="show">
                                            <iconify-icon icon="material-symbols-light:visibility-outline"
                                                class="text-[23px]"></iconify-icon>
                                        </template>
                                        <template x-if="!show">
                                            <iconify-icon icon="material-symbols-light:visibility-off-outline-rounded"
                                                class="text-[23px]"></iconify-icon>
                                        </template>
                                    </div>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/css/intlTelInput.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

@endsection

@section('script')

<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/intlTelInput.min.js"></script>
<script>
    const input = document.querySelector("#phone");
    window.intlTelInput(input, {
        separateDialCode: true,
        initialCountry: "id",
        loadUtils: () => import("https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/utils.js"),
    });

    flatpickr("#datepicker-wrapper", {
        wrap: true,
        dateFormat: "Y-m-d",
    });
</script>

@endsection