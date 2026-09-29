<header x-data="{ sidebarAccount: false, sidebarMenu: false }" x-cloak class="font-cabinet">
    <!-- Second Navigation -->
    <div id="secondNav"
        class="transition-all hidden lg:block h-[32px] transition-all bg-[#F7F6EF] w-full flex items-center justify-center py-[6px]">
        <div class="flex justify-center gap-[24px]">
            @foreach ($top_page as $item)
                <a class="flex gap-2 items-center"
                    href="{{ route('frontend.topPages', ['id' => $item['id'], 'title' => Str::slug($item['title'])]) }}">
                    <i class="{{ strtolower($item['icon']) }} text-primary-dark-700"></i>
                    <p class="font-cabinet text-primary-dark-700 text-[14px]">
                        {{ $item['title'] }}
                    </p>
                </a>
            @endforeach
            <a class="flex gap-2 items-center" href="{{ url('login-customer') }}">
                <i class="ri-user-line text-primary-dark-700 text-primary-dark-700"></i>
                <p class="font-cabinet text-primary-dark-700 text-[14px]">
                    Sign In / Sign Up
                </p>
            </a>
        </div>
    </div>

    <!-- Main Navigation -->
    <div x-data="{ navOpen: false }" id="mainNav"
        class="relative md:block z-[40] transition-all transition-transform duration-300 ease-in-out bg-white">
        <!-- Logo and Search Bar -->
        <div @mouseenter="navOpen = ''" class="px-[16px] md:px-[120px] border border-b border-primary-light-50">
            <div class="flex justify-between py-[16px]">
                <div class="w-[160px] h-[40px] flex items-center cursor-pointer">
                    <a href=" {{ route('frontend.index') }}" class="flex items-center">
                        <img src="{{ asset('assets/homepage/Logo.svg') }}" alt="" />
                    </a>
                </div>
                <form action="{{ route('frontend.product.search') }}" method="GET" id="searchForm"
                    class="relative w-[529px] h-[40px] mx-[13px] md:mx-0">
                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <img src="{{ asset('assets/homepage/icons/search.svg') }}" alt="Search Icon"
                            class="w-[24px] h-[24px]" />
                    </span>
                    <input type="search" name="search" id="searchInput" placeholder="Search"
                        value="{{ request('search') }}"
                        class="pl-4 border rounded-[2px] text-[14px] font-plush md:text-[16px] border-primary-light-200 
               focus:outline-none focus:border-2 focus:border-primary-light-500 
               py-[6px] px-[8px] md:py-[8px] md:px-[16px] w-full h-full 
               placeholder:text-primary-light-200" />
                </form>
                <div class="flex items-center">
                    <div onclick="window.location.href='{{ route('toggle.lang') }}'"
                        class="cursor-pointer w-[40px] h-[40px] flex items-center justify-center p-[12px]">

                        @if (session('lang', 'en') === 'en')
                            <img src="{{ asset('assets/homepage/icons/english.svg') }}" alt="EN"
                                class="w-[22px] h-[22px]" />
                        @else
                            <img src="{{ asset('assets/homepage/icons/indonesia.svg') }}" alt="ID"
                                class="w-[22px] h-[22px]" />
                        @endif
                    </div>


                    <div class="w-[40px] h-[40px] hidden md:flex items-center justify-center p-[12px] relative">
                        <iconify-icon onclick="window.location.href='{{ route('frontend.wishlist') }}'"
                            icon="material-symbols-light:favorite-outline"
                            class="text-[24px] cursor-pointer text-primary-dark-950"></iconify-icon>
                        @if ($wishlistCount > 0)
                            <span
                                class="absolute top-0 right-0 bg-red-600 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center">
                                {{ $wishlistCount }}
                            </span>
                        @endif
                    </div>

                    <div class="w-[40px] h-[40px] flex items-center justify-center p-[12px] relative">
                        <a href="{{ route('frontend.product.cart') }}" class=" cursor-pointer">
                            <img src="{{ asset('assets/homepage/icons/buy.svg') }}" alt="" />
                        </a>
                        @if ($cartCount > 0)
                            <span
                                class="absolute top-0 right-0 bg-red-600 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </div>
                    {{-- @if (Auth::check()) --}}
                    <div class=" relative hover:bg-primary-light-50">
                        @if (Auth::guard('customer')->check())
                            <div @click="sidebarAccount = !sidebarAccount; sidebarMenu = false"
                                class="w-[20px] h-[20px] rounded-full overflow-hidden cursor-pointer m-[10px]">
                                <img src="https://api.dicebear.com/7.x/initials/svg?seed=Budi&radius=50&backgroundColor=272E3F&fontSize=32"
                                    alt="Avatar" class="w-full h-full object-cover" />
                            </div>
                            <div x-show="sidebarAccount"
                                class="hidden lg:flex flex-col w-[284px] h-fit lg:absolute border border-primary-light-200 bg-white top-[50px] right-[0px]">
                                <div
                                    class="flex flex-col p-[16px] text-primary-dark-950 border-b border-primary-light-200">
                                    <p class="text-[24px] font-semibold">
                                        {{ Auth::guard('customer')->user()->first_name }}
                                        {{ Auth::guard('customer')->user()->last_name }}</p>
                                    <p class="text-[#888] text-[16px]">{{ Auth::guard('customer')->user()->email }}</p>
                                </div>
                                <div class="flex flex-col text-primary-dark-950">
                                    <nav class="px-[16px] py-[16px]">
                                        <ul class="flex flex-col gap-[14px]">
                                            <li
                                                class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                                <iconify-icon icon="material-symbols-light:home-storage-outline-rounded"
                                                    class="text-[20px]"></iconify-icon>
                                                <a href="{{ route('customer.account') }}"
                                                    class="font-plush text-[16px]">My
                                                    Account</a>
                                            </li>
                                            <li
                                                class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                                <iconify-icon
                                                    icon="material-symbols-light:article-person-outline-rounded"
                                                    class="text-[20px]"></iconify-icon>
                                                <a href="{{ route('customer.profile') }}"
                                                    class="font-plush text-[16px]">Profile</a>
                                            </li>
                                            <li
                                                class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                                <iconify-icon icon="material-symbols-light:map-pin-review-outline"
                                                    class="text-[20px]"></iconify-icon>
                                                <a href="{{ route('customer.address') }}"
                                                    class="font-plush text-[16px]">Address</a>
                                            </li>
                                            <li
                                                class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                                <iconify-icon icon="material-symbols-light:lock-open-outline"
                                                    class="text-[20px]"></iconify-icon>
                                                <a href="{{ route('customer.changePassword') }}"
                                                    class="font-plush text-[16px]">Password</a>
                                            </li>
                                            <li
                                                class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                                <iconify-icon icon="material-symbols-light:receipt-long-outline"
                                                    class="text-[20px]"></iconify-icon>
                                                <a href="{{ route('customer.orderHistory') }}"
                                                    class="font-plush text-[16px]">Order
                                                    History</a>
                                            </li>
                                            <li
                                                class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                                <iconify-icon icon="material-symbols-light:favorite-outline"
                                                    class="text-[20px]"></iconify-icon>
                                                <a href="{{ route('frontend.wishlist') }}"
                                                    class="font-plush text-[16px]">Wishlist</a>
                                            </li>
                                            <li
                                                class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                                <iconify-icon icon="material-symbols-light:logout-rounded"
                                                    class="text-[20px]"></iconify-icon>
                                                <form action="{{ route('logout.customer') }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="font-plush text-[16px]">Logout</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        @else
                            <div @click="sidebarAccount = !sidebarAccount; sidebarMenu = false"
                                class=" rounded-full overflow-hidden flex justify-center items-center p-[10px]">
                                <iconify-icon icon="material-symbols-light:person-outline-rounded"
                                    class="text-[24px] cursor-pointer text-primary-dark-950"></iconify-icon>
                            </div>
                            <div x-show="sidebarAccount"
                                class="hidden md:absolute md:flex flex-col w-[349px] right-0 bg-white p-[16px] border border-primary-light-200 z-50">
                                <div class="flex flex-col items-center justify-center">
                                    <p class=" text-primary-dark-950 text-[20px] font-bold leading-[1.2]">Let's pick up
                                        where you left off.</p>
                                    <p class=" text-primary-dark-950 text-[16px] text-center leading-[1.2] mt-[8px]">
                                        Please
                                        sign in to continue, or create an account to save your progress.</p>
                                </div>
                                <div class="flex flex-col gap-[12px] mt-[20px]">
                                    <button onclick="window.location.href='{{ route('loginForm.customer') }}'"
                                        class="text-white bg-primary-dark-950 hover:bg-primary-dark-800 transition-colors duration-200 py-[12px] px-[16px] rounded">Sign
                                        In</button>
                                    <button onclick="window.location.href='{{ route('registerForm.customer') }}'"
                                        class="border border-primary-dark-950 text-primary-dark-950 transition-colors duration-200 py-[12px] px-[16px] rounded">Create
                                        Account</button>
                                </div>
                            </div>
                        @endif
                    </div>
                    {{-- @else
                    <div class="relative">
                        <div @click="sidebarAccount = !sidebarAccount; sidebarMenu = false"
                            class=" rounded-full overflow-hidden flex justify-center items-center p-[10px]">
                            <iconify-icon icon="material-symbols-light:person-outline-rounded"
                                class="text-[24px] cursor-pointer text-primary-dark-950"></iconify-icon>
                        </div>
                        <div x-show="sidebarAccount"
                            class="hidden md:absolute md:flex flex-col w-[349px] right-0 bg-white p-[16px] border border-primary-light-200">
                            <div class="flex flex-col items-center justify-center">
                                <p class=" text-primary-dark-950 text-[20px] font-bold leading-[1.2]">Let's pick up
                                    where you left off.</p>
                                <p class=" text-primary-dark-950 text-[16px] text-center leading-[1.2] mt-[8px]">Please
                                    sign in to continue, or create an account to save your progress.</p>
                            </div>
                            <div class="flex flex-col gap-[12px] mt-[20px]">
                                <button onclick="window.location.href='{{ route('loginForm.customer') }}'"
                                    class="text-white bg-primary-dark-950 hover:bg-primary-dark-800 transition-colors duration-200 py-[12px] px-[16px] rounded">Sign
                                    In</button>
                                <button onclick="window.location.href='{{ route('registerForm.customer') }}'"
                                    class="border border-primary-dark-950 text-primary-dark-950 transition-colors duration-200 py-[12px] px-[16px] rounded">Create
                                    Account</button>
                            </div>
                        </div>
                    </div>
                    @endif --}}
                    <div
                        class="lg:hidden w-[40px] h-[40px] flex items-center justify-center p-[12px] text-primary-dark-950">
                        <iconify-icon
                            :icon="sidebarMenu ? 'material-symbols-light:close' : 'material-symbols-light:menu'"
                            class="text-[24px] cursor-pointer"
                            @click="sidebarMenu = !sidebarMenu; sidebarAccount = false"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="hidden lg:block px-[120px] flex justify-center border-b border-primary-light-200">
            <div class="flex justify-center">
                <div class="relative px-[20px] flex justify-center items-center relative">
                    <a href="{{ route('frontend.newest') }}" sidebarAccount=false"
                        class="text-secondary-terra-600 font-plush text-[16px] hover:text-secondary-terra-800 py-[12px]">New!</a>
                    <div class="absolute top-[12px] right-[0px] w-[8px] h-[8px] rounded-full bg-[#F1931E]"></div>
                </div>
                @foreach ($active_seasonals ?? [] as $seasonal)
                    <div class="relative flex justify-center items-center">
                        <a href="{{ route('frontend.seasonal', $seasonal->slug) }}"
                            @mouseenter="navOpen = 'seasonal_{{ $seasonal->id }}'; sidebarAccount = false"
                            :class="[
                                'px-[20px] text-secondary-terra-600 font-plush text-[16px] hover:text-secondary-terra-800 py-[12px] transition-all',
                                navOpen === 'seasonal_{{ $seasonal->id }}' ?
                                'border-b-2 pb-[11px] mb-[-1px] border-secondary-terra-800 text-secondary-terra-800' : ''
                            ]">
                            {{ $seasonal->name }}
                        </a>
                    </div>
                @endforeach
                @foreach ($navigation as $main)
                    <a href="{{ route('frontend.productByMainCategory', $main->slug) }}"
                        @mouseenter="navOpen = '{{ $main->id }}'; sidebarAccount = false"
                        :class="[
                            'px-[20px] text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800 py-[12px]',
                            navOpen === '{{ $main->id }}' ?
                            'border-b-2 pb-[11px] mb-[-1px] border-secondary-terra-800 text-secondary-terra-800' : ''
                        ]">
                        {{ $main->title }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Sub Navigation -->
        <div x-show="navOpen" x-cloak @mouseleave="navOpen = ''" class="relative font-plush">
            <div
                class="w-[1025px] min-h-[424px] bg-white absolute top-0 left-0 right-0 mx-auto flex justify-between border border-primary-light-200">

                @foreach ($active_seasonals ?? [] as $seasonal)
                    <div x-show="navOpen === 'seasonal_{{ $seasonal->id }}'"
                        class="px-[32px] py-[24px] grid grid-cols-3 h-fit w-full text-primary-dark-950">
                        <div class="col-span-3 flex flex-col justify-center opacity-70 mt-10 ml-8">
                             <p class="text-[24px] font-plush">{{ $seasonal->name }} Collection</p>
                             <p class="text-[16px] mt-2 max-w-lg">{{ $seasonal->description ? Str::limit($seasonal->description, 150) : 'Discover our exclusive seasonal collection.' }}</p>
                        </div>
                    </div>

                    <div x-show="navOpen === 'seasonal_{{ $seasonal->id }}'"
                        class="p-[24px] border-l border-primary-light-200 w-[260px] flex flex-col justify-between text-primary-dark-950 bg-primary-light-50">
                        <div class="flex flex-col gap-[8px]">
                            <div class="w-[212px] h-[137px] overflow-hidden">
                                @if($seasonal->hover_image)
                                    <img src="{{ asset('storage/seasonal/' . $seasonal->hover_image) }}" alt="{{ $seasonal->name }}" class="w-full h-full object-cover">
                                @elseif($seasonal->banner_image)
                                    <img src="{{ asset('storage/seasonal/' . $seasonal->banner_image) }}" alt="{{ $seasonal->name }}" class="w-full h-full object-cover">
                                @else
                                    <img src="{{ asset('assets/homepage/images/nav.svg') }}" alt="{{ $seasonal->name }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <p class="text-[24px]">{{ $seasonal->name }}</p>
                            <p class="text-[16px]">{{ $seasonal->hover_description ? Str::limit($seasonal->hover_description, 80) : Str::limit($seasonal->description, 80) }}</p>
                        </div>
                        <a href="{{ route('frontend.seasonal', $seasonal->slug) }}"
                            class="flex w-full justify-between items-center gap-2 text-primary-dark-950 hover:text-secondary-terra-800">
                            <span class="text-[16px]">Shop {{ $seasonal->name }}</span>
                            <iconify-icon icon="material-symbols-light:keyboard-arrow-right"
                                class="text-[16px]"></iconify-icon>
                        </a>
                    </div>
                @endforeach

                @foreach ($navigation as $main)
                    <div x-show="navOpen === '{{ $main->id }}'"
                        class="px-[32px] py-[24px] grid grid-cols-3 h-fit w-full text-primary-dark-950">
                        @foreach ($main->categories as $cat)
                            <div class="py-[16px] pr-[16px] h-fit">
                                <a href="{{ route('frontend.product', [$main->slug, $cat->slug]) }}">{{ $cat->title }}</a>
                            </div>
                        @endforeach
                    </div>

                    <div x-show="navOpen === '{{ $main->id }}'"
                        class="p-[24px] border-l border-primary-light-200 w-[260px] flex flex-col justify-between text-primary-dark-950 bg-primary-light-50">
                        <div class="flex flex-col gap-[8px]">
                            <div class="w-[212px] h-[137px] overflow-hidden">
                                <img src="{{ $main->picture ? asset('storage/main_category/' . $main->picture) : asset('assets/homepage/images/nav.svg') }}"
                                    alt="{{ $main->title }}">
                            </div>
                            <p class="text-[24px]">{{ $main->title }}</p>
                            <p class="text-[16px]">{{ $main->sub_title }}</p>
                        </div>
                        <a href="{{ route('frontend.productByMainCategory', $main->slug) }}"
                            class="flex w-full justify-between items-center gap-2 text-primary-dark-950 hover:text-secondary-terra-800">
                            <span class="text-[16px]">{{ $main->link_txt }}</span>
                            <iconify-icon icon="material-symbols-light:keyboard-arrow-right"
                                class="text-[16px]"></iconify-icon>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Sidebar + Overlay For Account -->
    <div x-show="sidebarAccount" x-cloak x-transition.opacity class="fixed inset-0 z-40 lg:hidden"
        style="display: none;">
        <!-- Overlay -->
        <div @click="sidebarAccount = false" class="absolute inset-0 bg-black bg-opacity-50"></div>

        <!-- Sidebar -->

        <div x-show="sidebarAccount" x-transition:enter="transition duration-300 transform"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition duration-300 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="absolute pt-[74px] top-0 right-0 w-[324px] h-full bg-white shadow-lg z-50">

            @if (Auth::guard('customer')->check())
                <div class="flex flex-col justify-between h-full">
                    <div>
                        <div class="py-[16px] px-[24px] border-b border-primary-light-50 text-primary-dark-950">
                            <div class="flex">
                                <div>
                                    <p class="text-[24px] font-semibold">
                                        {{ Auth::guard('customer')->user()->first_name }}
                                        {{ Auth::guard('customer')->user()->last_name }}</p>
                                    <p class="text-[#888] text-[16px]">{{ Auth::guard('customer')->user()->email }}
                                    </p>
                                </div>
                                <iconify-icon icon="material-symbols-light:close"
                                    class="text-[24px] ml-auto cursor-pointer"
                                    @click="sidebarAccount = false"></iconify-icon>
                            </div>
                        </div>

                        <!-- Nav Items -->
                        <nav class="px-[24px] py-[14px]">
                            <ul class="flex flex-col gap-[14px]">
                                <li
                                    class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                    <iconify-icon icon="material-symbols-light:home-storage-outline-rounded"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="#" class=" font-plush text-[16px]">My
                                        Account</a>
                                </li>
                                <li
                                    class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                    <iconify-icon icon="material-symbols-light:article-person-outline-rounded"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="#" class="font-plush text-[16px]">Profile</a>
                                </li>
                                <li
                                    class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                    <iconify-icon icon="material-symbols-light:map-pin-review-outline"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="#" class=" font-plush text-[16px]">Address</a>
                                </li>
                                <li
                                    class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                    <iconify-icon icon="material-symbols-light:lock-open-outline"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="#" class="font-plush text-[16px]">Password</a>
                                </li>
                                <li
                                    class="flex items-center gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                    <iconify-icon icon="material-symbols-light:receipt-long-outline"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="#" class="font-plush text-[16px]">Order
                                        History</a>
                                </li>
                                <li
                                    class="flex items-center cursor-pointer gap-[8px] text-primary-dark-950 hover:text-secondary-terra-800">
                                    <iconify-icon icon="material-symbols-light:favorite-outline"
                                        class="text-[20px]"></iconify-icon>
                                    <a href="{{ route('frontend.wishlist') }}"
                                        class="font-plush text-[16px]">Wishlist</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class=" p-[16px] text-[12px] flex flex-col gap-[8px] w-full">
                        <p class=" text-[#D1D1D1]">Copyright © 2022 SECO .All Right Reserved.</p>
                        <nav class="flex gap-[8px]">
                            <a href="#" class="text-primary-dark-950  hover:text-secondary-terra-800">Terms
                                of
                                Service</a>
                            <a href="#" class="text-primary-dark-950 hover:text-secondary-terra-800">Privacy
                                Policy</a>
                            <a href="#"
                                class="text-primary-dark-950 hover:text-secondary-terra-800">Accessibility</a>
                        </nav>
                    </div>
                </div>
            @else
                <div x-show="sidebarAccount" class=" p-[10px] ">
                    <div class="flex flex-col items-center justify-center">
                        <p class=" text-primary-dark-950 text-[20px] font-bold leading-[1.2]">Let's pick up
                            where you left off.</p>
                        <p class=" text-primary-dark-950 text-[16px] text-center leading-[1.2] mt-[8px]">
                            Please
                            sign in to continue, or create an account to save your progress.</p>
                    </div>
                    <div class="flex flex-col gap-[12px] mt-[20px]">
                        <button onclick="window.location.href='{{ route('loginForm.customer') }}'"
                            class="text-white bg-primary-dark-950 hover:bg-primary-dark-800 transition-colors duration-200 py-[12px] px-[16px] rounded">Sign
                            In</button>
                        <button onclick="window.location.href='{{ route('registerForm.customer') }}'"
                            class="border border-primary-dark-950 text-primary-dark-950 transition-colors duration-200 py-[12px] px-[16px] rounded">Create
                            Account</button>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Sidebar + Overlay For Menu -->
    <div x-show="sidebarMenu" x-cloak x-transition.opacity class="fixed inset-0 z-40 lg:hidden"
        style="display: none;">
        <!-- Overlay -->
        <div @click="sidebarMenu = false" class="absolute inset-0 bg-black bg-opacity-50"></div>

        <!-- Sidebar -->
        <div x-show="sidebarMenu" x-transition:enter="transition duration-300 transform"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition duration-300 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="absolute pt-[74px] top-0 right-0 w-[324px] h-full bg-white shadow-lg z-50">

            <div class="flex flex-col justify-between h-full pt-[16px] px-[12px]">
                <div class=" font-plush max-h-[520px] overflow-scroll">
                    <div class=" border-b border-primary-light-100 px-[16px] py-[16px] pb-[16px]">
                        <div x-data="{ open: false }" class="max-w-xl mx-auto bg-white rounded">
                            <!-- Header -->
                            <a href="{{ route("frontend.newest") }}" class="w-full text-left flex justify-between items-center text-secondary-terra-600">
                                <span class=" text-[16px]">New</span>
                                <iconify-icon :icon="open ? 'material-symbols:remove' : 'material-symbols:add'"
                                    class="text-xl text-primary-light-100" />
                            </a>

                        </div>
                    </div>
                    @foreach ($navigation as $main)
                        <div class=" border-b border-primary-light-100 px-[16px] py-[16px] pb-[16px]">
                            <div x-data="{ open: false }" class="max-w-xl mx-auto bg-white rounded">
                                <!-- Header -->
                                <button @click="open = !open"
                                    class="w-full text-left flex justify-between items-center text-primary-dark-950">
                                    <span class=" text-[16px]">{{ $main->title }}</span>
                                    <iconify-icon :icon="open ? 'material-symbols:remove' : 'material-symbols:add'"
                                        class="text-xl text-primary-light-100" />
                                </button>

                                <div x-show="open" x-transition
                                    class="px-[16px] text-[14px] border-l border-primary-light-50">
                                    <nav>
                                        <ul>

                                            @foreach ($main->categories as $row)
                                                <li class="py-2">
                                                    <a href="{{ route('frontend.product', [$main->slug, $row->slug]) }}"
                                                        class="text-primary-dark-950 hover:text-secondary-terra-800">{{ $row->title }}</a>
                                                </li>
                                            @endforeach

                                        </ul>
                                    </nav>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
                <div class=" p-[16px] text-[12px] flex flex-col gap-[8px] w-full">
                    <div
                        class="relative w-full flex items-center justify-between border border-primary-light-100 rounded-[2px] px-[24px] py-[14px]">
                        <div class="flex text-[16px] gap-[4px] text-primary-light-500">
                            <iconify-icon icon="material-symbols-light:favorite-rounded"
                                class="text-[20px]"></iconify-icon>
                            <a href="{{ route('frontend.wishlist') }}">Wishlist</a>
                        </div>
                        <iconify-icon icon="material-symbols-light:keyboard-arrow-right"
                            class="text-[24px] cursor-pointer"></iconify-icon>

                    </div>
                    <p class=" text-[#D1D1D1]">Copyright © 2022 SECO .All Right Reserved.</p>
                    <nav class="flex gap-[8px]">
                        <a href="#" class="text-primary-dark-950  hover:text-secondary-terra-800">Terms
                            of
                            Service</a>
                        <a href="#" class="text-primary-dark-950 hover:text-secondary-terra-800">Privacy
                            Policy</a>
                        <a href="#"
                            class="text-primary-dark-950 hover:text-secondary-terra-800">Accessibility</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <style>
        input[type="search"]::-webkit-search-cancel-button {
            -webkit-appearance: none;
            appearance: none;
        }
    </style>
</header>
