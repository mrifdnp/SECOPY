@extends('layouts.master')
@section('title') Edit {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Edit {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <form id="dt_form" action="{{route('head-account.update',$data->id)}}" class="form-horizontal" method="post">
                    @csrf
                    @method('put')
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
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Nama Akun</label>
                                <div class="col-sm-10">
                                    <input value="{{ old('name') ? old('name') : $data->name }}" type="text" class="form-control" name="name" id="name" placeholder="Nama Akun" size="50" required>
                                </div>
                            </div>
                            <br/>
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Kelompok Akun</label>
                                <div class="col-sm-10">
                                    <select class="form-control js-example-basic-single select2-nature" name="nature" required>
                                        <option value="assets" @if ($data->nature == 'assets')
                                            selected
                                        @endif>Assets</option>
                                        <option value="liability" @if ($data->nature == 'liability')
                                            selected
                                        @endif>Liability</option>
                                        <option value="equity" @if ($data->nature == 'equity')
                                            selected
                                        @endif>Equity</option>
                                        <option value="expense" @if ($data->nature == 'expense')
                                            selected
                                        @endif>Expense</option>
                                        <option value="revenue" @if ($data->nature == 'revenue')
                                            selected
                                        @endif>Revenue</option>
                                        <option value="hpp" @if ($data->nature == 'hpp')
                                            selected
                                        @endif>HPP</option>
                                    </select>
                                </div>
                            </div>
                            <br/>
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Tipe Akun</label>
                                <div class="col-sm-10">
                                    <select class="form-control js-example-basic-single select2-type" name="type" required>
                                        <option value="asset lancar" {{ $data->type == 'asset lancar' ? 'selected' : '' }}>Lancar</option>
                                        <option value="asset tetap" {{ $data->type == 'asset tetap' ? 'selected' : '' }}>Tetap</option>
                                        <option value="bukan asset" {{ $data->type == 'bukan asset' ? 'selected' : '' }}>Bukan Asset</option>
                                    </select>
                                </div>
                            </div>
                            <br/>
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Jenis Beban</label>
                                <div class="col-sm-10">
                                    <select class="form-control js-example-basic-single select2-expense_type" name="expense_type" required>
                                        <option value="beban kas" {{ $data->expense_type == 'beban kas' ? 'selected' : '' }}>Beban Kas</option>
                                        <option value="beban non kas" {{ $data->expense_type == 'beban non kas' ? 'selected' : '' }}>Beban Non Kas</option>
                                        <option value="beban barang" {{ $data->expense_type == 'beban barang' ? 'selected' : '' }}>Beban Barang</option>
                                        <option value="bukan beban" {{ $data->expense_type == 'bukan beban' ? 'selected' : '' }}>Bukan Beban</option>
                                    </select>
                                </div>
                            </div>  
                            <br/>
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Kas</label>
                                <div class="col-sm-10">
                                    <select class="form-control js-example-basic-single select2-expense_type" name="kas" required>
                                        <option value="kas" {{ $data->kas == 'kas' ? 'selected' : '' }}>Setara Kas</option>
                                        <option value="non kas" {{ $data->kas == 'non kas' ? 'selected' : '' }}>Setara Non Kas</option>
                                    </select>
                                </div>
                            </div>
                            <br/>   
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Activity</label>
                                <div class="col-sm-10">
                                    <select class="form-control js-example-basic-single select2-expense_type" name="aktifitas" >
                                        <option value="" {{ empty($data->aktifitas) ? 'selected' : '' }}>Bukan Aktivitas</option>
                                        <option value="aktivitas operasi" {{ $data->aktifitas == 'aktivitas operasi' ? 'selected' : '' }}>Aktivitas Operasi</option>
                                        <option value="aktivitas investasi" {{ $data->aktifitas == 'aktivitas investasi' ? 'selected' : '' }}>Aktivitas Investasi</option>
                                        <option value="aktivitas pendanaan" {{ $data->aktifitas == 'aktivitas pendanaan' ? 'selected' : '' }}>Aktivitas Pendanaan</option>
                                    </select>
                                </div>
                            </div>                                             
                            <br>                                                                                                             
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('head-account.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </div><!-- /.box-footer -->	                                
                        <div class="indoit-box-required">
                            <hr>
                            <span class="required">*</span>
                            Field Required
                        </div><!-- /.box-footer -->
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end col-->
</div>
<!--end row-->


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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>

<script>
    $(".select2-nature").select2();
    $(".select2-type").select2();
    $(".select2-expense_type").select2();    
</script>
@endsection
