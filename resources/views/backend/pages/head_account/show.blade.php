@extends('layouts.master')
@section('title') Detail {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Detail {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <div class="box">
                    <div class="box-body indoit-form">
                        <table class="table table-bordered">
                            <tr>
                                <td style="width: 200px"><b>Nama Akun</b></td>
                                <td>{{ $head_account->name }}</td>
                            </tr>
                            <tr>
                                <td style="width: 200px"><b>Kelompok Akun</b></td>
                                <td>{{ $head_account->nature }}</td>
                            </tr>
                            <tr>
                                <td style="width: 200px"><b>Tipe Akun</b></td>
                                <td>{{ $head_account->type }}</td>
                            </tr>
                            <tr>
                                <td style="width: 200px"><b>Jenis Beban</b></td>
                                <td>{{ $head_account->expense_type }}</td>
                            </tr>
                        </table>  
                    </div>
                    <span class="indoit-box-helper"></span>		
                    <div class="indoit-box-action">
                        <div class="col-sm-offset-2">
                            <div class="indoit-box-action-btn">                                    
                                <a href="{{ url()->previous() }}" class="btn btn-soft-secondary">Back to List</a>                                
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
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
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
@endsection
