@props([
    'id' => 'default-modal',
    'title' => 'Modal Title',
    'size' => 'md', // sm, md, lg, xl
    'color' => 'primary', // primary, secondary, dll.
    'withForm' => false, // true jika ingin membungkus body & footer dengan form
    'formAction' => '#',
    'formMethod' => 'POST',
    'formEnctype' => false,
])

<div id="{{ $id }}" class="modal fade modal-{{ $color }}">
    <div class="modal-dialog modal-{{ $size }}">
        <div class="modal-content">
            {{-- Header --}}
            <div class="modal-header p-3 bg-{{ $color }} bg-gradient d-flex align-items-center justify-content-between">
                <div class="w-100 d-flex align-items-center position-relative">
                    <h5 class="text-white m-0 d-flex align-items-center">
                        {{ $title }}
                    </h5>
                    <button type="button" class="btn-close text-white position-absolute end-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- Jika withForm = true, bungkus body dan footer dengan <form> --}}
            @if ($withForm)
                <form action="{{ $formAction }}" method="{{ $formMethod }}" {{ $formEnctype ? 'enctype=' . $formEnctype : '' }}>
                    @csrf
                    <div class="modal-body">
                        {{ $slot }}
                    </div>
                    <div class="modal-footer bg-opacity-75">
                        {{ $footer ?? '' }}
                    </div>
                </form>
            @else
                <div class="modal-body">
                    {{ $slot }}
                </div>
                <div class="modal-footer bg-opacity-75">
                    {{ $footer ?? '' }}
                </div>
            @endif
        </div>
    </div>
</div>
