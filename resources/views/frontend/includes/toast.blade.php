@php
    $type = $type ?? 'error';
    $showVariable = $showVariable ?? 'showToast';
    $messageVariable = $messageVariable ?? 'toastMessage';
    $duration = $duration ?? 5000;
@endphp

<div x-show="{{ $showVariable }}" 
     x-cloak
     x-init="$watch('{{ $showVariable }}', value => {
         if (value) {
             setTimeout(() => {{ $showVariable }} = false, {{ $duration }})
         }
     })"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-full"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-full"
     class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 max-w-sm w-full mx-4">
    
    @if($type === 'error')
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-red-800">Error</p>
                <p class="mt-1 text-sm text-red-700" x-text="{{ $messageVariable }}"></p>
            </div>
            <div class="ml-4 flex-shrink-0">
                <button @click="{{ $showVariable }} = false" 
                        class="inline-flex text-red-400 hover:text-red-600 focus:outline-none focus:text-red-600 transition">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @elseif($type === 'success')
    <div class="bg-[#E8F0F7] border-l-4 border-[#8CA5CE] p-4 rounded-lg shadow-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-[#8CA5CE]" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-[#6F86BC]">Success</p>
                <p class="mt-1 text-sm text-[#6F86BC]" x-text="{{ $messageVariable }}"></p>
            </div>
            <div class="ml-4 flex-shrink-0">
                <button @click="{{ $showVariable }} = false" 
                        class="inline-flex text-[#8CA5CE] hover:text-[#6F86BC] focus:outline-none focus:text-[#6F86BC] transition">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>