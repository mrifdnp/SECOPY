@extends('layouts.master')

@section('title')
    List {{ $titleHeader->name }}
@endsection

@section('cssAdded')
    <style>
        .tags-input-wrapper {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            cursor: text;
            position: relative;
        }

        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        /* Style untuk badge (sudah diubah) */
        .tag {
            background-color: #405189;
            /* Warna background baru */
            color: white;
            padding: 3px 6px;
            /* Padding lebih kecil */
            border-radius: 3px;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .tag .close {
            margin-left: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }

        #tag-input {
            border: none;
            outline: none;
            flex-grow: 1;
            padding: 5px;
            font-size: 14px;
            min-width: 150px;
        }

        .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 4px 4px;
            z-index: 10;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dropdown div {
            padding: 10px;
            cursor: pointer;
        }

        .dropdown div:hover {
            background-color: #f0f0f0;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Data {{ $titleHeader->name }}
        @endslot
    @endcomponent


    <div class="row">
        <div class="col-lg-12">
            <form action="#" method="post">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <div class="card">
                    <div class="card-body">
                        @include('backend.includes.flash')
                        <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox" type="checkbox" value=""></th>
                                    <th class="th_no" data-hide="phone">No</th>
                                    <th data-class="expand">Nama</th>
                                    <th data-class="expand">Telepon</th>
                                    <th data-class="expand">Tags</th>
                                    <th data-hide="phone">Toko yang Diasosiasikan</th>

                                    <th data-hide="phone">Created at</th>
                                    <th>Aksi</th>
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
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Nama</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="name" id="name" placeholder="" size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Tags</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="tags" id="tags" placeholder="" size="40">
                                </div>
                            </div>

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

    {{-- Modal Create --}}
    <div id="createModal" class="modal fade modal-primary">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="las la-plus-circle"></i> Create {{ $titleHeader->name }}</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_create" method="POST" action="{{ route('party.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal" novalidate>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Nama</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="name" id="name" placeholder="Nama" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Telepon</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="telephone" id="telephone" placeholder="Nomor Telepon" size="50">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Asosiasikan Dengan Toko</label>
                                <div class="col-sm-10 w-100">
                                    <select class="form-control js-example-basic-single" name="store_id" id="store_id" style="width: 100%">
                                        <option value="" selected>-- Pilih Toko --</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="tags" class="control-label">Tags</label>
                                <div class="tags-input-wrapper" id="tags-input-wrapper" data-initial-tags="">
                                    <div class="tags-container" id="tags-container"></div>
                                    <input type="text" class="tag-input" id="tag-input" placeholder="Pilih atau ketik..." autocomplete="off" />
                                    <div class="dropdown" id="dropdown"></div>
                                    <input type="hidden" class="tag-value" name="tags_value" id="tags_value" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light" data-bs-dismiss="modal">
                            <i class="las la-times-circle"></i> Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Create --}}

    {{-- Modal Edit --}}
    <div id="editModal" class="modal fade modal-primary">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="las la-edit"></i> Edit {{ $titleHeader->name }}</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form_edit" method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal">
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Nama</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="name" id="name" placeholder="Nama" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Telepon</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="telephone" id="telephone" placeholder="Nomor Telepon" size="50">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Asosiasikan Dengan Toko</label>
                                <div class="col-sm-10 w-100">
                                    <select class="form-control js-example-basic-single" name="store_id" id="store_id" style="width: 100%">
                                        <option id="default-option" value="">Jangan Asosiasikan</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="tags" class="control-label">Tags</label>
                                <div class="tags-input-wrapper" id="tags-input-wrapper" data-initial-tags="">
                                    <div class="tags-container" id="tags-container"></div>
                                    <input type="text" class="tag-input" id="tag-input" placeholder="Pilih atau ketik..." autocomplete="off" />
                                    <div class="dropdown" id="dropdown"></div>
                                    <input type="hidden" class="tag-value" name="tags_value" id="tags_value" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-light" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- End Modal Edit --}}

    <!-- form import -->
    <form id="form_import" name="form_import" action="" method="post">
        <div id="modalImport"class="modal fade modal-primary">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-info-circle"> </i> Import Data</h4>
                    </div>
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal">
                            <div class="col-md-2"></div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <button class="btn btn-sm btn-success" id="btnDownloadTemplate" data-tabel="parties"><i class="fa fa-download"></i> Download template</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <button class="btn btn-sm btn-info" id="btnImportData"><i class="fa fa-upload"></i> Import Data</button>
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                        </div><!-- /.box-body -->
                    </div>
                    <div class="modal-footer">
                        <button id="btn-reset" onClick="this.form.reset()" type="button" class="btn btn-outline pull-left">Reset</button>
                        <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- end form import -->
    {{-- @include('layouts.modal_delete') --}}
@endsection


@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jszip-3.1.3/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-1.2.4/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>



    <script>
        const TagManager = (wrapper) => {
            let state = {
                selectedTags: [],
                tagsWrapper: $(wrapper),
                tagsContainer: $(wrapper).find('.tags-container'),
                input: $(wrapper).find('.tag-input'),
                dropdown: $(wrapper).find('.dropdown'),
                hiddenInput: $(wrapper).find('.tag-value'),
                defaultOptions: ['Store', 'Customer', 'Supplier']
            };

            const manager = {
                loadInitialTags: () => {
                    const initialTags = state.tagsWrapper.data('initial-tags');
                    if (initialTags) {
                        initialTags.split(',').forEach(tag => {
                            if (tag.trim()) manager.addTag(tag.trim());
                        });
                    }
                },

                renderDropdown: () => {
                    state.dropdown.empty();
                    state.defaultOptions.forEach(option => {
                        if (!state.selectedTags.includes(option)) {
                            const $optionDiv = $('<div>').text(option).data('value', option);
                            state.dropdown.append($optionDiv);
                        }
                    });
                },

                addTag: (label) => {
                    const trimmedLabel = $.trim(label);
                    if (trimmedLabel && !state.selectedTags.includes(trimmedLabel)) {
                        state.selectedTags.push(trimmedLabel);

                        const $tagElement = $('<div>').addClass('tag').text(trimmedLabel);
                        const $closeBtn = $('<span>').addClass('close').html('&times;');

                        $closeBtn.on('click', function() {
                            manager.removeTag($tagElement, trimmedLabel);
                        });

                        $tagElement.append($closeBtn);
                        state.tagsContainer.append($tagElement);
                        state.input.val('');

                        manager.updateHiddenInput();
                        manager.renderDropdown();
                    }
                },

                removeTag: ($tagElement, label) => {
                    const index = state.selectedTags.indexOf(label);
                    if (index > -1) {
                        state.selectedTags.splice(index, 1);
                        $tagElement.remove();
                        manager.updateHiddenInput();
                        manager.renderDropdown();
                    }
                },

                updateHiddenInput: () => {
                    state.hiddenInput.val(state.selectedTags.join(','));
                },

                setTags: (tagsArray) => {
                    // Reset dulu
                    state.selectedTags = [];
                    state.tagsContainer.empty();

                    // Isi ulang dari array
                    tagsArray.forEach(tag => {
                        if (tag.trim()) manager.addTag(tag.trim());
                    });

                    manager.updateHiddenInput();
                    manager.renderDropdown();
                },

                init: () => {
                    state.tagsWrapper.on('click', function() {
                        state.input.trigger('focus');
                    });

                    state.input.on('focus', function() {
                        if (state.dropdown.children().length > 0) {
                            state.dropdown.show();
                        }
                    });

                    $(document).on('click', function(e) {
                        if (!$(e.target).closest(state.tagsWrapper).length) {
                            state.dropdown.hide();
                        }
                    });

                    state.input.on('keydown', function(e) {
                        if (e.which === 13) {
                            e.preventDefault();
                            manager.addTag(state.input.val());
                            state.dropdown.hide();
                        }
                        if (e.which === 8 && state.input.val() === '' && state.selectedTags.length > 0) {
                            const $lastTagElement = state.tagsContainer.children().last();
                            const lastTagLabel = $lastTagElement.text().slice(0, -1).trim();
                            manager.removeTag($lastTagElement, lastTagLabel);
                        }
                    });

                    state.dropdown.on('click', 'div', function() {
                        manager.addTag($(this).data('value'));
                        state.dropdown.hide();
                        state.input.trigger('focus');
                    });

                    manager.loadInitialTags();
                    manager.renderDropdown();
                }
            };

            return manager;
        };

        const Utils = {
            showAlert(type, message) {
                $('#alert-container').html(`
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
            },

            initCurrencyMask() {
                $('.currency').mask('000.000.000.000', {
                    reverse: true
                });
            },

            toast(message, type = "success") {
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    gravity: "bottom",
                    position: "right",
                    className: type === "success" ? "bg-success" : "bg-danger",
                    stopOnFocus: true
                }).showToast();
            }
        };

        const EventListeners = {
            init() {
                Utils.initCurrencyMask();
                this.downloadTemplate();
                this.createFormSubmit();
                this.editButtonClick();
                this.editFormSubmit();
                this.deleteButtonClick();
                this.checkboxClick();
                this.modalClosed();
                this.initDataTable();
                this.modalSelect2();
            },

            downloadTemplate() {
                $(document).on('click', '#btnDownloadTemplate', function(e) {
                    e.preventDefault();
                    let tabel = $(this).data('tabel');
                    $.ajax({
                        url: "{{ route('get.partiesTemplate') }}",
                        type: "GET",
                        data: {
                            _token: '{{ csrf_token() }}',
                            tabel: tabel
                        },
                        dataType: 'json',
                        success: function() {
                            window.location = "{{ route('download.partiesTemplate') }}";
                        }
                    });
                });
            },

            createFormSubmit() {
                $('#form_create').on('submit', function(e) {
                    e.preventDefault();
                    let form = $(this)[0];

                    if (!form.checkValidity()) {
                        $(form).addClass('was-validated');
                        Utils.toast("Please check your input!", "danger");
                        return;
                    }

                    $(form).removeClass('was-validated');
                    let formData = new FormData(this);

                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            Utils.toast("Data saved successfully!");
                            $('#form_create')[0].reset();
                            $('.dataTable').DataTable().ajax.reload(null, false);
                            Utils.showAlert('success', 'Data saved successfully!');
                        },
                        error: function() {
                            Utils.showAlert('danger', 'Failed to save data');
                        }
                    });
                });
            },

            editButtonClick() {
                $('body').on('click', '.btnEdit', function(e) {
                    e.preventDefault();
                    let id = $(this).data('id');

                    const tags = $(this).data('tags');
                    const tagsArray = tags ?? [];

                    const storeId = $(this).data('store-id');
                    const storeName = $(this).data('store-name');

                    $.ajax({
                        url: "{{ route($url . '.edit', '') }}/" + id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            let data = response.data;
                            let $form = $('#form_edit');

                            $.each(data, function(key, value) {
                                if (key === 'password') return;

                                let $fields = $form.find(`[name="${key}"], [name="${key}[]"]`);

                                if ($form.find(`input[name="${key}[]"]`).length) {
                                    $form.find(`input[name="${key}[]"]`).prop('checked', false);
                                    if (value) {
                                        value.split(',').forEach(v => {
                                            $form.find(`input[name="${key}[]"][value="${v.trim()}"]`).prop('checked', true);
                                        });
                                    }
                                } else if ($form.find(`input[type="radio"][name="${key}"]`).length) {
                                    $form.find(`input[type="radio"][name="${key}"][value="${value}"]`).prop('checked', true);
                                } else if ($form.find(`select[name="${key}"]`).length) {
                                    $form.find(`select[name="${key}"]`).val(value).trigger('change');
                                } else {
                                    let $input = $form.find(`[name="${key}"]`);
                                    if ($input.attr('type') !== 'file') {
                                        $input.val(value);
                                    }
                                }
                            });

                            // Set initial tags for TagManager
                            const wrapper = $('#editModal .tags-input-wrapper')[0];
                            const tagManager = TagManager('#editModal .tags-input-wrapper')
                            tagManager.init()
                            tagManager.setTags(tagsArray);

                            // Masukkan data store yang sudah dipilih (append setelah opsi default)
                            if (storeId && storeName) {
                                if ($form.find(`select[name="store_id"] option[value="${storeId}"]`).length === 0) {
                                    let newOption = $('<option>', {
                                        value: storeId,
                                        text: storeName,
                                        selected: true,
                                        class: 'removed-option'
                                    });

                                    newOption.insertAfter($form.find('select[name="store_id"] option').first());
                                }
                            }

                            $('#editModal #store_id').val(data.store_id).trigger('change');

                            $('#form_edit').data('id', id);
                            $('#editModal').modal('show');
                        },
                        error: function() {
                            Utils.showAlert('danger', 'Gagal mengambil data.');
                        }
                    });
                });
            },

            editFormSubmit() {
                $('#form_edit').on('submit', function(e) {
                    e.preventDefault();
                    let form = $(this)[0];

                    if (!form.checkValidity()) {
                        $(form).addClass('was-validated');
                        Utils.toast("Please check your input!", "danger");
                        return;
                    }

                    $(form).removeClass('was-validated');
                    let formData = new FormData(this);
                    let id = $(this).data('id');
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                    formData.append('_method', 'PUT');

                    $.ajax({
                        url: '/{{ $url }}/' + id,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            Utils.toast("Data saved successfully!");
                            $('.dataTable').DataTable().ajax.reload(null, false);
                            Utils.showAlert('success', 'Data berhasil diperbarui!');
                        },
                        error: function() {
                            Utils.showAlert('danger', 'Gagal update data.');
                        }
                    });
                });
            },

            deleteButtonClick() {
                $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                    e.preventDefault();
                    console.log('delete clicked');
                    let id_to_be_deleted = $(this).attr("id");
                    $.ajax({
                        type: "POST",
                        url: "{{ route('party.delete') }}",
                        data: {
                            id_to_be_deleted: [id_to_be_deleted],
                            delete_type: 'softdelete'
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(data) {
                            $('#myModal').modal('show');
                            $('#modal-delete').html(data);
                        }
                    });
                });
            },

            checkboxClick() {
                $('#main_checkbox').on('click', function() {
                    let isChecked = $(this).is(':checked');
                    $('#dt_basic tbody input:checkbox').prop('checked', isChecked);
                });

                $('#dt_basic tbody').on('click', 'input:checkbox', function() {
                    let allChecked = $('#dt_basic tbody input:checkbox').length === $('#dt_basic tbody input:checkbox:checked').length;
                    $('#main_checkbox').prop('checked', allChecked);
                });
            },

            initDataTable() {
                let table = new DataTable('#dt_basic', {
                    dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                        "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    scrollX: true,
                    lengthMenu: [
                        [100, 250, 500, 1000, -1],
                        [100, 250, 500, 1000, "All"]
                    ],
                    order: [
                        [4, "asc"]
                    ],
                    language: {
                        "lengthMenu": "Display _MENU_ "
                    },
                    ajax: {
                        url: "{{ route('party.get_datatable') }}",
                        type: "get",
                        data: function(data) {
                            data.name = $('#name').val();
                            data.tags = $('#tags').val();
                            i = data.start + 1;
                        }
                    },
                    columns: [{
                            data: 'checkboxs',
                            name: 'checkboxs',
                            orderable: false,
                            searchable: false,
                            width: '20px'
                        },
                        {
                            data: 'number',
                            name: 'number',
                            width: '20px',
                            render: () => i++
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'telephone',
                            name: 'telephone'
                        },
                        {
                            data: 'tags',
                            name: 'tags'
                        },
                        {
                            data: 'store_name',
                            name: 'store_name'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            width: '100px'
                        }
                    ],
                    buttons: [
                        @if (\RuleHelper::UserHasPermission('create', false))
                            {
                                text: '<i class="las la-plus-square text-success"></i> Create',
                                className: 'child-button',
                                action: function() {
                                    $('#form_create')[0].reset();
                                    $('#createModal').modal('show');
                                },
                            },
                        @endif
                        @if (\RuleHelper::UserHasPermission('delete', false))
                            {
                                text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                                className: 'child-button',
                                action: function(e, dt, button, config) {
                                    e.preventDefault();

                                    var id_to_be_deleted = $("#dt_basic tbody input:checkbox:checked").map(function() {
                                        return $(this).val();
                                    }).get();

                                    $.ajax({
                                        type: "POST",
                                        url: "{{ route('party.delete') }}",
                                        data: {
                                            id_to_be_deleted: id_to_be_deleted,
                                            delete_type: 'softdelete'
                                        },
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function(data) {
                                            $('#myModal').modal('show');
                                            $('#modal-delete').html(data);
                                        }
                                    });
                                }
                            },
                        @endif {
                            extend: 'collection',
                            text: '<i class="las la-file-excel"></i> Export',
                            className: 'child-button',
                            buttons: [{
                                extend: 'excel',
                                text: 'excel',
                                exportOptions: {
                                    columns: [1, 2, 3]
                                }
                            }]
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="las la-eye"></i> Show',
                            className: 'child-button'
                        },
                        {
                            text: '<i class="las la-filter"></i> Filter',
                            className: 'child-button',
                            action: function(e, dt, button, config) {
                                $('#myFilterModal').modal('show');
                            }
                        },
                    ]
                });

                // Tambahkan button untuk menuju halaman trash jika user memiliki izin
                @if (\RuleHelper::UserHasPermission('trash', false))
                    var a = '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-primary" tabindex="0" aria-controls="dt_basic" href="{{ route('party.index') }}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{ route('party.trash') }}" class="btn btn-info btn-light" style="margin-right:5px;" tabindex="0" ><span><i class="bx bx-recycle"></i> Recycle Bin ({{ $parties_count }}) </span> </a></div>';
                    $(".btn-tbl").prepend(a);
                @endif

                table.buttons(1, null).container()
                    .addClass('btn-group')
                    .appendTo(table.table().container());

                $('#btn-filter').click(() => table.ajax.reload());
                $('#btn-reset').click(() => table.ajax.reload());
            },

            modalSelect2() {
                $('.modal').on('shown.bs.modal', function() {
                    $(this).find('.js-example-basic-single').select2({
                        dropdownParent: $(this)
                    });
                });
            },

            modalClosed() {
                $('.modal').on('hidden.bs.modal', function() {
                    // Hapus opsi yang ditambahkan secara dinamis
                    $(this).find('select').each(function() {
                        $(this).find('option.removed-option').remove();
                        $(this).val($(this).find('option:first').val()).trigger('change');
                    });

                    // Reset form di dalam modal
                    $(this).find('form')[0].reset();

                    // Hapus tag yang ada di TagManager
                    $(this).find('.tags-input-wrapper').each(function() {
                        const manager = TagManager(this);
                        manager.init();
                        manager.setTags([]);
                    });
                });
            }
        };

        $(document).ready(function() {
            $('.tags-input-wrapper').each(function() {
                const manager = TagManager(this);
                manager.init();
            });

            EventListeners.init();
        });
    </script>



    <!-- dashboard init -->
@endsection
