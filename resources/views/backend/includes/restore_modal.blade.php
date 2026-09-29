<div class="modal-header p-3 bg-primary bg-gradient">
	{{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> --}}	
    <h5 class="modal-title text-white"><i class="las la-info-circle"> </i> Restore Confirmation</h5>
    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>  
</div>
<form action="{{route($route)}}" method="post">
    @csrf

    @if (count($data) > 0)
        <div class="modal-body">
            <div class="box-body index-padding">
                <table class="table table-stripped">
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Item</th>
                    </tr>
                    @foreach ($data as $row)
                    <tr>
                        <td>{{++$index}}</td>
                        <td>
                        @php
                            $row_array = (array) $row;
                            echo $row_array[$column_name];
                        @endphp
                        
                        </td>
                    </tr>
                @endforeach
                </table>
                <span>Are you sure you want to restore {{ $data->count() }} record(s) above?</span>
            </div>
        </div>
        <div class="modal-footer">        
            <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>    
            <button type="submit" class="btn btn-outline btn-info pull-left text-light"><i class="las la-trash-restore-alt"></i> Restore</button>                   
        </div>
    @else
        <div class="modal-body">
            <div class="box-body index-padding">
                <span>Please select your data row.</span>
            </div><!-- /.box-body -->
        </div>

        <div class="modal-footer">        
            <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>    
        </div>
    @endif
</form>
