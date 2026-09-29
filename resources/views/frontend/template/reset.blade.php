@extends('frontend.layouts.home')

@section('title', 'Reset Password')

@section('content')
 <div
        class="md:px-0 md:px-[120px] md:mt-[90px] mt-[50px] flex flex-col items-center justify-center font-cabinet text-primary-dark-950">
        <div>
            <h1 class="font-plush text-[36px]">Reset Password</h1>

            @if (session('status'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    {{ session('status') }}
                </div>
            @endif

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
                <form action="{{ route('customer.password.update') }}" method="POST">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="w-[342px] md:w-[426px]">
                        <label for="password">Password Baru</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password"
                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[8px] px-[16px] mt-[8px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                            required />
                    </div>

                    <div class="w-[342px] md:w-[426px]">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password"
                            class="w-full border border-primary-light-200 rounded-[2px] text-[14px] font-cabinet py-[8px] px-[16px] mt-[8px] mb-[16px] focus:outline-none focus:border-1 focus:border-primary-light-500"
                            required />
                    </div>

                    <button
                        class="w-full mt-[16px] bg-primary-dark-950 text-white font-plush text-[16px] py-[8px] px-[16px] rounded-[2px] hover:bg-primary-dark-700 transition-colors">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
