<footer class="mt-[90px] border-t border-primary-light-200">
    @if (!Auth::guard('customer')->check())
        <div
            class="grid grid-cols-1 md:grid-cols-2 px-[16px] py-[24px] md:px-[50px] lg:px-[120px] py-[56px] bg-primary-light-50">
            <div class="flex flex-col">
                <p
                    class="font-plush font-light text-[36px] md:text-[60px] text-primary-dark-950 leading-[1.2] font-light">
                    Sign Up For Exclusive Update
                </p>
                <p class="text-[20px] mt-[8px] font-plush text-primary-dark-700">
                    Get a first look at our latest creations, special offers, and
                    events. Stay inspired with SECO's curated collection of timeless,
                    handcrafted pieces.
                </p>
            </div>
            <div class="flex justify-center items-center mt-[24px] md:mt-0">
                <form action="{{ route('loginForm.customer') }}" method="GET" class=" pl-[16px] lg:pl-[140px]">
                    <div class="mb-6">
                        <label for="email" class="block text-primary-dark-950 font-medium text-base mb-2">
                            Email Address
                        </label>
                        <div class="flex gap-3 mt-[-6px]">
                            <input type="email" id="email" name="email" required value="{{ request('email') }}"
                                class="flex-1 border-[2px] px-[16px] py-[8px] rounded-[2px] border-primary-light-200 font-cabinet bg-primary-light-50 focus:border-[2px] focus:border-primary-light-500 focus:outline-none" />
                            <button type="submit"
                                class="bg-primary-dark-950 hover:bg-primary-dark-700 text-white px-[12px] py-[10px] rounded-[2px] font-cabinet text-[16px] flex items-center justify-center">
                                Submit
                            </button>
                        </div>
                    </div>

                    <p class="text-neutral-dark-500 text-sm mt-[-19px]">
                        By signing up, you'll receive SECO updates, promotions, and
                        exclusive offers. You also agree to SECO's
                        <a href="#" onclick="return false;"
                            class="text-neutral-dark-500 underline hover:text-gray-800 transition-colors">Privacy
                            Policy</a>.
                        Unsubscribe anytime.
                    </p>
                </form>
            </div>

        </div>
    @endif
    <div class="px-[16px] my-[24px] md:px-[120px] md:my-[64px]">
        <div class="grid grid-cols-1 md:grid-cols-[0.82fr_1.4fr] gap-[24px] md:gap-[80px]">
            <div>
                <div class="flex gap-[22px] md:gap-[8px]">
                    <img src="{{ asset('assets/homepage/logo-footer.svg') }}" alt="" />
                    <p class="text-[16px] font-plush text-primary-dark-950">
                        Embodies a philosophy that marries enduring beauty with
                        responsible choices. It is more than just aesthetics—it is a
                        commitment to emphasizes selecting high-quality, eco-friendly
                        materials that stand the test of time.
                    </p>
                </div>
                <hr class="border-t border-primary-light-200 w-full my-[16px]" />
                <div>
                    <p class="text-[14px] font-plush text-primary-dark-950 font-bold">
                        {{ $address_title }}
                    </p>
                    <p class="text-[14px] font-plush leading-[1.2]">
                        {{ $address }}
                    </p>
                    <p class="text-[14px] font-plush">{{ $address_phone }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 md:gap-[20px] lg:gap-[72px]">
                <div class="font-cabinet text-primary-dark-950">
                    <h3 class="text-[16px] font-plush text-primary-dark-950 font-bold mb-[12px]">
                        Help and Info
                    </h3>
                    <div class="flex flex-col gap-[8px]">
                        @foreach ($help as $item)
                            <a
                                href="{{ route('frontend.bottomPages', ['id' => $item->id, 'title' => Str::slug($item->title)]) }}">
                                <p class="text-[14px]">{{ $item->title }}</p>
                            </a>
                        @endforeach
                        <a href="{{ route('frontend.blogPage') }}">
                            <p class="text-[14px]">Blog</p>
                        </a>
                    </div>
                </div>
                <div class="font-cabinet text-primary-dark-950">
                    <h3 class="text-[16px] font-plush text-primary-dark-950 font-bold mb-[12px]">
                        More about us
                    </h3>
                    <div class="flex flex-col gap-[8px]">
                        @foreach ($about as $item)
                            <a
                                href="{{ route('frontend.bottomPages', ['id' => $item->id, 'title' => Str::slug($item->title)]) }}">
                                <p class="text-[14px]">{{ $item->title }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="font-cabinet text-primary-dark-950 mt-[24px] md:mt-0">
                    <h3 class="text-[16px] font-plush text-primary-dark-950 font-bold mb-[12px]">
                        Design services
                    </h3>
                    <div class="flex flex-col gap-[8px]">
                        @foreach ($service as $item)
                            <a
                                href="{{ route('frontend.bottomPages', ['id' => $item->id, 'title' => Str::slug($item->title)]) }}">
                                <p class="text-[14px]">{{ $item->title }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="font-cabinet text-primary-dark-950 mt-[24px] md:mt-0">
                    <div class="flex gap-2 md:justify-end">
                        <a href="{{ $youtube_url }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ asset('assets/homepage/icons/youtube.svg') }}" alt="" />
                        </a>
                        <a href="{{ $instagram_url }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ asset('assets/homepage/icons/instagram.svg') }}" alt="" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="md:flex md:justify-between">
        <div class="px-[16px] md:px-[120px] md:py-[16px]">
            <p class="text-[12px] font-cabinet text-primary-dark-950">
                Copyright © 2023 SECO. All rights reserved.
            </p>
        </div>
        <div class="px-[16px] py-[12px] md:px-[120px] md:py-[16px] flex gap-[24px]">
            @foreach ($footer as $item)
                <a href="{{ route('frontend.bottomPages', ['id' => $item->id, 'title' => Str::slug($item->title)]) }}">
                    <p class="text-[12px] font-cabinet text-primary-dark-950 underline">
                        {{ $item->title }}
                    </p>
                </a>
            @endforeach
        </div>
    </div>
</footer>
