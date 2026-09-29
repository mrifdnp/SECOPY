@extends('layouts.master')
@section('title') View {{ $titleHeader->name ?? 'Material' }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Data {{ $titleHeader->name ?? 'Material' }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <div class="card">            
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="20%">Title</th>
                        <td>{{ $material->title }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $material->created_at }}</td>
                    </tr>
                </table>
                <a href="{{ route('material.index') }}" class="btn btn-secondary mt-3">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
