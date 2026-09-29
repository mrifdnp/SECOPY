@extends('frontend.layouts.home')

@section('title', 'Blogs')

@section('content')
    <div class="md:px-0 max-w-[1420px] mx-auto">
        <div class="sticky top-0 z-20 bg-white px-[16px] pb-[7px] pd:mb-0">
            <div class="flex justify-between pt-[8px] md:pt-[12px] md:px-[50px] lg:px-[100px]">
                <div class="flex flex-col">
                    <h1 class="font-plush text-[24px] md:text-[36px] font-medium text-primary-dark-950">
                        Blogs
                    </h1>
                </div>
                <div class="hidden md:flex justify-end items-center gap-[16px]">
                    <div class="flex items-center gap-[16px]">
                        <span class="text-primary-dark-950 font-normal font-cabinet text-[18px]">Sort By:</span>
                        <div class="relative">
                            <form method="GET" action="{{ route('frontend.blogPage') }}">
                                <select name="filter"
                                    class="appearance-none bg-white font-cabinet text-[18px] border border-primary-light-200 rounded px-4 py-2 pr-8 text-primary-dark-950 focus:outline-none focus:ring-1 focus:ring-primary-light-500 focus:border-primary-light-500"
                                    onchange="this.form.submit()">
                                    <option value="featured" {{ request('filter') == 'featured' ? 'selected' : '' }}>
                                        Featured</option>
                                    <option value="newest" {{ request('filter') == 'newest' ? 'selected' : '' }}>Newest
                                    </option>
                                    <option value="oldest" {{ request('filter') == 'oldest' ? 'selected' : '' }}>Oldest
                                    </option>
                                </select>
                            </form>

                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-primary-dark-950" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-[8px]">
                        {{-- Previous Button --}}
                        @if ($blogs->onFirstPage())
                            <button class="p-3 text-primary-light-600 border border-primary-light-200 disabled:opacity-50"
                                disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                        @else
                            <a href="{{ $blogs->previousPageUrl() }}"
                                class="p-3 text-primary-light-600 border border-primary-light-200 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </a>
                        @endif

                        {{-- Current Page / Total Pages --}}
                        <div class="bg-white text-sm text-gray-700 text-center">
                            {{ $blogs->currentPage() }}/{{ $blogs->lastPage() }}
                        </div>

                        {{-- Next Button --}}
                        @if ($blogs->hasMorePages())
                            <a href="{{ $blogs->nextPageUrl() }}"
                                class="p-3 text-primary-light-600 border border-primary-light-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @else
                            <button class="p-3 text-primary-light-600 border border-primary-light-200 disabled:opacity-50"
                                disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        @endif
                    </div>

                </div>
            </div>
            <div class=" md:hidden flex w-full my-[16px]">
                <button class="text-primary-dark-700 w-11/12 border border-primary-light-500 rounded py-2 px-4">
                    Filter & Sort
                </button>
                <div class="ml-2 flex items-center justify-center w-[40px] h-[40px] border border-primary-light-500 ">
                    <img src="{{ asset('assets/product/icons/menu-icon.svg') }}" alt="">
                </div>
            </div>
        </div>
        <div class="px-[16px] md:px-[50px] lg:px-[120px] md:mb-[80px] mt-[20px]">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-[10px] lg:gap-[20px]">
                @foreach ($blogs as $blog)
                    <div
                        class=" relative border border-primary-light-200 w-full pt-[12px] md:pt-0 transition-all duration-300 ease-in-out overflow-hidden flex flex-col justify-between">

                        <!-- Bottom Content -->
                        <div
                            class="z-10 px-[8px] md:p-[14px] bg-white transition-all duration-300 ease-in-out flex flex-col justify-between h-full">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p
                                        class="font-cabinet text-[14px] md:text-[20px] font-bold leading-tight mb-[5px] line-clamp-2">
                                        {{ $blog->title }}
                                    </p>
                                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                        @if(isset($blog->created_at))
                                            <span>{{ $blog->created_at->format('d M Y') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-[12px] md:text-[14px] text-gray-600 line-clamp-3">
                                        {{ $blog->excerpt }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="overflow-hidden mt-[12px] transition-[height] duration-300 ease-in-out h-[40px]">
                                <a href="{{ route('frontend.blogDetail', $blog->id) }}" class="cursor-pointer">
                                    <button
                                        class="w-full bg-primary-light-500 py-[6px] flex justify-center items-center text-white text-[14px] font-light">
                                        Read More
                                    </button>
                                </a>
                            </div>

                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
