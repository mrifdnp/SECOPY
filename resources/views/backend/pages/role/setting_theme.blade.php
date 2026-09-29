@extends('layouts.master')
@section('title') Theme @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Theme @endslot
@endcomponent
<script>
    const changeThemeRoute = "{{ route('theme.changeLayout') }}";
    const csrfToken = "{{ csrf_token() }}";
    const roleName = "{{ $role->name }}";
</script>
@php
    $themeData = $currentTheme;
    $layoutKey = 'data-layout';
    $topbarKey = 'data-topbar';
    $sidebarKey = 'data-sidebar';
    $sidebarSize = 'data-sidebar-size';
    $sidebarImage = 'data-sidebar-image';
    $sidebarPreloader = 'data-preloader';
@endphp
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                @include('backend.includes.flash')
                    <div class="box">
                    <div class="row">
                        <div class="col-12 col-lg-5">
                        <div class="offcanvas-body p-0">
                            <div data-simplebar class="h-100">
                                <div class="p-4">
                                    <h6 class="mb-0 fw-semibold text-uppercase">Layout</h6>
                                    <p class="text-muted">Choose your layout</p>

                                    <div class="row gy-3">
                                        <div class="col-3">
                                            <div class="form-check card-radio">
                                                @if (isset($themeData->$layoutKey))
                                                    <input id="customizer-layout01" name="data-layout" type="radio" value="vertical" class="form-check-input" @if ($themeData->$layoutKey == 'vertical')
                                                        checked
                                                    @endif>
                                                @else
                                                    <input id="customizer-layout01" name="data-layout" type="radio" value="vertical" class="form-check-input">
                                                @endif

                                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout01">
                                                    <span class="d-flex gap-1 h-100">
                                                        <span class="flex-shrink-0">
                                                            <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                            </span>
                                                        </span>
                                                        <span class="flex-grow-1">
                                                            <span class="d-flex h-100 flex-column">
                                                                <span class="bg-light d-block p-1"></span>
                                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                            <h5 class="fs-13 text-center mt-2">Vertical</h5>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-check card-radio">
                                                @if (isset($themeData->$layoutKey))
                                                    <input id="customizer-layout02" name="data-layout" type="radio" value="horizontal" class="form-check-input" @if ($themeData->$layoutKey == 'horizontal')
                                                        checked
                                                    @endif>
                                                @else
                                                    <input id="customizer-layout02" name="data-layout" type="radio" value="horizontal" class="form-check-input">
                                                @endif
                                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout02">
                                                    <span class="d-flex h-100 flex-column gap-1">
                                                        <span class="bg-light d-flex p-1 gap-1 align-items-center">
                                                            <span class="d-block p-1 bg-soft-primary rounded me-1"></span>
                                                            <span class="d-block p-1 pb-0 px-2 bg-soft-primary ms-auto"></span>
                                                            <span class="d-block p-1 pb-0 px-2 bg-soft-primary"></span>
                                                        </span>
                                                        <span class="bg-light d-block p-1"></span>
                                                        <span class="bg-light d-block p-1 mt-auto"></span>
                                                    </span>
                                                </label>
                                            </div>
                                            <h5 class="fs-13 text-center mt-2">Horizontal</h5>
                                        </div>


                                        <div class="col-3">
                                            <div class="form-check card-radio">
                                                <input id="customizer-layout04" name="data-layout" type="radio" value="semibox" class="form-check-input">
                                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout04">
                                                    <span class="d-flex gap-1 h-100">
                                                        <span class="flex-shrink-0 p-1">
                                                            <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                            </span>
                                                        </span>
                                                        <span class="flex-grow-1">
                                                            <span class="d-flex h-100 flex-column pt-1 pe-2">
                                                                <span class="bg-light d-block p-1"></span>
                                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                            <h5 class="fs-13 text-center mt-2">Semi Box</h5>
                                        </div>
                                        <!-- end col -->
                                    </div>

                                    <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Color Scheme</h6>
                                    <p class="text-muted">Choose Light or Dark Scheme.</p>

                                    <div class="colorscheme-cardradio">
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-check card-radio">
                                                    <input class="form-check-input" type="radio" name="data-layout-mode" id="layout-mode-light" value="light">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="layout-mode-light">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Light</h5>
                                            </div>

                                            <div class="col-3">
                                                <div class="form-check card-radio dark">
                                                    <input class="form-check-input" type="radio" name="data-layout-mode" id="layout-mode-dark" value="dark">
                                                    <label class="form-check-label p-0 avatar-md w-100 bg-dark" for="layout-mode-dark">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-soft-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-soft-light d-block p-1"></span>
                                                                    <span class="bg-soft-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Dark</h5>
                                            </div>
                                        </div>
                                    </div>

                                     <div id="sidebar-visibility">
                                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Sidebar Visibility</h6>
                                        <p class="text-muted">Choose show or Hidden sidebar.</p>

                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-check card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar-visibility" id="sidebar-visibility-show" value="show">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-visibility-show">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0 p-1">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column pt-1 pe-2">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Show</h5>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-check card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar-visibility" id="sidebar-visibility-hidden" value="hidden">
                                                    <label class="form-check-label p-0 avatar-md w-100 px-2" for="sidebar-visibility-hidden">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column pt-1 px-2">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Hidden</h5>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="layout-width">
                                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Layout Width</h6>
                                        <p class="text-muted">Choose Fluid or Boxed layout.</p>

                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-check card-radio">
                                                    <input class="form-check-input" type="radio" name="data-layout-width" id="layout-width-fluid" value="fluid">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="layout-width-fluid">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Fluid</h5>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-check card-radio">
                                                    <input class="form-check-input" type="radio" name="data-layout-width" id="layout-width-boxed" value="boxed">
                                                    <label class="form-check-label p-0 avatar-md w-100 px-2" for="layout-width-boxed">
                                                        <span class="d-flex gap-1 h-100 border-start border-end">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Boxed</h5>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="layout-position">
                                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Layout Position</h6>
                                        <p class="text-muted">Choose Fixed or Scrollable Layout Position.</p>

                                        <div class="btn-group radio" role="group">
                                            <input type="radio" class="btn-check" name="data-layout-position" id="layout-position-fixed" value="fixed">
                                            <label class="btn btn-light  w-sm" for="layout-position-fixed">Fixed</label>

                                            <input type="radio" class="btn-check" name="data-layout-position" id="layout-position-scrollable" value="scrollable">
                                            <label class="btn btn-light  w-sm ms-0" for="layout-position-scrollable">Scrollable</label>
                                        </div>
                                    </div>
                                    <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Topbar Color</h6>
                                    <p class="text-muted">Choose Light or Dark Topbar Color.</p>

                                    <div class="row">
                                        <div class="col-3">
                                            <div class="form-check card-radio">
                                                <input class="form-check-input" type="radio" name="data-topbar" id="topbar-color-light" value="light">
                                                <label class="form-check-label p-0 avatar-md w-100" for="topbar-color-light">
                                                    <span class="d-flex gap-1 h-100">
                                                        <span class="flex-shrink-0">
                                                            <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                            </span>
                                                        </span>
                                                        <span class="flex-grow-1">
                                                            <span class="d-flex h-100 flex-column">
                                                                <span class="bg-light d-block p-1"></span>
                                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                            <h5 class="fs-13 text-center mt-2">Light</h5>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-check card-radio">
                                                <input class="form-check-input" type="radio" name="data-topbar" id="topbar-color-dark" value="dark">
                                                <label class="form-check-label p-0 avatar-md w-100" for="topbar-color-dark">
                                                    <span class="d-flex gap-1 h-100">
                                                        <span class="flex-shrink-0">
                                                            <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                            </span>
                                                        </span>
                                                        <span class="flex-grow-1">
                                                            <span class="d-flex h-100 flex-column">
                                                                <span class="bg-primary d-block p-1"></span>
                                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                            <h5 class="fs-13 text-center mt-2">Dark</h5>
                                        </div>
                                    </div>

                                    <div id="sidebar-size">
                                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Sidebar Size</h6>
                                        <p class="text-muted">Choose a size of Sidebar.</p>

                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-default" value="lg">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-default">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Default</h5>
                                            </div>

                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-compact" value="md">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-compact">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Compact</h5>
                                            </div>

                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-small" value="sm">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-small">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1">
                                                                    <span class="d-block p-1 bg-soft-primary mb-2"></span>
                                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Small (Icon View)</h5>
                                            </div>

                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-small-hover" value="sm-hover">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-small-hover">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1">
                                                                    <span class="d-block p-1 bg-soft-primary mb-2"></span>
                                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Small Hover View</h5>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="sidebar-view">
                                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Sidebar View</h6>
                                        <p class="text-muted">Choose Default or Detached Sidebar view.</p>

                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-layout-style" id="sidebar-view-default" value="default">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-view-default">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Default</h5>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-layout-style" id="sidebar-view-detached" value="detached">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-view-detached">
                                                        <span class="d-flex h-100 flex-column">
                                                            <span class="bg-light d-flex p-1 gap-1 align-items-center px-2">
                                                                <span class="d-block p-1 bg-soft-primary rounded me-1"></span>
                                                                <span class="d-block p-1 pb-0 px-2 bg-soft-primary ms-auto"></span>
                                                                <span class="d-block p-1 pb-0 px-2 bg-soft-primary"></span>
                                                            </span>
                                                            <span class="d-flex gap-1 h-100 p-1 px-2">
                                                                <span class="flex-shrink-0">
                                                                    <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                        <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                        <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                        <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    </span>
                                                                </span>
                                                            </span>
                                                            <span class="bg-light d-block p-1 mt-auto px-2"></span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Detached</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="sidebar-color">
                                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Sidebar Color</h6>
                                        <p class="text-muted">Choose a color of Sidebar.</p>

                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio" data-bs-toggle="collapse" data-bs-target="#collapseBgGradient.show">
                                                    <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-light" value="light">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-light">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-white border-end d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Light</h5>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio" data-bs-toggle="collapse" data-bs-target="#collapseBgGradient.show">
                                                    <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-dark" value="dark">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-dark">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-primary d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Dark</h5>
                                            </div>
                                            <div class="col-3">
                                                <button class="btn btn-link avatar-md w-100 p-0 overflow-hidden border collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBgGradient" aria-expanded="false" aria-controls="collapseBgGradient">
                                                    <span class="d-flex gap-1 h-100">
                                                        <span class="flex-shrink-0">
                                                            <span class="bg-vertical-gradient d-flex h-100 flex-column gap-1 p-1">
                                                                <span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                                <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                            </span>
                                                        </span>
                                                        <span class="flex-grow-1">
                                                            <span class="d-flex h-100 flex-column">
                                                                <span class="bg-light d-block p-1"></span>
                                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </button>
                                                <h5 class="fs-13 text-center mt-2">Gradient</h5>
                                            </div>
                                        </div>
                                        <!-- end row -->

                                        <div class="collapse" id="collapseBgGradient">
                                            <div class="d-flex gap-2 flex-wrap img-switch p-2 px-3 bg-light rounded">

                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-gradient" value="gradient">
                                                    <label class="form-check-label p-0 avatar-xs rounded-circle" for="sidebar-color-gradient">
                                                        <span class="avatar-title rounded-circle bg-vertical-gradient"></span>
                                                    </label>
                                                </div>
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-gradient-2" value="gradient-2">
                                                    <label class="form-check-label p-0 avatar-xs rounded-circle" for="sidebar-color-gradient-2">
                                                        <span class="avatar-title rounded-circle bg-vertical-gradient-2"></span>
                                                    </label>
                                                </div>
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-gradient-3" value="gradient-3">
                                                    <label class="form-check-label p-0 avatar-xs rounded-circle" for="sidebar-color-gradient-3">
                                                        <span class="avatar-title rounded-circle bg-vertical-gradient-3"></span>
                                                    </label>
                                                </div>
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-gradient-4" value="gradient-4">
                                                    <label class="form-check-label p-0 avatar-xs rounded-circle" for="sidebar-color-gradient-4">
                                                        <span class="avatar-title rounded-circle bg-vertical-gradient-4"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="sidebar-img">
                                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Sidebar Images</h6>
                                        <p class="text-muted">Choose a image of Sidebar.</p>

                                        <div class="d-flex gap-2 flex-wrap img-switch">
                                            <div class="form-check sidebar-setting card-radio">
                                                <input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-none" value="none">
                                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-none">
                                                    <span class="avatar-md w-auto bg-light d-flex align-items-center justify-content-center">
                                                        <i class="ri-close-fill fs-20"></i>
                                                    </span>
                                                </label>
                                            </div>

                                            <div class="form-check sidebar-setting card-radio">
                                                <input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-01" value="img-1">
                                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-01">
                                                    <img src="{{URL::asset('build/images/sidebar/img-1.jpg')}}" alt="" class="avatar-md w-auto object-cover">
                                                </label>
                                            </div>

                                            <div class="form-check sidebar-setting card-radio">
                                                <input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-02" value="img-2">
                                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-02">
                                                    <img src="{{URL::asset('build/images/sidebar/img-2.jpg')}}" alt="" class="avatar-md w-auto object-cover">
                                                </label>
                                            </div>
                                            <div class="form-check sidebar-setting card-radio">
                                                <input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-03" value="img-3">
                                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-03">
                                                    <img src="{{URL::asset('build/images/sidebar/img-3.jpg')}}" alt="" class="avatar-md w-auto object-cover">
                                                </label>
                                            </div>
                                            <div class="form-check sidebar-setting card-radio">
                                                <input class="form-check-input" type="radio" name="data-sidebar-image" id="sidebarimg-04" value="img-4">
                                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-04">
                                                    <img src="{{URL::asset('build/images/sidebar/img-4.jpg')}}" alt="" class="avatar-md w-auto object-cover">
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="preloader-menu">
                                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Preloader</h6>
                                        <p class="text-muted">Choose a preloader.</p>

                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-preloader" id="preloader-view-custom" value="enable">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="preloader-view-custom">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                        <!-- <div id="preloader"> -->
                                                        <div id="status" class="d-flex align-items-center justify-content-center">
                                                            <div class="spinner-border text-primary avatar-xxs m-auto" role="status">
                                                                <span class="visually-hidden">Loading...</span>
                                                            </div>
                                                        </div>
                                                        <!-- </div> -->
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Enable</h5>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-check sidebar-setting card-radio">
                                                    <input class="form-check-input" type="radio" name="data-preloader" id="preloader-view-none" value="disable">
                                                    <label class="form-check-label p-0 avatar-md w-100" for="preloader-view-none">
                                                        <span class="d-flex gap-1 h-100">
                                                            <span class="flex-shrink-0">
                                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                                    <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                                </span>
                                                            </span>
                                                            <span class="flex-grow-1">
                                                                <span class="d-flex h-100 flex-column">
                                                                    <span class="bg-light d-block p-1"></span>
                                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                                </span>
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <h5 class="fs-13 text-center mt-2">Disable</h5>
                                            </div>
                                        </div>

                                        <div class="offcanvas-footer border-top p-3 text-center">
                                            <div class="row">
                                                <div class="col-6">
                                                    <a href="{{ route('role.resetTheme',$role->id) }}" class="btn btn-soft-secondary w-100" id="reset-layout">Reset</a>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <!-- end preloader-menu -->

                                </div>
                            </div>
                        </div>
                        </div>
                    </div>



                    </div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>
<!--end row-->


@endsection


@section('css')
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js')}}"></script>
<script>

    function check(param){
        $('#groups-'+param)[0].checked = true;
    }

    function check2(param){
        if($("#groups_default-"+param).is(":checked")){
            $('#groups-'+param)[0].checked = true;
        }
    }
</script>
@endsection
