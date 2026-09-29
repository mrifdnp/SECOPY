<div class="modal-header p-3 bg-primary bg-gradient">
	{{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> --}}
	<h5 class="modal-title text-white"><i class="las la-info-circle"> </i> Delete Confirmation</h5>
    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>        
</div>
<form action="{{route($route,$delete_type)}}" method="post">
    @csrf
    @method('DELETE')

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
                            echo $row_array[$column_name].'<br>';
                            
                            $buttonDel = "true";
                            $label = "cascade";
                            $caption = 'false';
                            
                            if(count($dependencies_data)>0){
                                foreach($dependencies_data[$index-1]['data'] as $dependencies_data_row){
                                    if($dependencies_data_row['type']=='restrict' && $delete_type=='harddelete'){
                                        $buttonDel = "false";
                                        $label = "restrict";
                                        echo ' <span class="badge bg-info" style="font-weight:700"> '.$dependencies_data_row['table'].'* </span> ';
                                    }else{
                                        echo ' <span class="badge bg-info" style="font-weight:400"> '.$dependencies_data_row['table'].'* </span>';
                                    }
                                    $caption = true;
                                }
                            }

                            if($caption=='true' && $delete_type=='harddelete'){
                                if($label =='restrict'){
                                    echo '<br><span class="lbl-del bg-red"> Sorry, Admin index accept you to delete this data if the data already used on table[s]* above</span>';
                                }else{
                                    echo '<br><span class="lbl-del bg-yellow">WARNING: If you click Delete button, you will lose the data on table[s] above</span> ';
                                }                
                            }

                            if($caption=='true' && $delete_type!='harddelete'){
                                // echo '<br><span class="lbl-del bg-yellow">WARNING: The data already used on table[s] above</span> ';         
                            }
 
                        @endphp
                        
                        </td>
                    </tr>
                @endforeach
            </table>
            <span>Are you sure you want to delete {{ $data->count() }} record(s) above?</span>
        </div>
    </div>

    <div class="modal-footer">        
        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
        @if ($buttonDel=="true")
            <button type="submit" class="btn btn-outline btn-danger pull-left text-light"><i class="las la-trash"></i> Delete</button>
        @endif
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
