@extends('layouts.master')
@section('title') List {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Data {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <form action="#" method="post">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div class="card">
            {{-- <div class="card-header d-sm-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"> <i class="ri-pages-line"></i> Basic Datatables</h5>
                @slot('li_1') Tables @endslot
                @slot('title')Datatables @endslot
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Master</a></li>
                        <li class="breadcrumb-item active">Datatables</li>
                    </ol>
                </div>
            </div> --}}
            <div class="card-body">
                @include('backend.includes.flash')
                <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox" type="checkbox" value=""></th>
                            <th class="th_no" data-hide="phone">No</th>
                            <th data-class="expand">Nama Akun</th>
                            <th data-class="expand">Kelompok</th>
                            <th data-class="expand">Tipe</th>
                            <th data-class="expand">Kas</th>
                            <th data-class="expand">Aktifitas</th>
                            <th data-class="expand">Expense Type</th>
                            <th data-class="expand">Saldo Normal</th>
                            <th data-class="expand">POS</th>
                            @if (\RuleHelper::UserHasPermission('update', false))
                            <th data-hide="phone">Kelompok Bisnis</th>
                            <th data-hide="phone">Dibuat pada</th>
                            @endif
                            
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
        </form>
    </div>
    <!--end col-->
</div>
<!--end row-->
<div id="myModal"class="modal fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-delete" style="border: none !important;">

        </div>
    </div>
</div>	

{{-- modal filter --}}
<div id="myFilterModal"class="modal fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-filter">
            <div class="modal-header p-3 bg-primary bg-gradient">                
                <h5 class="text-white"><i class="ri-filter-line"> </i> Filter Data</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>    
            </div>
            <form id="form_filter" name="form_filter" action="" method="post">
                @csrf
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal">
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Nama Akun</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="name" id="name" placeholder="" size="40">
                            </div>
                        </div>
                        <br/>
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Kelompok Akun</label>
                            <div class="col-sm-12">
                                <select class="form-control select2" name="nature" id="nature" style="width: 100%;">
                                    <option value="" selected="selected">Silakan Pilih Kelompok Akun</option>
                                    <option value="assets">Assets</option>
                                    <option value="liability">Liability</option>
                                    <option value="equity">Equity</option>
                                    <option value="expense">Expense</option>
                                    <option value="revenue">Revenue</option>
                                    <option value="hpp">HPP</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Tipe Akun</label>
                            <div class="col-sm-12">
                                <select class="form-control select2" name="type" id="type" style="width: 100%;">
                                    <option value="" selected="selected">Silakan Pilih Tipe Akun</option>
                                    <option value="asset lancar">Asset Lancar</option>
                                    <option value="asset tetap">Asset Tetap</option>
                                    <option value="bukan asset">Bukan Asset</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                    </div><!-- /.box-body -->
                </div>

                <div class="modal-footer bg-opacity-75">        
                    <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
                    <button type="button" id="btn-reset" onClick="this.form.reset()" class="btn btn-warning pull-left text-light"><i class="las la-undo-alt"></i> Reset</button>
                    <button type="button" id="btn-filter" class="btn btn-info pull-left text-light"><i class=" las la-share"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>	
{{-- end modal filter --}}

{{-- Modal import --}}

<div id="importModal" class="modal fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-import">
            <div class="modal-header p-3 bg-primary bg-gradient">                
                <h5 class="text-white"><i class="modalLabel"> </i> Import Akun</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>    
            </div>
            <div class="modal-body">
                    <div class="form-steps">
                        <div class="step-arrow-nav mb-4">
                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="steparrow-gen-info-tab" data-bs-toggle="pill" data-bs-target="#steparrow-gen-info" type="button" role="tab" aria-controls="steparrow-gen-info" aria-selected="true">Template</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-description-info-tab" data-bs-toggle="pill" data-bs-target="#steparrow-description-info" type="button" role="tab" aria-controls="steparrow-description-info" aria-selected="false">Peraturan</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-experience-tab" data-bs-toggle="pill" data-bs-target="#pills-experience" type="button" role="tab" aria-controls="pills-experience" aria-selected="false">Upload</button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="steparrow-gen-info" role="tabpanel" aria-labelledby="steparrow-gen-info-tab">
                                <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px;">
                                    <i class="ri-download-2-fill" style="font-size: 4rem;"></i>
                                    <form action="{{ route('import.download_template', ['id' => $import_template->id]) }}" method="POST" class="mt-2" id="form_download_template">
                                        @csrf
                                        <button class="btn btn-primary" {{ is_null($import_template) ? 'disabled' : '' }}>Download Template</button>
                                    </form>
                                </div>
                            </div>
                            <!-- end tab pane -->

                            <div class="tab-pane fade" id="steparrow-description-info" role="tabpanel" aria-labelledby="steparrow-description-info-tab">
                                @if (!is_null($import_template))
                                    <p>Setiap kolom mempunyai peraturannya tersendiri, ikuti aturan untuk setiap kolom untuk memastikan data bisa terimport</p>
                                    <table class="table table-bordered">
                                        @foreach ($import_template->rules as $rule)
                                            <tr>
                                                <th>{{ $rule['column'] }}</th>
                                                <td>{{ $rule['rule'] }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </div>
                            <!-- end tab pane -->

                            <div class="tab-pane fade" id="pills-experience" role="tabpanel">
                                <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px; width:100%;">
                                    <i class="ri-upload-2-fill" style="font-size: 4rem;"></i>
                                    <form action="{{ route('import.head_account') }}" method="POST" class="mt-2 w-100" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="template_id" value="{{ $import_template->id }}">
                                        <input type="file" name="file" id="file" class="form-control mb-2 w-100" accept=".csv">
                                        <button class="btn btn-primary w-100">Upload</button>
                                    </form>
                                </div>
                            </div>
                            <!-- end tab pane -->
                        </div>
                        <!-- end tab content -->
                    </div>
                </div>
        </div>
    </div>
</div>

{{-- End Modal import --}}

{{-- Modal Create --}}

<div id="createModal"class="modal modal-lg fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-create">
            <div class="modal-header p-3 bg-primary bg-gradient">                
                <h5 class="text-white"><i class="ri-add-circle-line"></i> Create Data</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>    
            </div>
            <form id="form_create" name="form_create" action="{{ route('head-account.store') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal row g-3">
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Nama Akun</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="name" id="name" placeholder="" size="40">
                            </div>
                        </div>
                        <br/>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Kelompok Akun</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" name="nature" id="nature" style="width: 100%;">
                                    <option value="" selected="selected">Silakan Pilih Kelompok Akun</option>
                                    <option value="assets">Assets</option>
                                    <option value="liability">Liability</option>
                                    <option value="equity">Equity</option>
                                    <option value="expense">Expense</option>
                                    <option value="revenue">Revenue</option>
                                    <option value="hpp">HPP</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Activity</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" name="aktifitas">
                                    <option value="aktivitas operasi">Aktivitas Operasi</option>
                                    <option value="aktivitas investasi">Aktivitas Investasi</option>
                                    <option value="aktivitas pendanaan">Aktivitas Pendanaan</option>
                                    <option value="">Bukan Activitas</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Tipe Akun</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" name="type" id="type" style="width: 100%;">
                                    <option value="" selected="selected">Silakan Pilih Tipe Akun</option>
                                    <option value="asset lancar">Asset Lancar</option>
                                    <option value="asset tetap">Asset Tetap</option>
                                    <option value="bukan asset">Bukan Asset</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Saldo Normal</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" name="saldo_normal">
                                    <option value="debit">Debit</option>
                                    <option value="credit">Kredit</option>
                                </select>
                            </div>
                        </div>                                             
                        <br>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Jenis Beban</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" name="expense_type" required>
                                    <option value="beban kas">Beban Kas</option>
                                    <option value="beban non kas">Beban Non Kas</option>
                                    <option value="beban barang">Beban Barang</option>
                                    <option value="bukan beban">Bukan Beban</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">POS</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" name="pos">
                                    <option value="pl">Laba Rugi</option>
                                    <option value="balancesheet">Neraca</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Kas</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" name="kas" required>
                                    <option value="kas">Setara Kas</option>
                                    <option value="non kas">Setara Non Kas</option>
                                </select>
                            </div>
                        </div>
                        <br/>   
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Kelompok Bisnis</label>
                                <div class="col-sm-12">
                                    <select class="form-control js-example-basic-single select2" name="business_type[]" id="choices-multiple-remove-button" multiple>
                                        @foreach ($accounting_business_groups as $k => $out)
                                            <option value="{{$k}}">{{$out}}</option>
                                        @endforeach
                                    </select>
                                </div>
                        </div>
                        <br>
                    </div>
                </div>

                <div class="modal-footer bg-opacity-75">        
                    <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- End Modal Create --}}

{{-- Modal Edit --}}

<div id="editModal"class="modal modal-lg fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-edit">
            <div class="modal-header p-3 bg-primary bg-gradient">                
                <h5 class="text-white"><i class="ri-edit-line"> </i> Edit Data</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>    
            </div>
            <form id="form_edit" name="form_edit" method="POST">
                @csrf
                @method('put')
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal row g-3">
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Nama Akun</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="name" id="name_edit" placeholder="" size="40">
                            </div>
                        </div>
                        <br/>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Kelompok Akun</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" name="nature" id="nature_edit" style="width: 100%;">
                                    <option value="" selected="selected">Silakan Pilih Kelompok Akun</option>
                                    <option value="assets">Assets</option>
                                    <option value="liability">Liability</option>
                                    <option value="equity">Equity</option>
                                    <option value="expense">Expense</option>
                                    <option value="revenue">Revenue</option>
                                    <option value="hpp">HPP</option>
                                </select>
                            </div>
                        </div>
                        <br/>   
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Activity</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2-expense_type select2" id="aktifitas_edit" name="aktifitas">
                                    <option value="aktivitas operasi">Aktivitas Operasi</option>
                                    <option value="aktivitas investasi">Aktivitas Investasi</option>
                                    <option value="aktivitas pendanaan">Aktivitas Pendanaan</option>
                                    <option value="">Bukan Activitas</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Tipe Akun</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" name="type" id="type_edit" style="width: 100%;">
                                    <option value="" selected="selected">Silakan Pilih Tipe Akun</option>
                                    <option value="asset lancar">Asset Lancar</option>
                                    <option value="asset tetap">Asset Tetap</option>
                                    <option value="bukan asset">Bukan Asset</option>
                                </select>
                            </div>
                        </div>
                        <br/> 
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Saldo Normal</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" id="sn_edit" name="saldo_normal">
                                    <option value="debit">Debit</option>
                                    <option value="credit">Kredit</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Jenis Beban</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2-expense_type select2" id="expense_type_edit" name="expense_type" required>
                                    <option value="beban kas">Beban Kas</option>
                                    <option value="beban non kas">Beban Non Kas</option>
                                    <option value="beban barang">Beban Barang</option>
                                    <option value="bukan beban">Bukan Beban</option>
                                </select>
                            </div>
                        </div>
                        <br/> 
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">POS</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2" id="pos_edit" name="pos">
                                    <option value="pl">Laba Rugi</option>
                                    <option value="balancesheet">Neraca</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Kas</label>
                            <div class="col-sm-12">
                                <select class="form-control js-example-basic-single select2-expense_type select2" id="kas_edit" name="kas" required>
                                    <option value="kas">Setara Kas</option>
                                    <option value="non kas">Setara Non Kas</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-6 control-label">Kelompok Bisnis</label>
                                <div class="col-sm-12">
                                    <select class="form-control js-example-basic-single select2" name="business_type[]" id="choices-multiple-remove-button" multiple>
                                        @foreach ($accounting_business_groups as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                        </div>
                        <br>
                    </div>
                </div>

                <div class="modal-footer bg-opacity-75">        
                    <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- End Modal Edit --}}

