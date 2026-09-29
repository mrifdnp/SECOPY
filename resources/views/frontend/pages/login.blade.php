@extends('frontend.layouts.home')

@section('title', 'Login')
@section('meta_robots', 'noindex, nofollow')

@section('content')

<div>
    <div
        class="md:px-0 md:px-[120px] md:mt-[90px] mt-[50px] flex flex-col items-center justify-center font-cabinet text-primary-dark-950">
        <div>
            <h1 class="font-plush text-[36px]">Sign In</h1>
            <p>Let's pick up where you left off.</p>

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
                <form action="{{ route('login.customer') }}" method="POST">
                    @csrf
                    <div class=" w-[342px] md:w-[426px]">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email"
                            value="{{ old('email', $defaultEmail ?? "") }}"
                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[8px] px-[16px] mt-[8px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                            required />
                    </div>
                    <div x-data="{ show: false }" class=" w-[342px] md:w-[426px] relative">
                        <label for="password">Password</label>

                        <input :type="show ? 'text' : 'password'" id="password" name="password"
                            placeholder="Enter your password"
                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[8px] px-[16px] mt-[8px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                            required />

                        <!-- Toggle icon -->
                        <div class="absolute right-4 top-[40px] cursor-pointer text-gray-600" @click="show = !show">
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
                    <p class="mt-[16px] text-primary-dark-700">
                        <a href="{{ route('customer.password.request') }}" class="text-primary-dark-700 hover:underline">
                            Forgot your password?
                        </a>
                    </p>
                    <button
                        class="w-full mt-[16px] bg-primary-dark-950 text-white font-plush text-[16px] py-[8px] px-[16px] rounded-[2px] hover:bg-primary-dark-700 transition-colors">
                        Sign In
                    </button>
                    <a href="{{ route('customer.google.redirect') }}" 
                    class="w-full mt-[16px] bg-white border border-gray-300 text-gray-700 font-cabinet text-[16px] py-[8px] px-[16px] rounded-[2px] hover:bg-gray-50 transition-colors flex items-center justify-center gap-3">
                        <iconify-icon icon="logos:google-icon" class="text-[18px]"></iconify-icon>
                        Login with Google
                    </a>
                </form>
                <div class="flex items-center my-6">
                    <hr class="flex-grow border-t border-gray-300" />
                    <span class="px-4 text-gray-400 text-sm">First time here?</span>
                    <hr class="flex-grow border-t border-gray-300" />
                </div>
                <button type="button" onclick="window.location.href='{{ route('registerForm.customer')}}'"
                    class="w-full bg-primary-light-50 font-plush text-[16px] py-[8px] px-[16px] rounded-[2px] hover:bg-primary-light-200 transition-colors">
                    Create an account
                </button>
            </div>
        </div>
</div>
</div>

@endsection

@section('css')

<link href="https://fonts.cdnfonts.com/css/cabinet-grotesk" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/css/intlTelInput.css" />

@endsection

@section('script')

<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

@endsection