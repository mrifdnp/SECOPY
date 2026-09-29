<!doctype html >
@php
    $themeData = $themes;
    $layoutKey = 'data-layout';
    $topbarKey = 'data-topbar';
    $sidebarKey = 'data-sidebar';
    $sidebarSize = 'data-sidebar-size';
    $sidebarImage = 'data-sidebar-image';
    $sidebarPreloader = 'data-preloader';
    $sidebarLayoutMode = 'data-layout-mode';
    $sidebarLayoutWidth = 'data-layout-width';
    $sidebarLayoutStyle = 'data-layout-style';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
        data-layout="{{ isset($themeData->$layoutKey) ? $themeData->$layoutKey : 'horizontal' }}"
        data-topbar="{{ isset($themeData->$topbarKey) ? $themeData->$topbarKey : 'light' }}"
        data-layout-mode="{{ isset($themeData->$sidebarLayoutMode) ? $themeData->$sidebarLayoutMode : 'light' }}"
        data-sidebar="{{ isset($themeData->$sidebarKey) ? $themeData->$sidebarKey : 'light' }}"
        data-layout-width="{{ isset($themeData->$sidebarLayoutWidth) ? $themeData->$sidebarLayoutWidth : 'fluid' }}"
        data-sidebar-size="{{ isset($themeData->$sidebarSize) ? $themeData->$sidebarSize : 'lg' }}"
        data-sidebar-image="{{ isset($themeData->$sidebarImage) ? $themeData->$sidebarImage : 'none' }}"
        data-layout-style="{{ isset($themeData->$sidebarLayoutStyle) ? $themeData->$sidebarLayoutStyle : 'default' }}"
        data-preloader="{{ isset($themeData->$sidebarPreloader) ? $themeData->$sidebarPreloader : 'disable' }}"
        >

<head>
    <meta charset="utf-8" />
    <title>@yield('title') Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico')}}">
    @include('layouts.head-css')
    @yield('cssAdded')
</head>

@section('body')
    @include('layouts.body')
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                    @include('layouts.modal_change_store')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    @include('layouts.customizer')


    <!-- JAVASCRIPT -->

    <script>
        var base_url_js = '{{ URL::asset('') }}';
    </script>
    @include('layouts.vendor-scripts')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        $(document).on('click', '#btnStoreAktif', function (e) {
            e.preventDefault();
            $('#storeChangeForm').modal('show');
        });
    </script>
</body>

</html>