{{-- @include('layouts.modal_delete') --}}
@endsection


@section('css')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('script')

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.colVis.min.js"></script>
{{-- <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap.min.js')}}"></script> --}}
<script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>

<script>
      $(document).ready(function () {
        // Inisialisasi Select2 saat modal ditampilkan
        $('#myFilterModal').on('shown.bs.modal', function () {
            $('#nature').select2({
                placeholder: "Pilih Kelompok Akun",
                width: '100%', // Pastikan lebar dropdown sesuai
                dropdownParent: $('#myFilterModal') // Agar dropdown tampil dalam modal
            });
        });
    });

    // $(document).ready(function () {
    //     // Inisialisasi Select2 saat modal ditampilkan
    //     $('#myFilterModal').on('shown.bs.modal', function () {
    //         $('#type').select2({
    //             placeholder: "Pilih Kelompok type",
    //             width: '100%', // Pastikan lebar dropdown sesuai
    //             dropdownParent: $('#myFilterModal') // Agar dropdown tampil dalam modal
    //         });
    //     });
    // });

    $(document).on('click', '.btnEdit', function(e) {
        e.preventDefault();
        let id = $(this).data('id');

        $.ajax({
            url: '/head-account/' + id + '/edit', 
            type: 'GET',
            success: function(data) {
                $('#name_edit').val(data.name);
                $('#nature_edit').val(data.nature).trigger('change');
                $('#type_edit').val(data.type).trigger('change');
                $('#expense_type_edit').val(data.expense_type).trigger('change');
                $('#kas_edit').val(data.kas).trigger('change');
                $('#aktifitas_edit').val(data.aktifitas).trigger('change');
                $('#sn_edit').val(data.sn).trigger('change');
                $('#pos_edit').val(data.pos).trigger('change');
                if(data.business_type && Array.isArray(data.business_type)){
                $('#business_type_edit').val(data.business_type).trigger('change');
            } else {
                $('#business_type_edit').val([]).trigger('change');
            }
                $('#form_edit').attr('action', '/head-account/' + id);
                $('#editModal').modal('show');
            }
        });
    })



    $(".select2").select2();

    $('.modal').on('shown.bs.modal', function () {
        $(this).find('.js-example-basic-single').select2({
            dropdownParent: $(this)
        });
    });

    $(document).on('click','#btnDownloadTemplate',function(){
       event.preventDefault();
       let tabel = $(this).data('tabel');
       $.ajax({
           url:"{{route('get.head_accountsTemplate')}}",
           type: "GET",
           data: {
               _token: '{{ csrf_token() }}',
               tabel: tabel
           },
           cache: false,
           dataType: 'json',
           success: function(data){
               var url = "{{route('download.head_accountsTemplate')}}";
               window.location = url;
           }
       });
   });

document.addEventListener('DOMContentLoaded', function () {    
    let table = new DataTable('#dt_basic',
    {
        dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                processing: true,
                serverSide: true,
                autoWidth:false,
                scrollX:true,
                "lengthMenu": [[100, 250, 500,1000, -1], [100, 250, 500,1000, "All"]],
                order: [[ 2, "ASC" ]],
                language: {
                    "lengthMenu": "Display _MENU_ "
                },
                buttons: [     
                    @if(\RuleHelper::UserHasPermission('create',false))                                                           
                        {
                            text: '<i class="las la-plus-square text-success"></i> Create',
                            className: 'child-button',
                             action: function (e, dt, button, config){
                                $('#createModal').modal('show');
                            }        
                        },
                    @endif      
                    @if(\RuleHelper::UserHasPermission('delete',false))                  
                        {
                            text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                            className: 'child-button',
                            action: function ( e, dt, button, config ) {
                                e.preventDefault();
                                
                                var id_to_be_deleted = $("#dt_basic tbody input:checkbox:checked").map(function(){
                                return $(this).val();
                                }).get();
                                
                                $.ajax({
                                    type:"POST",
                                    url:"{{route('head-account.delete')}}",
                                    data:{
                                        id_to_be_deleted: id_to_be_deleted,
                                        delete_type: 'softdelete'
                                    },
                                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                    success:function(data){
                                        $('#myModal').modal('show');
                                        $('#modal-delete').html(data);
                                    }
                                });
                            }        
                        },    
                    @endif               
                        {
                            extend: 'collection',
                            text: '<i class="las la-file-excel"></i> Export',
                            className: 'child-button',
                            buttons: [
                                {
                                    extend: 'excel',
                                    text: 'excel',
                                    exportOptions: {
                                        columns :[ 1,2,3 ]
                                    }
                                }
                            ]
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="las la-eye"></i> Show',
                            className: 'child-button'
                        },                        
                        {
                            text: '<i class="las la-filter"></i> Filter',
                            className: 'child-button',
                            action: function ( e, dt, button, config ) {
                                    $('#myFilterModal').modal('show');
                            }        
                        },
                        {
                            text: '<i class="ri-upload-2-fill"></i> Import',
                            className: 'child-button',
                            action: function (e, dt, button, config){
                                $('#importModal').modal('show');
                            }
                        }
                    ],
                ajax: {
                    "url":  "{{route('head-account.get_datatable')}}",
                    "type": "get",
                    "data": function ( data ) {
                        data.name = $('#name').val();
                        data.nature = $('#nature option:selected').val();
                        data.type = $('#type option:selected').val();
                        i = data.start+1;
                    }
                },
                columns: [
                    { 
                        data: 'checkboxs',
                        name: 'checkboxs',
                        orderable: false,
                        searchable: false,
                        width:'20px'
                    },                    
                    {
                        data: 'number',
                        name: 'number',
                        width:'20px',
                        render: function() {
                            return i++;
                        }
                    },
                        {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'nature',
                        name: 'nature'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },   
                    {
                        data: 'kas',
                        name: 'kas'
                    },    
                    {
                        data: 'aktifitas',
                        name: 'aktifitas',
                        render: function(data, type, row) {
                            return data ? data : 'Bukan Aktivitas';
                        }
                    }, 
                    {
                        data: 'expense_type',
                        name: 'expense_type'
                    },      
                    {
                        data: 'sn',
                        name: 'sn',
                        render: function(data) {
                            if (data === 'credit') return 'Kredit';
                            if (data === 'debit') return 'Debit';
                            return data;
                        }
                    },      
                    {
                        data: 'pos',
                        name: 'pos',
                        render: function(data) {
                            if (data === 'pl') return 'Laba Rugi';
                            if (data === 'balancesheet') return 'Neraca';
                            return data;
                        }
                    },  
                    {
                        data: 'business_type',
                        name: 'business_type',
                        render: function(data){
                            if (!data) return '';
                            try {
                                let arr = JSON.parse(data);
                                if (Array.isArray(arr)) {
                                    return arr.join(', ');
                                } else {
                                    return arr; 
                                }
                            } catch (e) {
                                return data;
                            }
                        }
                    },
                    @if (\RuleHelper::UserHasPermission('update', false))
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    @endif         
                    
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width:'100px'
                    }
                ],
            
    });
    $('#btn-filter').click(function(){ //button filter event click
        table.ajax.reload();  //just reload table
    });
    $('#btn-reset').click(function () {
        // Reset form
        $('#form_filter')[0].reset(); 
        
        // Reset Select2
        $('#nature').val(null).trigger('change'); 
        $('#type').val(null).trigger('change'); 

        
        // Reload DataTable
        table.ajax.reload(); 
    });
    $('body').on('click', '#main_checkbox', function () {
        if($(this).is(':checked')) {
            $('.checkbox_item').prop('checked', true);
        }
        else{
            $('.checkbox_item').prop('checked', false);
        }
    });
    $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
        e.preventDefault();				
        var id_to_be_deleted =  $(this).attr("id");
        
        $.ajax({
            type:"POST",
            url:"{{route('head-account.delete')}}",
            data:{
                id_to_be_deleted: [id_to_be_deleted],
                delete_type: 'softdelete'
            },
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success:function(data){
                $('#myModal').modal('show');
                $('#modal-delete').html(data);
            }
        });
    });
    @if(\RuleHelper::UserHasPermission('trash',false))
        var a = '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-primary" tabindex="0" aria-controls="dt_basic" href="{{route('head-account.index')}}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{route('head-account.trash')}}" class="btn btn-info btn-light" style="margin-right:5px;" tabindex="0" ><span><i class="bx bx-recycle"></i> Recycle Bin ({{$head_accounts_count}}) </span> </a></div>';
        $(".btn-tbl" ).prepend(a);
    @endif
    table.buttons(1, null).container()
    .addClass('btn-group')
    .appendTo(table.table().container());
});
</script>


<!-- dashboard init -->
@endsection
