@extends('layouts.master')
@section('title') System  Log @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') System  Log @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <form action="#" method="post">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div class="card">
            <iframe style="width: 100%;height:1000px;" src="{{ url('telescope') }}"></iframe>
        </div>
        </form>
    </div>
</div>

@endsection

@section('script')

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

@endsection
