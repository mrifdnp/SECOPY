@extends('layouts.master')
@section('title')
    List {{ $titleHeader->name ?? 'Material' }}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Data {{ $titleHeader->name ?? 'Material' }}
        @endslot
    @endcomponent


    <div class="row">
        <div class="col-lg-12">
            <form action="#" method="post">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <div class="card">
                    <div class="card-body">
                        @include('backend.includes.flash')
                        <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox"
                                            type="checkbox" value=""></th>
                                    <th class="th_no" data-hide="phone">No</th>
                                    <th data-class="expand">Title</th>
                                    <th data-class="expand">Tipe</th>
                                    <th data-hide="phone">Created At</th>
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
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Title</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="title" id="title"
                                        placeholder="" size="40">
                                </div>
                            </div>
                        </div><!-- /.box-body -->
                    </div>

                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i
                                class="las la-times-circle"></i> Close</button>
                        <button type="button" id="btn-reset" onClick="this.form.reset()"
                            class="btn btn-warning pull-left text-light"><i class="las la-undo-alt"></i> Reset</button>
                        <button type="button" id="btn-filter" class="btn btn-info pull-left text-light"><i
                                class=" las la-share"></i> Filter</button>
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
                    <h5 class="text-white"><i class="las la-plus-circle"></i> Create {{ $titleHeader->name ?? 'Material' }}</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_create" method="POST" action="{{ route('material.store') }}" enctype="multipart/form-data"
                    class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal" novalidate>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Title</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="title"
                                        id="title" placeholder="" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_consignment" id="create_is_consignment" value="1">
                                    <label class="form-check-label" for="create_is_consignment">Consignment</label>
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
                    <h5 class="text-white"><i class="las la-edit"></i> Edit {{ $titleHeader->name ?? 'Material' }}</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form_edit" method="POST" action="" enctype="multipart/form-data"
                    class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal">
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Title</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="title"
                                        id="title" placeholder="" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_consignment" id="edit_is_consignment" value="1">
                                    <label class="form-check-label" for="edit_is_consignment">Consignment</label>
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

@endsection


@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link
        href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.dataTables.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link
        href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js') }}">
    </script>
    <script
        src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/dataTables.buttons.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.print.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.html5.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jszip-3.1.3/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-1.2.4/buttons.colVis.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>

    <script>
        function showAlert(type, message) {
            $('#alert-container').html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `);
        }

        $(document).ready(function() {
            $('#form_create').on('submit', function(e) {
                e.preventDefault();

                let form = $(this)[0];
                if (!form.checkValidity()) {
                    $(form).addClass('was-validated');
                    Toastify({
                        text: "Please check your input!",
                        duration: 3000,
                        close: true,
                        gravity: "bottom",
                        position: "right",
                        className: "bg-danger",
                        stopOnFocus: true
                    }).showToast();
                    return;
                }

                $(form).removeClass('was-validated');

                let url = $(form).attr('action');
                let formData = new FormData(this);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Toastify({
                            text: "Data saved successfully!",
                            duration: 3000,
                            close: true,
                            gravity: "bottom",
                            position: "right",
                            className: "bg-success",
                            stopOnFocus: true
                        }).showToast();

                        $('#form_create')[0].reset();
                        $('#createModal').modal('hide');
                        $('.dataTable').DataTable().ajax.reload(null, false);
                    },
                    error: function() {
                        showAlert('danger', 'Failed to save data');
                    }
                });
            });
        });

        $('body').on('click', '.btnEdit', function(e) {
            e.preventDefault();
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route('material.edit', '') }}/" + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let data = response.data;
                    $.each(data, function(key, value) {
                        let $form = $('#form_edit');
                        let $input = $form.find(`[name="${key}"]`);
                        if ($input.length && $input.attr('type') !== 'file' && $input.attr('type') !== 'checkbox') {
                            $input.val(value);
                        }
                    });
                    // Set checkbox Consignment
                    $('#edit_is_consignment').prop('checked', data.is_consignment == 1);
                    $('#form_edit').data('id', id);
                    $('#editModal').modal('show');
                },
                error: function() {
                    showAlert('danger', 'Gagal mengambil data.');
                }
            });
        });

        $('#form_edit').on('submit', function(e) {
            e.preventDefault();

            let form = $(this)[0];
            if (!form.checkValidity()) {
                $(form).addClass('was-validated');
                Toastify({
                    text: "Please check your input!",
                    duration: 3000,
                    close: true,
                    gravity: "bottom",
                    position: "right",
                    className: "bg-danger",
                    stopOnFocus: true
                }).showToast();
                return;
            }

            $(form).removeClass('was-validated');

            let formData = new FormData(this);
            let id = $(this).data('id');
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('_method', 'PUT');

            $.ajax({
                url: '/material/' + id,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Toastify({
                        text: "Data saved successfully!",
                        duration: 3000,
                        close: true,
                        gravity: "bottom",
                        position: "right",
                        className: "bg-success",
                        stopOnFocus: true
                    }).showToast();

                    $('#editModal').modal('hide');
                    $('.dataTable').DataTable().ajax.reload(null, false);
                },
                error: function() {
                    showAlert('danger', 'Gagal update data.');
                }
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            let table = new DataTable('#dt_basic', {
                dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                    "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollX: true,
                "lengthMenu": [
                    [100, 250, 500, 1000, -1],
                    [100, 250, 500, 1000, "All"]
                ],
                order: [
                    [4, "desc"]
                ],
                language: {
                    "lengthMenu": "Display _MENU_ "
                },
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
                                var id_to_be_deleted = $("#dt_basic tbody input:checkbox:checked")
                                    .map(function() {
                                        return $(this).val();
                                    }).get();
                                $.ajax({
                                    type: "POST",
                                    url: "{{ route('material.delete') }}",
                                    data: {
                                        id_to_be_deleted: id_to_be_deleted,
                                        delete_type: 'softdelete'
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    success: function(data) {
                                        $('#myModal').modal('show');
                                        $('#modal-delete').html(data);
                                    }
                                });
                            }
                        },
                    @endif 
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
                ],
                ajax: {
                    "url": "{{ route('material.get_datatable') }}",
                    "type": "get",
                    "data": function(data) {
                        data.title = $('#title').val();
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
                        render: function() {
                            return i++;
                        }
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'is_consignment',
                        name: 'is_consignment',
                        className: 'text-center'
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

            });
            $('#btn-filter').click(function() { //button filter event click
                table.ajax.reload(); //just reload table
            });
            $('#btn-reset').click(function() { //button reset event click
                table.ajax.reload(); //just reload table
            });
            $('body').on('click', '#main_checkbox', function() {
                if ($(this).is(':checked')) {
                    $('.checkbox_item').prop('checked', true);
                } else {
                    $('.checkbox_item').prop('checked', false);
                }
            });
            $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                e.preventDefault();
                var id_to_be_deleted = $(this).attr("id");
                $.ajax({
                    type: "POST",
                    url: "{{ route('material.delete') }}",
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
            @if (\RuleHelper::UserHasPermission('trash', false))
                var a =
                    '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-primary" tabindex="0" aria-controls="dt_basic" href="{{ route('material.index') }}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{ route('material.trash') }}" class="btn btn-info btn-light" style="margin-right:5px;" tabindex="0" ><span><i class="bx bx-recycle"></i> Recycle Bin ({{ $materials_count ?? 0 }}) </span> </a></div>';
                $(".btn-tbl").prepend(a);
            @endif
            table.buttons(1, null).container()
                .addClass('btn-group')
                .appendTo(table.table().container());
        });
    </script>
@endsection
