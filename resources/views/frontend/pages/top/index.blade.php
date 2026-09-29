@extends('frontend.layouts.home')

@section('title', $page['title'])

@section('content')
<div class="px-0 md:px-[120px] md:mt-[32px]">
    <div class="flex flex-col justify-center px-[16px] pb-0 md:px-[120px] md:pb-[16px] w-full">
        <div>
            <div class=" w-full mb-[16px]">
                <div class=" flex items-center md:pl-[20px] pt-[20px] md:pl-[16px]">
                    <p class="text-neutral-dark-400">Home</p>
                    <iconify-icon icon="mdi:chevron-right" class="mx-[8px] text-neutral-dark-400"></iconify-icon>
                    <p class="text-primary-dark-950">{{ $page['title'] }}</p>
                </div>
            </div>
            <div class="flex justify-center flex-col md:flex-row md:w-[800px]">
                <div class="hidden md:w-[230px] md:flex flex-col items-center p-[16px] ">
                    <p class=" text-left w-full font-plush text-[16px] font-medium mb-[16px]">Help and Info</p>
                    <nav class="w-full">
                        <ul class="flex flex-col gap-[12px] font-cabinet">
                            @foreach($nav_page as $item)
                            @php
                                $isActive = $page['id'] === $item['id'];
                            @endphp
                            <li>
                                <a href="{{ route('frontend.topPages', ['id' => $item['id'], 'title' => Str::slug($item['title'])]) }}"
                                    class="{{ $isActive 
                                                        ? 'text-primary-light-500 underline font-plush text-[12px] font-bold hover:text-secondary-terra-800' 
                                                        : 'text-primary-dark-950 font-plush text-[12px] font-medium hover:text-secondary-terra-800' }}">
                                    {{ $item['title'] }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </nav>
                </div>

                <div class="w-full text-primary-dark-950 md:border-x md:border-[#F6F6F6]">
                    <div class=" md:px-[24px] md:py-[16px]">
                        <h1 class="hidden md:block font-plush text-[24px] font-medium mb-[16px]">{{ $page['title'] }}</h1>
                        <div x-data="{ open: false }" class="md:hidden block w-full mb-[24px]">
                            <div @click="open = ! open" class="flex justify-between items-center">
                                <h1 class=" text-[30px]">{{ $page['title'] }}</h1>
                                <div class="bg-primary-light-50 py-[6px] px-[8px] flex justify-center items-center rounded-[2px]">
                                    <iconify-icon x-show="!open" icon="material-symbols-light:keyboard-arrow-down-rounded"
                                        class="text-[30px] cursor-pointer"></iconify-icon>
                                    <iconify-icon x-show="open" icon="material-symbols-light:keyboard-arrow-up-rounded"
                                        class="text-[30px] cursor-pointer"></iconify-icon>
                                </div>
                            </div>

                            <div x-show="open">
                                <nav class="w-full mt-[16px] border-b border-primary-light-300 pb-[16px]">
                                    <ul class="flex flex-col gap-[12px] font-cabinet">
                                        @foreach($nav_page as $item)
                                        @php
                                        $isActive = $page['id'] === $item['id'];
                                        @endphp
                                        <li>
                                            <a href="{{ route('frontend.topPages', ['id' => $item['id'], 'title' => Str::slug($item['title'])]) }}"
                                                class="{{ $isActive 
                                                        ? 'text-primary-light-500 underline font-plush text-[16px] hover:text-secondary-terra-800' 
                                                        : 'text-primary-dark-950 font-plush text-[16px] hover:text-secondary-terra-800' }}">
                                                {{ $item['title'] }}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="flex flex-col gap-[12px]">
                            {!! $page['content'] !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection