{{-- <div id="modalDelete" class="modal fade bs-example-modal-center" tabindex="-1" aria-labelledby="mySmallModalLabel" aria-modal="true" role="dialog" style="display: block;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <lord-icon src="https://cdn.lordicon.com/hrqwmuhr.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:120px;height:120px">
                </lord-icon>
                <div class="mt-4">
                    <h4 class="mb-3">Oops something went wrong!</h4>
                    <p class="text-muted mb-4"> The transfer was not successfully received by us. the email of the recipient wasn't correct.</p>
                    <div class="hstack gap-2 justify-content-center">
                        <button type="button" class="btn-soft-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="javascript:void(0);" class="btn btn-danger">Try Again</a>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> --}}


<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title"><i class="fa fa-info-circle"> </i> Delete Confirmation</h4>
</div>
<form action="{{route($route,$delete_type)}}" method="post">
@method('DELETE')
@csrf

@if (count($data) > 0)
    <div class="modal-body">
        <div class="box-body index-padding">
			<table class="table table-striped">
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
                                   // echo ' <span class="badge bg-info" style="font-weight:400"> '.$dependencies_data_row['table'].' </span>';
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
        @if ($buttonDel=="true")
        <button type="submit" class="btn btn-outline pull-left">Delete</button>
        @endif
        <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
    </div>
@else
    <div class="modal-body">
        <div class="box-body index-padding">
            <span>Please select your data row.</span>
        </div><!-- /.box-body -->
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
    </div>
@endif
</form>
