<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="28">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="28">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">

                @php
                    $store = session('store');
                    $activeStoreName = is_array($store) ? ($store['name'] ?? null)
                                        : (is_object($store) ? ($store->name ?? null) : null);
                @endphp
                <li class="menu-title"><span>STORE AKTIF</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#" role="button" id="btnStoreAktif">
                        <i class="ri-barcode-fill"></i>
                        <span>{{ $activeStoreName ?? 'Tidak ada store aktif' }}</span>
                        @if(!empty($activeStoreName))
                            <span class="badge badge-pill bg-primary"><i class="ri-exchange-fill"></i></span>
                        @endif
                    </a>
                </li>

                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                {!! $sidebar !!}
                @if ($isAllowed == 1)
                    <li class="menu-title"><span>Developer Only</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('change-log.index') }}" role="button" aria-expanded="false" aria-controls="sidebarPages">
                            <i class=" ri-barcode-fill"></i> <span>Change Log</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('generator_basic.index') }}" role="button" aria-expanded="false" aria-controls="sidebarPages">
                            <i class=" ri-braces-fill"></i> <span>Module Generator</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('generator-log.index') }}" role="button" aria-expanded="false" aria-controls="sidebarPages">
                            <i class=" ri-bookmark-3-fill"></i> <span>Module Generator Log</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('telescope.index') }}" role="button" aria-expanded="false" aria-controls="sidebarPages">
                            <i class=" ri-stack-fill"></i> <span>System Log</span>
                        </a>
                    </li>


                @endif
                <li class="menu-title"><span>Users</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('home') }}" role="button" aria-expanded="false" aria-controls="sidebarPages">
                        <i class=" ri-user-fill"></i> <span>Profile</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('logout') }}" role="button" aria-expanded="false" aria-controls="sidebarPages">
                        <i class="  ri-logout-box-fill"></i> <span>Logout</span>
                    </a>
                </li>                
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
