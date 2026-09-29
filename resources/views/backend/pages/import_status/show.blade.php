@extends('layouts.master')

@section('title')
    Detail {{ $titleHeader->name }}
@endsection

@section('cssAdded')
    <style>
        /* CSS untuk horizontal scroll */
        .table-responsive-custom {
            overflow-x: auto;
            white-space: nowrap;
        }

        .table-responsive-custom .table {
            min-width: 1200px;
            /* Set minimum width sesuai kebutuhan */
            margin-bottom: 0;
        }

        .table-responsive-custom .table th,
        .table-responsive-custom .table td {
            white-space: nowrap;
            /* Mencegah text wrapping */
            min-width: 120px;
            /* Set minimum width untuk setiap kolom */
        }

        /* Styling untuk kolom checkbox agar lebih kecil */
        .table-responsive-custom .table th:first-child,
        .table-responsive-custom .table td:first-child {
            min-width: 50px;
            width: 50px;
        }

        /* Styling untuk kolom action agar lebih kecil */
        .table-responsive-custom .table th:last-child,
        .table-responsive-custom .table td:last-child {
            min-width: 80px;
            width: 80px;
        }

        /* Custom scrollbar styling (optional) */
        .table-responsive-custom::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive-custom::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive-custom::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive-custom::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .value-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            min-height: 30px;
        }

        .value-left {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .value-right {
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }

        .old-value {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 2px;
        }

        .current-value {
            font-weight: 500;
        }

        .error-icon {
            color: #dc3545;
            cursor: pointer;
            font-size: 18px;
        }

        .tooltip {
            opacity: 1 !important;
        }

        .tooltip-inner {
            max-width: 300px;
            text-align: left;
        }

        .replace-table th,
        .replace-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .replace-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            margin-top: 20px;
            color: #495057;
        }

        .table-title:first-child {
            margin-top: 0;
        }

        .download-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Detail {{ $titleHeader->name }}
        @endslot
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
                                    <td style="width: 200px"><b>Task</b></td>
                                    <td>{{ $importStatus->task }}</td>
                                </tr>
                                <tr>
                                    @php
                                        $badgeClass = 'badge-soft-secondary';
                                        switch (strtolower($importStatus->status)) {
                                            case 'waiting':
                                                $badgeClass = 'badge-soft-warning';
                                                break;
                                            case 'running':
                                                $badgeClass = 'badge-soft-info';
                                                break;
                                            case 'done':
                                                $badgeClass = 'badge-soft-success';
                                                break;
                                            case 'failed':
                                                $badgeClass = 'badge-soft-danger';
                                                break;
                                            default:
                                                $badgeClass = 'badge-soft-secondary';
                                        }
                                    @endphp
                                    <td style="width: 200px"><b>Status</b></td>
                                    <td><span class="badge {{ $badgeClass }} fs-6">{{ $importStatus->status }}</span></td>
                                </tr>
                                <tr>
                                    <td style="width: 200px"><b>Data Sukses</b></td>
                                    <td>
                                        <span class="badge bg-success fs-6">{{ number_format($importStatus->success_row) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 200px"><b>Data Gagal</b></td>
                                    <td><span class="badge bg-danger fs-6">{{ number_format($importStatus->failed_row) }}</span></td>
                                </tr>
                                <tr>
                                    <td style="width: 200px"><b>Total Data</b></td>
                                    <td><span class="badge bg-primary fs-6">{{ number_format($importStatus->total_row) }}</span></td>
                                </tr>
                                <tr>
                                    <td style="width: 200px"><b>Created at</b></td>
                                    <td>{{ $importStatus->created_at->format('d-m-Y H:i:s') }}</td>
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

    @if (!$failedImportData->isEmpty())
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Data Gagal</h4>
                        <p class="card-title-desc">Berikut adalah data yang gagal diimpor. Silakan periksa dan lakukan tindakan yang diperlukan.</p>
                    </div>
                    <div class="card-body">
                        <!-- Wrapper untuk horizontal scroll -->
                        <a href="#" id="bulkDeleteButton" class="btn btn-soft-secondary">Bulk Delete</a>
                        <button type="button" class="btn btn-soft-secondary download-btn" data-import-status-id="{{ $importStatus->id }}">
                            <span class="btn-text">Download Failed Data</span>
                        </button>
                        <div class="table-responsive-custom">
                            <table class="table table-wrap mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="mainCheck">
                                                <label class="form-check-label" for="mainCheck"></label>
                                            </div>
                                        </th>
                                        <th style="width: 50px; min-width: 50px;">No</th>
                                        @foreach ($failedDataColumns as $column)
                                            <th>{{ $column }}</th>
                                        @endforeach
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="failedDataTableBody">
                                    @foreach ($failedImportData as $index => $data)
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="{{ $data->id }}" id="data_check_{{ $data->id }}">
                                                    <label class="form-check-label" for="data_check_{{ $data->id }}"></label>
                                                </div>
                                            </th>
                                            <td style="width: 50px; min-width: 50px;">{{ $index + 1 }}</td>
                                            {{-- Loop through each column in the failed data --}}
                                            @foreach ($failedDataColumns as $column)
                                                <td>
                                                    <div class="value-container">
                                                        <div class="value-left">
                                                            {{-- Show old value with strikethrough if exists --}}
                                                            @if (isset($data->row[$column]['old_value']) && !empty($data->row[$column]['old_value']))
                                                                <div class="old-value">{{ $data->row[$column]['old_value'] }}</div>
                                                            @endif
                                                            {{-- Show current value --}}
                                                            <div class="current-value">{{ $data->row[$column]['value'] ?? '-' }}</div>
                                                        </div>
                                                        <div class="value-right">
                                                            {{-- Show error icon with tooltip if mistake exists --}}
                                                            @if (isset($data->row[$column]['mistake']) && !empty($data->row[$column]['mistake']))
                                                                <i class="las la-exclamation-triangle error-icon" data-bs-toggle="tooltip" data-bs-placement="top" data-error-message="{{ $data->row[$column]['mistake'] }}" title="{{ $data->row[$column]['mistake'] }}"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            @endforeach
                                            <td>
                                                <a href="#" class="btn btn-sm btn-soft-secondary btnDelete" id="{{ $data->id }}" title="Delete">
                                                    <i class="las la-trash"></i>
                                                </a>
                                                @if (!is_null($data->replace_action))
                                                    <a href="#" class="btn btn-sm btn-soft-secondary btnReplace" id="{{ $data->id }}" data-updated="{{ $data->replace_action }}">Replace</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button id="loadMoreButton" class="btn btn-primary mb-4" data-import-status-id="{{ $importStatus->id }}" data-offset="{{ count($failedImportData) }}">
                        <span class="button-text">Load More Data</span>
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div id="myModal"class="modal fade modal-primary">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-delete" style="border: none !important;">

            </div>
        </div>
    </div>

    <div id="replaceModal" class="modal fade" tabindex="-1" aria-labelledby="replaceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replaceModalLabel">Konfirmasi Replace</h5>
                </div>
                <div class="modal-body">
                    <h5 class="fs-15 mb-3">
                        Berikut adalah data yang akan di-replace. Pastikan data yang akan di-replace sudah benar.
                    </h5>
                    <div id="replaceDataContainer">
                        {{-- Dynamic content will be populated here --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('failed_import_data.replace') }}" method="POST">
                        @csrf
                        <input type="hidden" name="failed_import_data_id" id="failed_import_data_id">
                        <button type="submit" class="btn btn-primary">Replace</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-1.2.4/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>

    <script>
        $(document).ready(function() {
            var loadMoreButton = $('#loadMoreButton');
            var tableBody = $('#failedDataTableBody');
            var currentOffset = parseInt(loadMoreButton.data('offset'));
            var importStatusId = loadMoreButton.data('import-status-id');
            var isLoading = false;
            var failedDataColumns = @json($failedDataColumns);

            // Load More Button Click Handler
            loadMoreButton.click(function(e) {
                e.preventDefault();

                if (isLoading) return;

                loadMoreData();
            });

            // Function to load more data
            function loadMoreData() {
                isLoading = true;
                showLoadingState();

                $.ajax({
                    url: "{{ route('failed_import_data.load_more') }}", // Replace with your actual route
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        import_status_id: importStatusId,
                        offset: currentOffset
                    },
                    success: function(response) {
                        if (response.data && response.data.length > 0) {
                            appendNewRows(response.data);
                            currentOffset += response.data.length;
                            loadMoreButton.data('offset', currentOffset);
                        }

                        // Hide button if no more data
                        if (!response.hasMore || response.data.length < 100) {
                            loadMoreButton.hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Load more failed:', error);
                        showMessage('Failed to load more data. Please try again.', 'error');
                    },
                    complete: function() {
                        isLoading = false;
                        hideLoadingState();
                    }
                });
            }

            function showMessage(message, type) {
                var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';

                $('body').prepend(alertHtml);

                setTimeout(function() {
                    $('.alert').alert('close');
                }, 3000);
            }

            // Function to show loading state
            function showLoadingState() {
                loadMoreButton.prop('disabled', true);
                loadMoreButton.find('.button-text').text('Loading...');
                loadMoreButton.find('.spinner-border').removeClass('d-none');
            }

            // Function to hide loading state
            function hideLoadingState() {
                loadMoreButton.prop('disabled', false);
                loadMoreButton.find('.button-text').text('Load More Data');
                loadMoreButton.find('.spinner-border').addClass('d-none');
            }

            // Function to append new rows to table
            function appendNewRows(data) {
                var currentRowCount = tableBody.find('tr').length;

                data.forEach(function(item, index) {
                    var rowNumber = currentRowCount + index + 1;
                    var rowHtml = buildRowHtml(item, rowNumber);
                    var newRow = $(rowHtml).addClass('fade-in');
                    tableBody.append(newRow);
                });

                // Re-initialize tooltips for new rows
                initializeTooltips();

                // Update checkbox event handlers
                updateCheckboxHandlers();
            }

            // Function to build row HTML
            function buildRowHtml(data, rowNumber) {
                var html = '<tr data-id="' + data.id + '">';

                // Checkbox column
                html += '<th scope="row">';
                html += '<div class="form-check">';
                html += '<input class="form-check-input" type="checkbox" value="' + data.id + '" id="data_check_' + data.id + '">';
                html += '<label class="form-check-label" for="data_check_' + data.id + '"></label>';
                html += '</div>';
                html += '</th>';

                // Row number
                html += '<td style="width: 50px; min-width: 50px;">' + rowNumber + '</td>';

                // Data columns
                failedDataColumns.forEach(function(column) {
                    html += '<td>';
                    html += '<div class="value-container">';
                    html += '<div class="value-left">';

                    // Old value if exists
                    if (data.row[column] && data.row[column].old_value) {
                        html += '<div class="old-value">' + data.row[column].old_value + '</div>';
                    }

                    // Current value
                    var currentValue = (data.row[column] && data.row[column].value) ? data.row[column].value : '-';
                    html += '<div class="current-value">' + currentValue + '</div>';

                    html += '</div>';
                    html += '<div class="value-right">';

                    // Error icon if mistake exists
                    if (data.row[column] && data.row[column].mistake) {
                        html += '<i class="las la-exclamation-triangle error-icon" ';
                        html += 'data-bs-toggle="tooltip" ';
                        html += 'data-bs-placement="top" ';
                        html += 'data-error-message="' + data.row[column].mistake + '" ';
                        html += 'title="' + data.row[column].mistake + '"></i>';
                    }

                    html += '</div>';
                    html += '</div>';
                    html += '</td>';
                });

                // Action column
                html += '<td>';
                html += "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete me-1' data-id=" + data.id + " title='Delete'>";
                html += '<i class="las la-trash"></i>';
                html += '</a>';

                if (data.replace_action) {
                    html += '<a href="#" class="btn btn-sm btn-soft-secondary btnReplace" ';
                    html += 'data-id="' + data.id + '" ';
                    html += "data-updated='" + data.replace_action + "'>Replace</a>";
                }

                html += '</td>';
                html += '</tr>';

                return html;
            }

            // Function to update checkbox event handlers for new rows
            function updateCheckboxHandlers() {
                // Remove existing handlers to avoid duplicates
                $('input[id^="data_check_"]').off('change.loadmore');

                // Add handlers for all checkboxes
                $('input[id^="data_check_"]').on('change.loadmore', function() {
                    updateMainCheckbox();
                });

                // Update delete button handlers
                $('.btnDelete').off('click.loadmore');
                $('.btnDelete').on('click.loadmore', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');

                    deleteFailedImportData(id);
                });

                // Update replace button handlers
                $('.btnReplace').off('click.loadmore');
                $('.btnReplace').on('click.loadmore', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var updatedData = $(this).data('updated');

                    try {
                        populateReplaceModal(updatedData, id);
                        var modal = new bootstrap.Modal(document.getElementById('replaceModal'));
                        modal.show();
                    } catch (error) {
                        console.error('Error parsing replace data:', error);
                        alert('Error loading replace data. Please try again.');
                    }
                });
            }

            // Initialize Bootstrap tooltips for error icons
            initializeTooltips();

            // Main checkbox functionality
            $('#mainCheck').change(function() {
                $('input[id^="data_check_"]').prop('checked', this.checked);
            });

            // Individual checkbox functionality
            $('input[id^="data_check_"]').change(function() {
                updateMainCheckbox();
            });

            // Delete button functionality
            $('.btnDelete').click(function(e) {
                e.preventDefault();
                var id_to_be_deleted = $(this).attr("id");

                deleteFailedImportData(id_to_be_deleted);
            });

            $('#bulkDeleteButton').click(function(e) {
                e.preventDefault();
                var selectedIds = $('input[id^="data_check_"]:checked').map(function() {
                    return $(this).val();
                }).get();

                deleteFailedImportData(selectedIds);
            });

            // Replace button functionality
            $('.btnReplace').click(function(e) {
                e.preventDefault();

                var id = $(this).attr('id');
                var updatedData = $(this).data('updated');

                try {
                    $('#failed_import_data_id').val(id);

                    // Populate modal with data
                    populateReplaceModal(updatedData, id);

                    // Show modal
                    var modal = new bootstrap.Modal(document.getElementById('replaceModal'));
                    modal.show();

                } catch (error) {
                    console.error('Error parsing replace data:', error);
                    alert('Error loading replace data. Please try again.');
                }
            });

            $('.download-btn').on('click', function() {
                var $btn = $(this);
                var importStatusId = $btn.data('import-status-id');

                // Jika sedang loading, return
                if ($btn.prop('disabled')) return;

                // Set loading state
                $btn.prop('disabled', true)
                    .find('.btn-text')
                    .html('<span class="spinner-border spinner-border-sm me-2"></span>Downloading...');

                // Download via AJAX
                $.ajax({
                    url: "{{ route('failed_import_data.download', '') }}/" + importStatusId,
                    type: 'POST',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'import_status_id': importStatusId
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data) {
                        // Create download link
                        var blob = new Blob([data]);
                        var url = window.URL.createObjectURL(blob);
                        var a = $('<a>', {
                            href: url,
                            download: '[DATA GAGAL] {{ $importStatus->name }}.csv'
                        }).appendTo('body');

                        a[0].click();
                        a.remove();
                        window.URL.revokeObjectURL(url);

                        // Reset button after 1 second
                        setTimeout(function() {
                            $btn.prop('disabled', false)
                                .find('.btn-text')
                                .html('Download Failed Data');
                        }, 1000);
                    },
                    error: function() {
                        alert('Download gagal. Silakan coba lagi.');
                        $btn.prop('disabled', false)
                            .find('.btn-text')
                            .html('Download Failed Data');
                    }
                });
            });
        });

        function deleteFailedImportData(ids) {
            // Check if ids is an array or a single id
            if (!Array.isArray(ids)) {
                ids = [ids]; // Convert single id to array
            }

            $.ajax({
                type: "POST",
                url: "{{ route('failed_import_data.delete') }}",
                data: {
                    id_to_be_deleted: ids,
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

        // Function to initialize tooltips
        function initializeTooltips() {
            // Dispose existing tooltips first
            $('[data-bs-toggle="tooltip"]').tooltip('dispose');

            // Initialize new tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    html: true,
                    trigger: 'hover focus'
                });
            });
        }

        // Function to update main checkbox based on individual checkboxes
        function updateMainCheckbox() {
            var totalCheckboxes = $('input[id^="data_check_"]').length;
            var checkedCheckboxes = $('input[id^="data_check_"]:checked').length;

            if (checkedCheckboxes === 0) {
                $('#mainCheck').prop('checked', false).prop('indeterminate', false);
            } else if (checkedCheckboxes === totalCheckboxes) {
                $('#mainCheck').prop('checked', true).prop('indeterminate', false);
            } else {
                $('#mainCheck').prop('checked', false).prop('indeterminate', true);
            }
        }

        function populateReplaceModal(updatedData, id) {
            var container = $('#replaceDataContainer');
            container.empty();

            // Store data in confirm button for later use
            $('#confirmReplace').data('current-id', id).data('replace-data', updatedData);

            // Loop through each table in the updated data
            Object.keys(updatedData).forEach(function(tableName) {
                var tableData = updatedData[tableName];
                var formattedTableName = formatTableName(tableName);

                // Add table title
                container.append('<div class="table-title">' + formattedTableName + '</div>');

                // Create table for updates
                if (tableData.update && Object.keys(tableData.update).length > 0) {
                    var tableHtml = '<table class="replace-table">';
                    tableHtml += '<thead><tr><th>Kolom</th><th>Value</th></tr></thead>';
                    tableHtml += '<tbody>';

                    Object.keys(tableData.update).forEach(function(column) {
                        var value = tableData.update[column];
                        var formattedColumn = formatColumnName(column);

                        tableHtml += '<tr>';
                        tableHtml += '<td>' + formattedColumn + '</td>';
                        tableHtml += '<td>' + formatValue(value) + '</td>';
                        tableHtml += '</tr>';
                    });

                    tableHtml += '</tbody></table>';
                    container.append(tableHtml);
                }
            });
        }

        function formatColumnName(columnName) {
            return columnName
                .replace(/([A-Z])/g, ' $1') // Add space before capital letters
                .replace(/_/g, ' ') // Replace underscores with spaces
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ')
                .trim();
        }

        // Function to format values
        function formatValue(value) {
            if (value === null || value === undefined) {
                return '<em class="text-muted">null</em>';
            }

            if (typeof value === 'boolean') {
                return value ? '<span class="badge bg-success">true</span>' : '<span class="badge bg-danger">false</span>';
            }

            if (typeof value === 'object') {
                return '<code>' + JSON.stringify(value) + '</code>';
            }

            return String(value);
        }

        function formatTableName(tableName) {
            return tableName
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }
    </script>
@endsection
