@extends('frontend.layouts.home')

@section('title', 'Order History')
@section('meta_robots', 'noindex, nofollow')

@section('content')

    <div class="md:px-0 md:px-[120px] md:mt-[32px]">
        <div
            class="flex flex-col items-center justify-center pt-[24px] px-[16px] pb-0 md:pt-[40px] md:px-[120px] md:pb-[16px] w-full">
            <div class="w-full">
                <div class="flex gap-2 md:gap-3 items-center mb-[16px]">
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
                        <p class=" text-[30px] font-medium">Order History</p>
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
                                <li
                                    class=" px-[16px] py-[14px] rounded-[2px] flex items-center gap-[8px] bg-primary-light-50">
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
                    <div class="w-full" x-data="{ activeTab: 'upcoming' }">
                        <div class=" md:px-[24px] md:py-[16px]">
                            <div class="flex gap-[12px] mb-[24px]">
                                <button @click="activeTab = 'upcoming'"
                                    :class="activeTab === 'upcoming'
                                        ?
                                        'text-secondary-terra-800 border-secondary-terra-800' :
                                        'border-none'"
                                    class=" p-[12px]  font-bold border-b-2 ">
                                    Upcoming Orders
                                </button>
                                <button @click="activeTab = 'previous'"
                                    :class="activeTab === 'previous'
                                        ?
                                        'text-secondary-terra-800 border-secondary-terra-800' :
                                        'border-none'"
                                    class=" p-[12px]  font-bold border-b-2 ">
                                    Previous Orders
                                </button>
                            </div>
                            <div x-show="activeTab === 'upcoming'">
                                @if ($upcomingOrders->isEmpty())
                                    <p class="mb-[16px] text-neutral-dark-900">You haven't placed any orders yet.</p>
                                    <button
                                        class="flex justify-center items-center border border-primary-light-500 rounded-[2px] px-[8px] py-[6px]">
                                        <iconify-icon class="tabs-icon"
                                            icon="material-symbols-light:storefront-outline"></iconify-icon>
                                        <p class="p-[4px] text-[14px]">Start Shopping</p>
                                    </button>
                                @else
                                    @foreach ($upcomingOrders as $order)
                                        <div class="border border-[#F6F6F6]" x-data="{ showProducts: false }">
                                            <div class="flex justify-between p-[16px]">
                                                <div class="flex flex-col">
                                                    <p class="text-primary-dark-950 text-[16px] font-bold">Order #:
                                                        {{ $order->invoice_id }}</p>
                                                    <div class="flex gap-[8px] text-[12px]">
                                                        <span>{{ $order->items_sum_qty }} Products</span>
                                                        <span class="dot">•</span>
                                                        <span>{{ \Carbon\Carbon::parse($order->date)->format('H:i, d F Y') }}</span>
                                                    </div>
                                                    <p @class([
                                                        'px-[4px] w-fit my-[8px] text-[14px] rounded',
                                                        'text-[#904C18] bg-[#FCF7EA]' => $order->payment_status === 'pending',
                                                        'text-[#166534] bg-[#DCFCE7]' => $order->payment_status === 'paid',
                                                        'text-[#991B1B] bg-[#FEE2E2]' => $order->payment_status === 'EXPIRED',
                                                        'text-[#B91C1C] bg-[#FEE2E2]' => $order->payment_status === 'FAILED',
                                                        'text-[#1D4ED8] bg-[#DBEAFE]' => $order->payment_status === 'shipping',
                                                        'text-[#065F46] bg-[#D1FAE5]' => $order->payment_status === 'completed',
                                                        'text-[#78350F] bg-[#FEF3C7]' => $order->payment_status === 'canceled',
                                                    ])>
                                                        {{ $order->payment_status }}
                                                    </p>
                                                    <div class="flex gap-[16px]">
                                                        <div class="flex flex-col">
                                                            <p class="text-[10px] text-[#B0B0B0]">Total</p>
                                                            <p class="text-[14px] text-[#3D3D3D] font-bold">IDR
                                                                {{ number_format($order->total, 0, ',', '.') }}</p>
                                                        </div>
                                                        @if ($order->payment_status == 'pending')
                                                            <div class="flex flex-col">
                                                                <p class=" text-[10px] text-[#B0B0B0]">Expired at</p>
                                                                <p class=" text-[14px] text-[#3D3D3D]">
                                                                    {{ $order->xendit_expired_invoice }}</p>
                                                            </div>
                                                        @endif
                                                        @if ($order->payment_status == 'paid')
                                                            <div class="flex flex-col">
                                                                <p class=" text-[10px] text-[#B0B0B0]">Paid at</p>
                                                                <p class=" text-[14px] text-[#3D3D3D]">
                                                                    {{ $order->paid_at }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                        @if ($order->payment_status == 'shipping')
                                                            @if ($order->courier)
                                                                <div class="flex flex-col">
                                                                    <p class=" text-[10px] text-[#B0B0B0]">Courier</p>
                                                                    <p class=" text-[14px] text-[#3D3D3D]">{{ strtoupper($order->courier['code'] ?? '') }} ({{ $order->courier['service'] ?? '' }})
                                                                    </p>
                                                                </div>
                                                            @endif
                                                            <div class="flex flex-col">
                                                                <p class=" text-[10px] text-[#B0B0B0]">Air Waybill</p>
                                                                <p class=" text-[14px] text-[#3D3D3D]">{{ $order->resi ?? '-' }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex flex-col justify-between">
                                                    <div class="flex justify-end">
                                                        <iconify-icon icon="material-symbols-light:keyboard-arrow-right"
                                                            class="text-[20px] text-primary-dark-950"></iconify-icon>
                                                    </div>
                                                    @if ($order->payment_status === 'pending')
                                                        <div class="flex justify-end">
                                                            <a href="{{ route('frontend.invoice.id', ['id' => $order->id]) }}"
                                                                class="w-[80px] px-[8px] py-[6px] flex justify-center items-center border border-primary-light-500 text-primary-light-500 w-fit rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                                <span
                                                                    class="text-[14px] flex justify-center items-center gap-[4px]">
                                                                    <iconify-icon
                                                                        icon="material-symbols-light:cloud-download-outline"
                                                                        class="text-[14px]"></iconify-icon>
                                                                </span>
                                                            </a>
                                                        </div>
                                                        <a href="{{ $order->xendit_url }}" target="_blank"
                                                            class="w-[80px] px-[14px] py-[6px] text-white flex justify-center items-center bg-primary-dark-950 rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                            <span class="text-[14px]">Pay Now</span>
                                                        </a>
                                                    @elseif ($order->payment_status === 'shipping')
                                                        <div class="flex justify-end">
                                                            <a href="{{ route('frontend.invoice.id', ['id' => $order->id]) }}"
                                                                class="w-[80px] px-[8px] py-[6px] flex justify-center items-center border border-primary-light-500 text-primary-light-500 w-fit rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                                <span
                                                                    class="text-[14px] flex justify-center items-center gap-[4px]">
                                                                    <iconify-icon
                                                                        icon="material-symbols-light:cloud-download-outline"
                                                                        class="text-[14px]"></iconify-icon>
                                                                </span>
                                                            </a>
                                                        </div>
                                                        <a href="https://cekresi.com/?noresi={{ $order->resi }}" target="_blank"
                                                            class=" w-[80px] px-[8px] py-[6px] flex justify-center items-center border border-primary-light-500 text-primary-light-500 w-fit rounded-[2px] hover:bg-primary-light-500 hover:text-white">
                                                            <span
                                                                class="text-[14px] flex justify-center items-center gap-[4px]">
                                                                <iconify-icon
                                                                    icon="material-symbols-light:delivery-truck-speed-outline"
                                                                    class="text-[14px]"></iconify-icon> Track
                                                            </span>
                                                        </a>
                                                    @elseif ($order->payment_status === 'paid')
                                                        <a href="{{ route('frontend.invoice.id', ['id' => $order->id]) }}"
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
                                                            <a href="{{ route('frontend.invoice.id', ['id' => $order->id]) }}"
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
                                            <!-- Product Details Toggle -->
                                            <div class="border-t border-[#F6F6F6] bg-[#FAFAFA] p-[10px] cursor-pointer flex justify-center items-center gap-[4px] text-primary-dark-950 hover:bg-[#F0F0F0] transition-colors" @click="showProducts = !showProducts">
                                                <span class="text-[12px] font-medium" x-text="showProducts ? 'Hide Products' : 'View Products'"></span>
                                                <iconify-icon icon="material-symbols-light:keyboard-arrow-down" class="text-[18px] transition-transform duration-300" :class="showProducts ? 'rotate-180' : ''"></iconify-icon>
                                            </div>
                                            <div class="p-[16px] bg-white flex flex-col gap-[12px] border-t border-[#F6F6F6]" x-show="showProducts" x-transition x-cloak>
                                                @foreach ($order->items as $item)
                                                    <div class="flex justify-between items-center bg-[#FAFAFA] p-[12px] border border-[#F6F6F6] rounded">
                                                        <div class="flex items-center gap-[12px]">
                                                            @if($item->productDetail?->product?->main_pic)
                                                                <img src="{{ $item->productDetail->product->main_pic }}" class="w-[50px] h-[50px] object-cover rounded" alt="{{ $item->product_detail['product_title'] ?? 'Product' }}">
                                                            @else
                                                                <div class="w-[50px] h-[50px] bg-gray-200 rounded flex items-center justify-center">
                                                                    <iconify-icon icon="material-symbols-light:image-outline" class="text-[24px] text-gray-400"></iconify-icon>
                                                                </div>
                                                            @endif
                                                            <div class="flex flex-col">
                                                            <p class="text-[14px] font-bold text-primary-dark-950">{{ $item->product_detail['product_title'] ?? $item->productDetail?->product?->title ?? 'Unknown Product' }}</p>
                                                            <p class="text-[12px] text-neutral-dark-900">
                                                                {{ $item->product_detail['variant_name'] ?? $item->productDetail?->variant?->name ?? '' }} 
                                                                {{ ($item->product_detail['color_label'] ?? $item->productDetail?->color?->label) ? ' - ' . ($item->product_detail['color_label'] ?? $item->productDetail?->color?->label) : '' }}
                                                            </p>
                                                        </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <p class="text-[12px] text-neutral-dark-900">{{ $item->qty }} x IDR {{ number_format($item->price, 0, ',', '.') }}</p>
                                                            <p class="text-[14px] font-bold text-primary-dark-950">IDR {{ number_format($item->qty * $item->price, 0, ',', '.') }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div x-show="activeTab === 'previous'">
                                @if ($previousOrders->isEmpty())
                                    <p class="mb-[16px] text-neutral-dark-900">No previous orders found.</p>
                                @else
                                    @foreach ($previousOrders as $order)
                                        <div class="border border-[#F6F6F6]" x-data="{ showProducts: false }">
                                            <div class="flex justify-between p-[16px]">
                                                <div class="flex flex-col">
                                                    <p class="text-primary-dark-950 text-[16px] font-bold">Order #:
                                                        {{ $order->invoice_id }}</p>
                                                    <div class="flex gap-[8px] text-[12px]">
                                                        <span>{{ $order->items_sum_qty }} Products</span>
                                                        <span class="dot">•</span>
                                                        <span>{{ \Carbon\Carbon::parse($order->date)->format('H:i, d F Y') }}</span>
                                                    </div>
                                                    <p @class([
                                                        'px-[4px] w-fit my-[8px] text-[14px] rounded',
                                                        'text-[#904C18] bg-[#FCF7EA]' => $order->payment_status === 'pending',
                                                        'text-[#166534] bg-[#DCFCE7]' => $order->payment_status === 'paid',
                                                        'text-[#991B1B] bg-[#FEE2E2]' => $order->payment_status === 'EXPIRED',
                                                        'text-[#B91C1C] bg-[#FEE2E2]' => $order->payment_status === 'FAILED',
                                                        'text-[#1D4ED8] bg-[#DBEAFE]' => $order->payment_status === 'shipping',
                                                        'text-[#065F46] bg-[#D1FAE5]' => $order->payment_status === 'completed',
                                                        'text-[#78350F] bg-[#FEF3C7]' => $order->payment_status === 'canceled',
                                                    ])>
                                                        {{ $order->payment_status }}
                                                    </p>
                                                    <div class="flex gap-[16px]">
                                                        <div class="flex flex-col">
                                                            <p class="text-[10px] text-[#B0B0B0]">Total</p>
                                                            <p class="text-[14px] text-[#3D3D3D] font-bold">IDR
                                                                {{ number_format($order->total, 0, ',', '.') }}</p>
                                                        </div>
                                                        <div class="flex flex-col">
                                                            <p class="text-[10px] text-[#B0B0B0]">Expired at</p>
                                                            <p class="text-[14px] text-[#3D3D3D]">
                                                                {{ \Carbon\Carbon::parse($order->xendit_expired_invoice)->format('H:i, d F Y') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col justify-between">
                                                    <div class="flex justify-end">
                                                        <iconify-icon icon="material-symbols-light:keyboard-arrow-right"
                                                            class="text-[20px] text-primary-dark-950"></iconify-icon>
                                                    </div>
                                                    <div class="flex justify-end">
                                                        <a href="{{ route('frontend.invoice.id', ['id' => $order->id]) }}"
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
                                                </div>
                                            </div>
                                            <!-- Product Details Toggle -->
                                            <div class="border-t border-[#F6F6F6] bg-[#FAFAFA] p-[10px] cursor-pointer flex justify-center items-center gap-[4px] text-primary-dark-950 hover:bg-[#F0F0F0] transition-colors" @click="showProducts = !showProducts">
                                                <span class="text-[12px] font-medium" x-text="showProducts ? 'Hide Products' : 'View Products'"></span>
                                                <iconify-icon icon="material-symbols-light:keyboard-arrow-down" class="text-[18px] transition-transform duration-300" :class="showProducts ? 'rotate-180' : ''"></iconify-icon>
                                            </div>
                                            <div class="p-[16px] bg-white flex flex-col gap-[12px] border-t border-[#F6F6F6]" x-show="showProducts" x-transition x-cloak>
                                                @foreach ($order->items as $item)
                                                    <div class="flex justify-between items-center bg-[#FAFAFA] p-[12px] border border-[#F6F6F6] rounded">
                                                        <div class="flex items-center gap-[12px]">
                                                            @if($item->productDetail?->product?->main_pic)
                                                                <img src="{{ $item->productDetail->product->main_pic }}" class="w-[50px] h-[50px] object-cover rounded" alt="{{ $item->product_detail['product_title'] ?? 'Product' }}">
                                                            @else
                                                                <div class="w-[50px] h-[50px] bg-gray-200 rounded flex items-center justify-center">
                                                                    <iconify-icon icon="material-symbols-light:image-outline" class="text-[24px] text-gray-400"></iconify-icon>
                                                                </div>
                                                            @endif
                                                            <div class="flex flex-col">
                                                            <p class="text-[14px] font-bold text-primary-dark-950">{{ $item->product_detail['product_title'] ?? $item->productDetail?->product?->title ?? 'Unknown Product' }}</p>
                                                            <p class="text-[12px] text-neutral-dark-900">
                                                                {{ $item->product_detail['variant_name'] ?? $item->productDetail?->variant?->name ?? '' }} 
                                                                {{ ($item->product_detail['color_label'] ?? $item->productDetail?->color?->label) ? ' - ' . ($item->product_detail['color_label'] ?? $item->productDetail?->color?->label) : '' }}
                                                            </p>
                                                        </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <p class="text-[12px] text-neutral-dark-900">{{ $item->qty }} x IDR {{ number_format($item->price, 0, ',', '.') }}</p>
                                                            <p class="text-[14px] font-bold text-primary-dark-950">IDR {{ number_format($item->qty * $item->price, 0, ',', '.') }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
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
