@extends('layouts.master')
@section('title')
    Edit {{ $titleHeader->name }}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Edit {{ $titleHeader->name }}
        @endslot
    @endcomponent


    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @include('backend.includes.flash')
                    <form id="dt_form" action="{{ route('hero.update', $hero) }}" class="form-horizontal" method="post">
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
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Title</label>
                                    <div class="col-sm-12"><input value="{{ old('title', $hero->title) }}" type="text"
                                            class="form-control" name="title" id="title" placeholder=""
                                            size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Subtitle</label>
                                    <div class="col-sm-12"><input value="{{ old('subtitle', $hero->subtitle) }}" type="text"
                                            class="form-control" name="subtitle" id="subtitle" placeholder=""
                                            size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Link txt</label>
                                    <div class="col-sm-12"><input value="{{ old('link_txt', $hero->link_txt) }}"
                                            type="text" class="form-control" name="link_txt" id="link_txt"
                                            placeholder="" size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Link url</label>
                                    <div class="col-sm-12"><input value="{{ old('link_url', $hero->link_url) }}"
                                            type="text" class="form-control" name="link_url" id="link_url"
                                            placeholder="" size="50">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-sm-12 control-label"></label>
                                    <div class="col-lg-6 col-md-8 col-sm-10">
                                        @if (is_null($hero->content))
                                            <img src="{{ asset('img/default-image.png') }}" alt=""
                                                class="img-detail"
                                                style="width: 200px;height: auto;padding: 10px;vertical-align: middle;border-style: none;"
                                                id="imgEntity-content">
                                        @else
                                            <img src="{{ asset('img/hero/' . $hero->content) }}" alt=""
                                                class="img-detail"
                                                style="width: 200px;height: auto;padding: 10px;vertical-align: middle;border-style: none;"
                                                id="imgEntity-content">
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Content</label>
                                    <div class="col-sm-12"><label class="label" for="inputImage">Choose file</label>
                                        <input type="file" class="form-control @error('content') is-invalid @enderror"
                                            name="content" id="inputImage-content">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Type</label>
                                    <div class="col-sm-12"><select class="form-control js-example-basic-single select2-type"
                                            name="type" required>
                                            <option value="1" @if ('1' == $hero->type) selected="" @endif>
                                                Gambar</option>
                                            <option value="2" @if ('2' == $hero->type) selected="" @endif>
                                                Video</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Status</label>
                                    <div class="col-sm-12">
                                        <div class="radio">
                                            <input type="radio" id="Aktif" name="status" value="1"
                                                @if ('1' == $hero->status) checked="checked" @endif />
                                            <label for="Aktif">Aktif</label>
                                            <input type="radio" id="Tidak Aktif" name="status" value="2"
                                                @if ('2' == $hero->status) checked="checked" @endif />
                                            <label for="Tidak Aktif">Tidak Aktif</label>
                                        </div>
                                    </div>
                                </div>

                                <br>
                            </div>
                            <span class="indoit-box-helper"></span>
                            <div class="indoit-box-action">
                                <div class="col-sm-offset-2">
                                    <div class="indoit-box-action-btn">
                                        <a href="{{ route('hero.index') }}" class="btn btn-soft-secondary">Back to List</a>
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
    <link
        href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/ckeditor5-classic-40.2.0/ckeditor.js') }}"></script>

    <script>
        function getURL_content(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imgEntity-content').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $('#inputImage-content').on('change', function(e) {
            getURL_content(this);
            var fileName = e.target.files[0].name;
            $(this).next('.custom-file-label').html(fileName);
        });
        $(".select2-type").select2();
    </script>
@endsection
