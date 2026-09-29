@extends('layouts.master')
@section('title')
    Welcome
@endsection
@section('content')
    <div class="profile-foreground position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg">
            <img src="{{ URL::asset('build/images/profile-bg.jpg') }}" alt="" class="profile-wid-img">
        </div>
    </div>
    <div class="pt-4 mb-4 mb-lg-0 pb-lg-4 profile-wrapper">
        <div class="row g-4">

            <div class="col">
                <div class="p-2">
                    <h3 class="text-white mb-1">{{ $greating }}, {{ $user->name }}.</h3>
                    <p class="text-white-75">{{ $greating_msg }}</p>
                    <div class="hstack text-white-50 gap-1">
                        <div>
                            <i class="ri-calendar-2-line me-1 text-white-75 fs-16 align-middle"></i>{{ $date_id }} <span id="datetime">{{ $date_timeserver }}</span>
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <!--end row-->
    </div>


    <div class="row">
        <div class="col-lg-12">
            <div>

                <!-- Tab panes -->
                <div class="tab-content pt-4 text-muted">
                    <div class="tab-pane active show" id="overview-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-xxl-3">

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Pilih Hak Akses</h5>
                                        <div class="d-flex flex-wrap gap-2 fs-15">
                                            @foreach ($userPrivilegesArrAll as $row)
                                                <a href="{{ route('welcome.changeRole', $row->id) }}" class="btn btn-sm btn-soft-primary position-relative">
                                                    {{ $row->name }}
                                                    @if ($row->id == session()->get('current_role')->id)
                                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success"><i class=" las la-check"></i></span>
                                                    @endif
                                                </a>
                                            @endforeach
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Info</h5>
                                        <div class="table-responsive">
                                            <table class="table table-borderless mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Full Name :</th>
                                                        <td class="text-muted">{{ $user->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">E-mail :</th>
                                                        <td class="text-muted">{{ $user->email }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Location :</th>
                                                        <td class="text-muted">Indonesia
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Joining Date</th>
                                                        <td class="text-muted">{{ $user->created_at }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Last Login</th>
                                                        <td class="text-muted">{{ $user->last_login_at }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row p-2">
                                            <a href="{{ route('profile.index') }}" class="btn btn-primary"><i class="ri-edit-box-line align-bottom"></i> Edit Profile</a>
                                        </div>

                                    </div><!-- end card body -->
                                </div><!-- end card -->

                                <!-- end card -->

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Access</h5>
                                        <div class="d-flex flex-wrap gap-2 fs-15">
                                            @foreach ($role_has_privileges as $row)
                                                <a href="{{ $row->url }}" class="badge badge-soft-primary">{{ $row->name }}</a>
                                            @endforeach
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->




                                <!--end card-->


                                <!--end card-->
                            </div>
                            <!--end col-->
                            <div class="col-xxl-9">
                                <!-- end card -->

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header align-items-center d-flex">
                                                <h4 class="card-title mb-0  me-2">Change Log</h4>

                                            </div>
                                            <div class="card-body">
                                                <div class="tab-content text-muted">
                                                    <div class="tab-pane active" id="today" role="tabpanel">
                                                        <div class="profile-timeline">
                                                            <div class="accordion accordion-flush" id="todayExample">
                                                                @foreach ($changeLogs as $key => $item)
                                                                    @if ($key == 0)
                                                                        <div class="accordion-item border-0">
                                                                            <div class="accordion-header" id="headingOne">
                                                                                <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#collapse{{ $key }}" a1ria-expanded="true">
                                                                                    <div class="d-flex">
                                                                                        @if ($item->type == 'Minor Update')
                                                                                            <div class="flex-shrink-0 avatar-xs">
                                                                                                <div class="avatar-title bg-light text-muted rounded-circle">
                                                                                                    <i class="ri-user-3-fill"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="flex-grow-1 ms-3">
                                                                                                <h6 class="fs-14 mb-1">
                                                                                                    {{ $item->type }}
                                                                                                </h6>
                                                                                                <small class="text-muted">
                                                                                                    <span class="text-secondary">{{ $item->version }}</span> pada {{ \Carbon\Carbon::parse($item->created_date)->format('d F Y H:i:s') }}
                                                                                                </small>
                                                                                            </div>
                                                                                        @elseif($item->type == 'Mayor Update')
                                                                                            <div class="flex-shrink-0 avatar-xs">
                                                                                                <div class="avatar-title bg-primary text-light rounded-circle">
                                                                                                    <i class="ri-user-3-fill"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="flex-grow-1 ms-3">
                                                                                                <h6 class="fs-14 mb-1">
                                                                                                    {{ $item->type }}
                                                                                                </h6>
                                                                                                <small class="text-muted">
                                                                                                    <span class="text-secondary">{{ $item->version }}</span> pada {{ \Carbon\Carbon::parse($item->created_date)->format('d F Y H:i:s') }}
                                                                                                </small>
                                                                                            </div>
                                                                                        @else
                                                                                            <div class="flex-shrink-0 avatar-xs">
                                                                                                <div class="avatar-title bg-success text-light rounded-circle">
                                                                                                    <i class="ri-user-3-fill"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="flex-grow-1 ms-3">
                                                                                                <h6 class="fs-14 mb-1">
                                                                                                    {{ $item->type }}
                                                                                                </h6>
                                                                                                <small class="text-muted">
                                                                                                    <span class="text-secondary">{{ $item->version }}</span> pada {{ \Carbon\Carbon::parse($item->created_date)->format('d F Y H:i:s') }}
                                                                                                </small>
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                </a>
                                                                            </div>
                                                                            <div id="collapse{{ $key }}" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                                                <div class="accordion-body ms-2 ps-5">
                                                                                    {!! $item->description !!}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div class="accordion-item border-0">
                                                                            <div class="accordion-header" id="headingOne">
                                                                                <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#collapse{{ $key }}" aria-expanded="true">
                                                                                    <div class="d-flex">
                                                                                        @if ($item->type == 'Minor Update')
                                                                                            <div class="flex-shrink-0 avatar-xs">
                                                                                                <div class="avatar-title bg-light text-muted rounded-circle">
                                                                                                    <i class="ri-user-3-fill"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="flex-grow-1 ms-3">
                                                                                                <h6 class="fs-14 mb-1">
                                                                                                    {{ $item->type }}
                                                                                                </h6>
                                                                                                <small class="text-muted">
                                                                                                    <span class="text-secondary">{{ $item->version }}</span> pada {{ \Carbon\Carbon::parse($item->created_date)->format('d F Y H:i:s') }}
                                                                                                </small>
                                                                                            </div>
                                                                                        @elseif($item->type == 'Mayor Update')
                                                                                            <div class="flex-shrink-0 avatar-xs">
                                                                                                <div class="avatar-title bg-primary text-light rounded-circle">
                                                                                                    <i class="ri-user-3-fill"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="flex-grow-1 ms-3">
                                                                                                <h6 class="fs-14 mb-1">
                                                                                                    {{ $item->type }}
                                                                                                </h6>
                                                                                                <small class="text-muted">
                                                                                                    <span class="text-secondary">{{ $item->version }}</span> pada {{ \Carbon\Carbon::parse($item->created_date)->format('d F Y H:i:s') }}
                                                                                                </small>
                                                                                            </div>
                                                                                        @else
                                                                                            <div class="flex-shrink-0 avatar-xs">
                                                                                                <div class="avatar-title bg-success text-light rounded-circle">
                                                                                                    <i class="ri-user-3-fill"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="flex-grow-1 ms-3">
                                                                                                <h6 class="fs-14 mb-1">
                                                                                                    {{ $item->type }}
                                                                                                </h6>
                                                                                                <small class="text-muted">
                                                                                                    <span class="text-secondary">{{ $item->version }}</span> pada {{ \Carbon\Carbon::parse($item->created_date)->format('d F Y H:i:s') }}
                                                                                                </small>
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                </a>
                                                                            </div>
                                                                            <div id="collapse{{ $key }}" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                                                <div class="accordion-body ms-2 ps-5">
                                                                                    {!! $item->description !!}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                                {{-- <div class="accordion-item border-0">
                                                                    <div class="accordion-header" id="headingOne">
                                                                        <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#collapse2" aria-expanded="true">
                                                                            <div class="d-flex">
                                                                                <div class="flex-shrink-0 avatar-xs">
                                                                                    <div class="avatar-title bg-primary text-light rounded-circle">
                                                                                        <i class="ri-user-3-fill"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="flex-grow-1 ms-3">
                                                                                    <h6 class="fs-14 mb-1">
                                                                                       Mayor Update
                                                                                    </h6>
                                                                                    <small class="text-muted"><span class="text-secondary">v.2.0.1</span> pada 28 Desember 2023 23:22:13</small>
                                                                                </div>
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                                        <div class="accordion-body ms-2 ps-5">
                                                                            - Update typo text <br/>
                                                                            - Perbaikan validasi
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="accordion-item border-0">
                                                                    <div class="accordion-header" id="headingOne">
                                                                        <a class="accordion-button p-2 shadow-none collapsed" data-bs-toggle="collapse" href="#collapse3" aria-expanded="false">
                                                                            <div class="d-flex">
                                                                                <div class="flex-shrink-0 avatar-xs">
                                                                                    <div class="avatar-title bg-light text-muted rounded-circle">
                                                                                        <i class="ri-user-3-fill"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="flex-grow-1 ms-3">
                                                                                    <h6 class="fs-14 mb-1">
                                                                                       Minor Update
                                                                                    </h6>
                                                                                    <small class="text-muted"><span class="text-secondary">v.1.1</span> pada 28 Desember 2023 23:22:13</small>
                                                                                </div>
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                    <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                                        <div class="accordion-body ms-2 ps-5">
                                                                            - Update typo text <br/>
                                                                            - Perbaikan validasi
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="accordion-item border-0">
                                                                    <div class="accordion-header" id="headingOne">
                                                                        <a class="accordion-button p-2 shadow-none collapsed" data-bs-toggle="collapse" href="#collapse4" aria-expanded="false">
                                                                            <div class="d-flex">
                                                                                <div class="flex-shrink-0 avatar-xs">
                                                                                    <div class="avatar-title bg-success  rounded-circle text-light">
                                                                                        <i class="ri-user-3-fill"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="flex-grow-1 ms-3">
                                                                                    <h6 class="fs-14 mb-1">
                                                                                       Release
                                                                                    </h6>
                                                                                    <small class="text-muted"><span class="text-secondary">v.1.0</span> pada 28 Desember 2023 23:22:13</small>
                                                                                </div>
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                    <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                                        <div class="accordion-body ms-2 ps-5">
                                                                            - Update typo text <br/>
                                                                            - Perbaikan validasi
                                                                        </div>
                                                                    </div>
                                                                </div> --}}

                                                            </div>
                                                            <!--end accordion-->
                                                        </div>
                                                    </div>


                                                </div>
                                            </div><!-- end card body -->
                                        </div><!-- end card -->
                                    </div><!-- end col -->
                                </div><!-- end row -->

                                <!-- end card -->

                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>

                </div>
                <!--end tab-content-->
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
        function check(param) {
            $('#groups-' + param)[0].checked = true;
        }

        function check2(param) {
            if ($("#groups_default-" + param).is(":checked")) {
                $('#groups-' + param)[0].checked = true;
            }
        }

        var serverTime = {{ $datenow }}; //this would come from the server obviously
        console.log(serverTime);
        var localTime = +Date.now();
        console.log(localTime);
        var timeDiff = serverTime - localTime;
        console.log(timeDiff);



        // create a function to update the date and time
        function updateDateTime() {

            var realtime = +Date.now() + timeDiff;
            var date = new Date(realtime);
            // hours part from the timestamp
            var hours = date.getHours();

            if (hours < 10) {
                hours = '0' + hours.toString();
            }

            // minutes part from the timestamp
            var minutes = date.getMinutes();

            if (minutes < 10) {
                minutes = '0' + minutes.toString();
            }

            // seconds part from the timestamp
            var seconds = date.getSeconds();

            if (seconds < 10) {
                seconds = '0' + seconds.toString();
            }

            var formattedTime = hours + ':' + minutes + ':' + seconds;

            // update the `textContent` property of the `span` element with the `id` of `datetime`
            document.querySelector('#datetime').textContent = formattedTime;
        }



        // call the `updateDateTime` function every second
        setInterval(updateDateTime, 1000);
    </script>
@endsection
