@extends('layouts.master')
@section('title') Add {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Add {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <form id="dt_form" action="{{route('general-journal.store')}}" class="form-horizontal" method="post">
                    @csrf
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
                                <label for="" class="col-sm-2 control-label">Rincian Transaksi*</label>
                                <div class="col-sm-12">
                                    <input value="{{ old('naration') }}" type="text" class="form-control" name="naration" id="naration" placeholder="Rincian Transaksi" size="50" required>
                                </div>
                            </div>
                            <br/>                                                
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Tanggal*</label>
                                <div class="col-sm-12">
                                    <input type="date" class="form-control" id="date" name="date" value="{{ $now }}" required>                                    
                                </div>
                            </div>
                            <br/>
                            <div class="form-group">
                                <div class="col-sm-12 table-responsive">
                                    <table class="table table-borderless table-striped"  style="min-width: 700px">
                                        <thead>
                                            <tr>
                                                <td><b>Akun</b></td>
                                                <td><b>Debit</b></td>
                                                <td><b>Kredit</b></td>
                                            </tr>
                                        </thead>               
                                        <tbody id="bodyData">
                                            <tr>
                                                <td>
                                                    <select class="form-control js-example-basic-single forSelectDua" name="head_accounts_id[]" required>
                                                        <option value="" selected>-------------------------</option>
                                                        @php
                                                            $list = ['assets','liability','equity','expense','revenue','hpp'];
                                                        @endphp
                                                        @foreach ($dataAccount as $key => $head_account) 
                                                            @if (in_array($key,$list))
                                                                <option value="{{ $key }}" disabled>{{ $head_account }}</option>
                                                            @else
                                                                <option value="{{ $key }}" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $head_account }}</option>
                                                            @endif                                                            
                                                        @endforeach
                                                    </select>        
                                                </td>    
                                                <td>
                                                    <input type="text" class="form-control debitData" name="debit[]" id="" value="">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control kreditData" name="kredit[]" id="" value="">
                                                </td>
                                            </tr>    
                                        </tbody>   
                                        <tfoot>
                                            <tr>
                                                <td colspan="3">
                                                    <button type="button" class="btn btn-primary" id="btnAddRow"><i class="ri-add-circle-fill"></i> Tambah</button>    
                                                </td>    
                                            </tr>  
                                            <tr>
                                                <td style="text-align: right;font-weight:bold;">
                                                    Total:
                                                </td>    
                                                <td>
                                                    <input type="text" class="form-control" name="total_debit" id="total_debit" value="" disabled>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="total_kredit" id="total_kredit" value="" disabled>
                                                </td>
                                            </tr> 
                                            <tr>
                                                <td colspan="3" style="text-align: justify;font-weight:bold;">
                                                    <span id="balanced">Rp.0</span>
                                                </td>    
                                            </tr> 
                                        </tfoot>              
                                    </table>
                                </div>
                            </div>                                                
                            <br/>
                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('general-journal.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
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

    let totalKredit = 0;
    let totalDebit = 0;
    let kredit = 0;
    let debit = 0;
    let balanced = 0;
    let debitAll = 0;
    let kreditAll = 0;
    $(document).ready(function() {
        $(document).on('focus', '.debitData', function() {
            $(this).mask('000.000.000', {reverse: true});
        });

        $(document).on('focus', '.kreditData', function() {
            $(this).mask('000.000.000', {reverse: true});
        });

        $('.forSelectDua').select2();

        $(document).on('keyup', '.debitData', function(){            
            debitAll = 0;
            balanced = 0;          
              
            $('.debitData').each(function() {
                let debitValue = $(this).val();
                if (debitValue !== '') {
                    debitValue = parseInt(debitValue.split('.').join("")) || 0;
                    debitAll += debitValue;
                }
            });

            if (debitAll != kreditAll) {
                balanced = Math.abs(debitAll - kreditAll);
                balanced = -balanced;
            } else {
                balanced = 0;
            }

            $('#total_debit').val('Rp'+debitAll.toLocaleString("id-ID"));
            $('#total_debit').mask('000.000.000', {reverse: true});
            $('#balanced').text('Rp'+balanced.toLocaleString("id-ID"));         
        });

        $(document).on('keyup', '.kreditData', function(){            
            kreditAll = 0;
            balanced = 0;          
              
            $('.kreditData').each(function() {
                let kreditValue = $(this).val();
                if (kreditValue !== '') {
                    kreditValue = parseInt(kreditValue.split('.').join("")) || 0;
                    kreditAll += kreditValue;
                }
            });

            if (debitAll != kreditAll) {
                balanced = Math.abs(debitAll - kreditAll);
                balanced = -balanced;
            } else {
                balanced = 0;
            }

            $('#total_kredit').val('Rp'+kreditAll.toLocaleString("id-ID"));
            $('#total_kredit').mask('000.000.000', {reverse: true});
            $('#balanced').text('Rp'+balanced.toLocaleString("id-ID"));         
        });

    });

    $(function () {
        $('#datetimepicker-date').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss'
        });
    });    

    $(document).ready(function() {   
        $("#btnAddRow").on('click', function() {
            $.ajax({
                url:"{{route('general-journal.addRow')}}",
                type: "GET",
                data:{ 
                    _token:'{{ csrf_token() }}',                    
                },
                cache: false,
                dataType: 'json',
                success: function(dataResult){
                    $("#bodyData").append(dataResult.data);                          
                    $('.js-example-basic-single').last().select2();          
                }
            });
        });
    });

</script>
@endsection
