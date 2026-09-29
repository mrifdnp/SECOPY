@extends('layouts.master')
@section('title') Neraca Saldo @endsection
@section('content')
    @component('components.breadcrumb')
    @slot('li_1') Systems @endslot
    @slot('title') Neraca Saldo @endslot
    @endcomponent


    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @include('backend.includes.flash')
                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body indoit-form">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="row">
                                <form action="{{ route('neraca-saldo.getData_excel') }}" method="post" class="w-100">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="perTanggal" class="form-label">Per Tanggal</label>
                                                <input type="date" class="form-control" id="perTanggal" name="perTanggal"
                                                    value="{{ $perTanggal }}">
                                            </div>
                                        </div>
                                        <!--end col-->
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="" class="control-label">Kelompok Usaha</label>
                                                <select class="form-control js-example-basic-single select2"
                                                    name="business_group[]" id="choices-multiple-remove-button" multiple>
                                                    @foreach($businessGroups as $business)
                                                        <option value="{{$business->id}}">{{$business->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="" class="form-label" style="width: 100%;">&nbsp;</label>
                                                <button class="btn btn-primary" type="button" id="btnTampil"><i
                                                        class="las la-file-alt"></i> Preview Neraca Saldo</button>
                                                <button class="btn btn-success" type="submit"><i
                                                        class="las la-file-alt"></i> Export Excel</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @include('backend.includes.flash')
                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body indoit-form table-responsive">

                            @php
                                $dataRender = json_decode($data)->data;
                            @endphp
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label"
                                    style="text-align: center;font-size:18px;font-weight:bold;" id="labelTitle">
                                    {{ $dataRender[0] }}
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label"
                                    style="text-align: center;font-size:14px;font-weight:bold;" id="labelSubTitle">
                                    {{ $dataRender[1] }}
                                </label>
                                <hr>
                            </div>
                            <div class="form-group" id="bodyData">
                                {!! $dataRender[2] !!}
                            </div>
                        </div>
                        <span class="indoit-box-helper"></span>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->


@endsection


@section('css')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('script')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>

    <script>

        $(document).ready(function () {
            $('.forSelectDua').select2();
        });

        $(document).ready(function () {
            $("#btnTampil").on('click', function () {
                let perTanggal = $("#perTanggal").val();
                let groups = $("#choices-multiple-remove-button").val();
                $.ajax({
                    url: "{{route('neraca-saldo.getData')}}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        perTanggal: perTanggal,
                        business_group: groups
                    },
                    cache: false,
                    dataType: 'json',
                    success: function (dataResult) {
                        $("#labelTitle").empty();
                        $("#labelTitle").append(dataResult.data[0]);
                        $("#labelSubTitle").empty();
                        $("#labelSubTitle").append(dataResult.data[1]);
                        $("#bodyData").empty();
                        $("#bodyData").append(dataResult.data[2]);
                    }
                });
            });
        });

        $('form').on('submit', function () {
            let groups = $("#choices-multiple-remove-button").val();
            $(this).find('input[name="business_group[]"]').remove();
            groups.forEach(id => {
                $(this).append(`<input type="hidden" name="business_group[]" value="${id}">`);
            });
        });

    </script>
@endsection