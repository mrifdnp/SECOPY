@extends('frontend.layouts.home')

@section('title', 'Account')
@section('meta_robots', 'noindex, nofollow')

@section('content')

    <div class="md:px-0 md:px-[120px] md:mt-[32px]">
        <div
            class="flex flex-col items-center justify-center pt-[24px] px-[16px] pb-0 md:pt-[40px] md:px-[120px] md:pb-[16px] w-full">
            <div>
                <div class="flex md:gap-3 items-center mb-[16px]">
                    <p class=" font-cabinet text-[16px]">Account</p>
                    <iconify-icon icon="material-symbols-light:chevron-right" class="text-[16px]"></iconify-icon>
                    <p class=" text-[16px]">My Account</p>
                </div>
                <div class="flex">
                    <div class="hidden md:flex flex-col w-[364px] mb-[16px] justify-center">
                        <p class=" text-[24px] font-bold ">{{ Auth::guard('customer')->user()->first_name }}
                            {{ Auth::guard('customer')->user()->last_name }}</p>
                        <p>{{ Auth::guard('customer')->user()->email }}</p>
                    </div>
                    <div class="w-full flex items-center mb-[16px] text-primary-dark-950 md:pl-[16px] font-plush">
                        <p class=" text-[30px] font-medium">My Account</p>
                    </div>
                </div>

                <div class="flex justify-center md:border md:border-[#F6F6F6] md:w-[800px]">
                    <div
                        class="hidden md:w-[364px] md:border-r md:border-[#F6F6F6] md:flex flex-col items-center p-[16px] ">
                        <nav class="w-full">
                            <ul class="flex flex-col">
                                <li
                                    class=" px-[16px] py-[14px] rounded-[2px] bg-primary-light-50 flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:home-storage-outline-rounded"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('customer.account') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">My
                                        Account</a>
                                </li>
                                <li class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
                                    <iconify-icon icon="material-symbols-light:article-person-outline-rounded"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('customer.profile') }}"
                                        class="text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800">Profile</a>
                                </li>
                                <li class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px]">
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
                                    <form action="{{ route('logout.customer') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="font-plush text-[16px]">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="w-full ">
                        <div class=" md:px-[24px] md:py-[16px]">
                            <div class="flex flex-col md:gap-[16px] mb-[24px]">
                                <p class=" font-medium text-[20px] mb-[16px] md:mb-0">Recent Orders</p>
                                @foreach ($orderHistory as $item)
                                    <div class=" border border-[#F6F6F6]">
                                        <div class="flex justify-between p-[16px]">
                                            <div class="flex flex-col">
                                                <p class=" text-primary-dark-950 text-[16px] font-bold">Order #:
                                                    {{ $item->invoice_id }}</p>
                                                <div class="flex gap-[8px] text-[12px]">
                                                    <span>{{ $item->items_sum_qty }} Products</span>
                                                    <span class="dot">•</span>
                                                    <span>{{ \Carbon\Carbon::parse($item->created_at)->format('g:i, j F Y') }}</span>
                                                </div>
                                                <p @class([
                                                    'px-[4px] w-fit my-[8px] text-[14px] rounded',
                                                    'text-[#904C18] bg-[#FCF7EA]' => $item->payment_status === 'pending',
                                                    'text-[#166534] bg-[#DCFCE7]' => $item->payment_status === 'paid',
                                                    'text-[#991B1B] bg-[#FEE2E2]' => $item->payment_status === 'EXPIRED',
                                                    'text-[#B91C1C] bg-[#FEE2E2]' => $item->payment_status === 'FAILED',
                                                    'text-[#1D4ED8] bg-[#DBEAFE]' => $item->payment_status === 'shipping',
                                                    'text-[#065F46] bg-[#D1FAE5]' => $item->payment_status === 'completed',
                                                    'text-[#78350F] bg-[#FEF3C7]' => $item->payment_status === 'canceled',
                                                ])>
                                                    {{ $item->payment_status }}
                                                </p>

                                                <div class="flex gap-[16px]">
                                                    <div class="flex flex-col">
                                                        <p class=" text-[10px] text-[#B0B0B0]">Total</p>
                                                        <p class=" text-[14px] text-[#3D3D3D] font-bold">IDR
                                                            {{ number_format($item->total, 0, ',', '.') }}</p>
                                                    </div>
                                                    @if ($item->payment_status == 'pending')
                                                        <div class="flex flex-col">
                                                            <p class=" text-[10px] text-[#B0B0B0]">Expired at</p>
                                                            <p class=" text-[14px] text-[#3D3D3D]">
                                                                {{ $item->xendit_expired_invoice }}</p>
                                                        </div>
                                                    @endif
                                                    @if ($item->payment_status == 'paid')
                                                        <div class="flex flex-col">
                                                            <p class=" text-[10px] text-[#B0B0B0]">Paid at</p>
                                                            <p class=" text-[14px] text-[#3D3D3D]">{{ $item->paid_at }}</p>
                                                        </div>
                                                    @endif
                                                    @if ($item->payment_status == 'shipping')
                                                        <div class="flex flex-col">
                                                            <p class=" text-[10px] text-[#B0B0B0]">Air Waybill</p>
                                                            <p class=" text-[14px] text-[#3D3D3D]">{{ $item->resi }} </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex flex-col justify-between">
                                                <div class=" flex justify-end">
                                                    <iconify-icon icon="material-symbols-light:keyboard-arrow-right"
                                                        class="text-[20px] text-primary-dark-950"></iconify-icon>
                                                </div>
                                                @if ($item->payment_status === 'pending')
                                                    <div class="flex justify-end">
                                                        <a href="{{ route('frontend.invoice.id', ['id' => $item->id]) }}"
                                                            class="w-[80px] px-[8px] py-[6px] flex justify-center items-center border border-primary-light-500 text-primary-light-500 w-fit rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                            <span
                                                                class="text-[14px] flex justify-center items-center gap-[4px]">
                                                                <iconify-icon
                                                                    icon="material-symbols-light:cloud-download-outline"
                                                                    class="text-[14px]"></iconify-icon>
                                                            </span>
                                                        </a>
                                                    </div>
                                                    <a href="{{ $item->xendit_url }}" target="_blank"
                                                        class="w-[80px] px-[14px] py-[6px] text-white flex justify-center items-center bg-primary-dark-950 rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                        <span class="text-[14px]">Pay Now</span>
                                                    </a>
                                                @elseif ($item->payment_status === 'shipping')
                                                    <div class="flex justify-end">
                                                        <a href="{{ route('frontend.invoice.id', ['id' => $item->id]) }}"
                                                            class="w-[80px] px-[8px] py-[6px] flex justify-center items-center border border-primary-light-500 text-primary-light-500 w-fit rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                            <span
                                                                class="text-[14px] flex justify-center items-center gap-[4px]">
                                                                <iconify-icon
                                                                    icon="material-symbols-light:cloud-download-outline"
                                                                    class="text-[14px]"></iconify-icon>
                                                            </span>
                                                        </a>
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <p class=" text-[10px] text-[#B0B0B0]">Courier</p>
                                                        <p class=" text-[14px] text-[#3D3D3D]">
                                                            {{ $item->courier['name'] }}
                                                        </p>
                                                    </div>
                                                @elseif ($item->payment_status === 'paid')
                                                    <a href="{{ route('frontend.invoice.id', ['id' => $item->id]) }}"
                                                        class="w-[80px] px-[8px] py-[6px] flex justify-center items-center border border-primary-light-500 text-primary-light-500 w-fit rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                        <span
                                                            class="text-[14px] flex justify-center items-center gap-[4px]">
                                                            <iconify-icon
                                                                icon="material-symbols-light:cloud-download-outline"
                                                                class="text-[14px]"></iconify-icon> Invoice
                                                        </span>
                                                    </a>
                                                @else
                                                    <div class="flex justify-end">
                                                        <a href="{{ route('frontend.invoice.id', ['id' => $item->id]) }}"
                                                            class="w-[80px] px-[8px] py-[6px] flex justify-center items-center border border-primary-light-500 text-primary-light-500 w-fit rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                            <span
                                                                class="text-[14px] flex justify-center items-center gap-[4px]">
                                                                <iconify-icon
                                                                    icon="material-symbols-light:cloud-download-outline"
                                                                    class="text-[14px]"></iconify-icon>
                                                            </span>
                                                        </a>
                                                    </div>
                                                    <button
                                                        class=" w-[80px] px-[8px] py-[6px] flex justify-center items-center border border-primary-light-500 text-primary-light-500 w-fit rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                        <span
                                                            class="text-[14px] flex justify-center items-center gap-[4px]">
                                                            Re-order
                                                        </span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex flex-col gap-[16px]">
                                <p class=" font-medium text-[20px]">Address</p>
                                <div class="flex border border-[#F6F6F6] p-[16px]">
                                    <div class="flex flex-col gap-[8px]">
                                        <div class="flex gap-[8px] items-center">
                                            <p class=" text-neutral-dark-900 text-[14px] font-bold">
                                                {{ Auth::guard('customer')->user()->first_name }}
                                                {{ Auth::guard('customer')->user()->last_name }}</p>
                                            <span class="dot">•</span>
                                            <p class=" text-neutral-dark-400 text-[14px]">
                                                +{{ Auth::guard('customer')->user()->phone }}</p>
                                        </div>
                                        <p class=" text-[14px] text-[#888]">{{ Auth::guard('customer')->user()->address }}
                                        </p>
                                        <div class="flex justify-end">
                                            <button onclick="window.location.href='{{ route('customer.address') }}'"
                                                class=" flex justify-center items-center gap-[4px] w-fit px-[8px] py-[4px] text-primary-dark-700 border border-primary-light-500 rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                <iconify-icon icon="material-symbols-light:edit-square-outline"
                                                    class="text-[14px]"></iconify-icon>
                                                Change Your Address
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

@endsection

@section('script')

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

@endsection
