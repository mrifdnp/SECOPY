@if(Session::has('flash_message'))
    <div class="alert alert-success"> <i class="icon fa fa-check"></i> {{ Session::get('flash_message') }}</div>
@endif
@if(Session::has('warning_message'))
    <div class="alert alert-warning"> <i class="icon fa fa-time"></i> {{ Session::get('warning_message') }}</div>
@endif
@if(Session::has('error_message'))
    <div class="alert alert-danger"> <i class="icon fa fa-time"></i> {{ Session::get('error_message') }}</div>
@endif