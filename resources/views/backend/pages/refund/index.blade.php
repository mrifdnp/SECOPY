@extends('layouts.master')
@section('title')
    List Refund
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Data Refund
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @include('backend.includes.flash')
                    <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle"
                        style="width:100%">
                        <thead>
                            <tr>
                                <th class="th_no" data-hide="phone">No</th>
                                <th>Refund Date</th>
                                <th>Refund Invoice</th>
                                <th>Transaction Invoice</th>
                                <th>Reason</th>
                                <th>Total Refund</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            var url_data = "{{ route('refund.get_datatable') }}";
            var table = $('#dt_basic').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: url_data,
                    type: 'GET'
                },
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'refund_date', name: 'refund_date' },
                    { data: 'refund_invoice_id', name: 'refund_invoice_id' },
                    { data: 'invoice_id', name: 'invoice_id' },
                    { data: 'reason', name: 'reason' },
                    { data: 'total_refund', name: 'total_refund' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
                scrollX: true,
            });
        });
    </script>
@endsection
