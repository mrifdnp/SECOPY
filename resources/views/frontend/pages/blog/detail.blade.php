@extends('frontend.layouts.home')
@section('title', $blog->title )
@section('content')
    <div class="md:px-0 max-w-[1420px] mx-auto">
        <div class=" bg-white px-[16px] pb-[12px] border-b border-gray-100">
            <div class="flex justify-between pt-[12px] md:pt-[16px]">
                <div class="flex flex-col space-y-2 mt-[24px]">
                    <h1 class="font-plush text-[28px] md:text-[42px] lg:text-[48px] font-medium text-primary-dark-950 leading-tight">
                        {{ $blog->title }}
                    </h1>
                    <div class="text-sm text-gray-500 flex items-center space-x-4">
                        @if(isset($blog->created_at))
                            <span>{{ $blog->created_at->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 px-[16px] md:rid-cols-[2fr_1fr] gap-4">
            <article class="py-[32px] md:py-[48px]">
                <div class="prose prose-lg max-w-none 
                           prose-headings:text-primary-dark-950 
                           prose-headings:font-semibold
                           prose-p:text-gray-700 
                           prose-p:leading-relaxed
                           prose-p:mb-6
                           prose-a:text-primary-light-500
                           prose-a:no-underline
                           hover:prose-a:underline
                           prose-strong:text-primary-dark-950
                           prose-ul:space-y-2
                           prose-ol:space-y-2
                           prose-li:text-gray-700">
                    {!! $blog->content !!}
                </div>
            </article>
    
            @if($otherBlogs->count())
                <section class="pb-[80px] md:pb-[100px] mt-[20px]">
                    <div>
                        <div class="flex items-center space-x-4 mb-2">
                            <div class="w-1 h-8 bg-primary-light-500"></div>
                            <h2 class="text-[24px] md:text-[32px] font-semibold text-primary-dark-950">
                                Related Blog
                            </h2>
                        </div>
                    </div>
    
                    <div class=" gap-6 md:gap-8">
                        @foreach($otherBlogs as $item)
                            <article class="group bg-white border border-gray-200 rounded-xl overflow-hidden">
                                <div class="p-5 md:p-6">
                                    <h3 class="text-[18px] md:text-[20px] font-bold text-primary-dark-950 mb-3 leading-tight group-hover:text-primary-light-500 transition-colors duration-200">
                                        <a href="{{ route('frontend.blogDetail', $item->id) }}" class="stretched-link">
                                            {{ $item->title }}
                                        </a>
                                    </h3>
                                    
                                    <p class="text-sm md:text-base text-gray-600 mb-4 leading-relaxed line-clamp-3">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($item->content), 120) }}
                                    </p>
    
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                        @if(isset($item->created_at))
                                            <span>{{ $item->created_at->format('d M Y') }}</span>
                                        @endif
                                    </div>
    
                                    <div class="flex items-center text-primary-light-500 font-medium group-hover:text-primary-dark-950 transition-colors duration-200">
                                        <span class="mr-2">Read More</span>
                                        <svg class="w-4 h-4 transform transition-transform duration-200" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

    </div>

    
@endsection
@section('css')
    <style>
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .stretched-link::after {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 1;
            content: "";
        }
    </style>
@endsection