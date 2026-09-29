<?php
namespace App\Http\Controllers\Master;

use App\Exports\PartyExport;
use App\Helpers\RuleHelper;
use App\Http\Controllers\Core\BackendController;
use App\Models\Module;
use App\Models\Party;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class PartyController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title']  = "Party Page";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        RuleHelper::UserHasPermission('access');

        $associatedStoreIds = Party::whereNotNull('store_id')->whereNull('deleted_at')->distinct()->pluck('store_id')->toArray();
        $availableStores    = Store::whereNull('deleted_at')->whereNotIn('id', $associatedStoreIds)->get();

        $this->data['parties_count'] = Party::whereNotNull('deleted_at')->count();

        $this->data['url']     = 'party';
        $tgl                   = null;
        $dateNow               = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        $this->data['stores'] = $availableStores;

        return view('backend.pages.party.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        RuleHelper::UserHasPermission('create');

        $tgl                   = null;
        $dateNow               = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.party.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        RuleHelper::UserHasPermission('create');

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string',
            'telephone'  => 'nullable|string',
            'tags_value' => 'nullable|string',
            'store_id'   => 'nullable|exists:stores,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $party             = new Party();
        $party->id         = ucwords($request->id);
        $party->name       = ucwords($request->name);
        $party->telephone  = $request->telephone;
        $party->tags       = json_encode(explode(',', $request->tags_value));
        $party->store_id   = $request->store_id;
        $party->created_by = Auth::user()->email;
        $simpan            = $party->save();

        if ($simpan) {
            Session::flash('flash_message', 'New record created successfully');

            return redirect()->route('party.create');
        } else {
            Session::flash('error_message', 'New record failed to created');

            return redirect()->route('party.create');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        RuleHelper::UserHasPermission('read');
        $party = Party::findOrFail($id);

        $this->data['party'] = $party;

        return view('backend.pages.party.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        RuleHelper::UserHasPermission('update');
        $party = Party::findOrFail($id);

        $this->data['party'] = $party;

        return response()->json([
            'status' => 'success',
            'data'   => $party,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        RuleHelper::UserHasPermission('update');
        $party     = Party::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string',
            'telephone'  => 'nullable|string',
            'tags_value' => 'nullable|string',
            'store_id'   => 'nullable|exists:stores,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $party->id         = ucwords($request->id);
        $party->name       = ucwords($request->name);
        $party->telephone  = $request->telephone;
        $party->tags       = json_encode(explode(',', $request->tags_value));
        $party->store_id   = $request->store_id;
        $party->updated_by = Auth::user()->email;
        $simpan            = $party->save();

        if ($simpan) {
            Session::flash('flash_message', 'The record updated successfully');

            return redirect()->route('party.edit', $id);
        } else {
            Session::flash('error_message', 'The record failed to updated');

            return redirect()->route('party.edit', $id);
        }
    }

    public function trash()
    {
        RuleHelper::UserHasPermission('trash');

        $this->data['parties_count'] = DB::table('parties')->whereNotNull('deleted_at')->count();
        return view('backend.pages.party.trash', $this->data);
    }

    public function get_datatable()
    {
        RuleHelper::UserHasPermission('access');

        $data = Party::with('store')
            ->select(['id',
                'name',
                'telephone',
                'tags',
                'store_id',
                'created_at',
                'deleted_at',
                'deleted_by',
                'updated_at',
            ])->whereNull('deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                $checkbox = " <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
                return $checkbox;
            })
            ->editColumn('tags', function ($data) {
                $tags = json_decode($data->tags);
                if (is_array($tags)) {
                    $tagHtml = '';
                    foreach ($tags as $tag) {
                        $tagHtml .= '<span class="badge bg-info me-1 mb-1">' . htmlspecialchars($tag) . '</span>';
                    }
                    return $tagHtml;
                } else {
                    return '';
                }
            })
            ->addColumn('store_name', function ($data) {
                return $data?->store?->name ?? '-';
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->addColumn('action', function ($data) {
                $btnEdit   = '';
                $btnDelete = '';

                if (RuleHelper::UserHasPermission('update', false) == true) {
                    $btnEdit = <<<HTML
                        <a href='#' class='btn btn-sm btn-soft-secondary btnEdit' data-id='{$data->id}' data-tags='{$data->tags}' data-store-id='{$data?->store?->id}' data-store-name='{$data?->store?->name}' title='Edit'><i class='las la-edit '></i></a>
                    HTML;
                }

                if (RuleHelper::UserHasPermission('delete', false) == true) {
                    $btnDelete = <<<HTML
                        <a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='{$data->id}' title='Delete'><i class='las la-trash '></i></a>
                    HTML;
                }

                $viewRoute = route('party.show', ['party' => $data->id]);
                $btnView   = <<<HTML
                    <a href='{$viewRoute}' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>
                HTML;

                return $btnView . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('name') && ! is_null(request('name'))) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }if (request()->has('tags') && ! is_null(request('tags'))) {
                    $query->where('tags', 'like', "%" . request('tags') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'tags'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        RuleHelper::UserHasPermission('trash');

        $data = Party::select(['id',
            'name',
            'telephone',
            'tags',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at',
        ])->whereNotNull('deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('action', function ($data) {
                $btnView    = "<a href='" . route('party.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
                $btnDelete  = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='" . $data->id . "'><i class='ri-restart-line '></i> Restore</a>";
                return $btnView . " " . $btnRestore . " " . $btnDelete;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('name') && ! is_null(request('name'))) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }if (request()->has('tags') && ! is_null(request('tags'))) {
                    $query->where('tags', 'like', "%" . request('tags') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        RuleHelper::UserHasPermission('delete');

        $table          = "parties";
        $column_visible = "name";
        $route          = "party.destroy";

        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy($delete_type)
    {
        RuleHelper::UserHasPermission('delete');
        $user              = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index             = 0;
        $redirect_url      = "";
        foreach ($dependencies_data as $row) {
            if ($row['delete_access_type'] != "restrict" && $delete_type == "harddelete") {
                $delete_data = array_reverse($row['data']);
                foreach ($delete_data as $delete_data_row) {
                    DB::table($delete_data_row['table'])
                        ->where($delete_data_row['field'], $delete_data_row['value'])
                        ->delete();
                }

                $redirect_url = "party/trash";
                $party        = Party::findOrFail($row['id']);

                $party->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "party";

                DB::table($row['table'])
                    ->where('id', $row['id'])
                    ->update(['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => $user->email, 'restored_at' => null, 'restored_by' => null]);

                $index++;
            }
        }

        Session::flash('flash_message', $index . ' Record[s] deleted successfully');

        return redirect($redirect_url);
    }

    public function restore(Request $request)
    {
        RuleHelper::UserHasPermission('trash');

        if ($request->id_to_be_restored == "") {
            $request->id_to_be_restored = [];
        }

        $data = DB::table('parties')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index']       = 0;
        $this->data['data']        = $data;
        $this->data['route']       = 'party.restore_action';
        $this->data['column_name'] = 'name';

        Session::flash('dependencies_data', $data);

        return view('backend.includes.restore_modal', $this->data);
    }

    public function upload(Request $request)
    {

    }

    public function restore_action(Request $request)
    {
        RuleHelper::UserHasPermission('trash');

        $user              = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index             = 0;
        foreach ($dependencies_data as $row) {
            Party::where('id', $row->id)
                ->update(
                    ['deleted_at' => null, 'deleted_by' => null,
                        'restored_at' => date('Y-m-d H:i:s'),
                        'restored_by' => $user->email]);
            $index++;
        }

        Session::flash('flash_message', $index . ' Record[s] restored successfully');

        return redirect('party/trash');
    }

    public function get_template(Request $request)
    {
        $data              = [];
        $columns           = Schema::getColumnListing($request->tabel);
        $columnsRemanen    = DB::select(DB::raw('SHOW COLUMNS FROM `' . $request->tabel . '`'));
        $max               = (int) count($columns) - 8;
        $max2              = (int) count($columnsRemanen) - 8;
        $columnDepedencies = [];

        $columnExtraAttribute = [];
        for ($i = 1; $i < $max2; $i++) {
            array_push($columnDepedencies, $columnsRemanen[$i]->Field);
            if ($columnsRemanen[$i]->Type == 'tinyint(1)') {
                array_push($columnExtraAttribute, [0, 1]);
            } elseif (strpos($columnsRemanen[$i]->Type, 'enum') !== false) {
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', explode('enum', $columnsRemanen[$i]->Type)[1]);
                $tmp   = [];
                for ($j = 0; $j < count($split); $j++) {
                    if ($split[$j] != "") {
                        array_push($tmp, $split[$j]);
                    }
                }
                array_push($columnExtraAttribute, $tmp);
            } elseif ($columnsRemanen[$i]->Type == 'date') {
                array_push($columnExtraAttribute, 'format : 2021-09-30');
            } elseif ($columnsRemanen[$i]->Type == 'datetime') {
                array_push($columnExtraAttribute, 'format : 2021-09-30 23:59:00');
            } elseif ($columnsRemanen[$i]->Type == 'double(8,2)' || $columnsRemanen[$i]->Type == 'decimal(8,2)' || $columnsRemanen[$i]->Type == 'float(8,2)') {
                array_push($columnExtraAttribute, 'format decimal with dots (.), ex : 17.05');
            } elseif ($this->isFK($request->tabel, $columnsRemanen[$i]->Field)) {
                $primaryTable   = explode('_', $columnsRemanen[$i]->Field)[0];
                $tabelPrimer    = DB::table($primaryTable)->get()->toArray();
                $allColumns     = Schema::getColumnListing($primaryTable);
                $primaryColumns = [$allColumns[0], $allColumns[1]];
                $tmp            = [];
                foreach ($tabelPrimer as $key => $tp) {
                    $newTp = get_object_vars($tp);
                    $field = '(' . $newTp[$primaryColumns[1]] . ') ' . $newTp[$primaryColumns[0]];
                    array_push($tmp, $field);
                }
                array_push($columnExtraAttribute, $tmp);
            } else {
                array_push($columnExtraAttribute, null);
            }
        }
        $data['columnDepedencies']    = $columnDepedencies;
        $data['columnExtraAttribute'] = $columnExtraAttribute;
        session()->put('columnExportData', $data);

        return true;
    }

    public function download()
    {
        $columnExportData = session()->get('columnExportData');
        session()->forget('columnExportData');

        return Excel::download(new PartyExport($columnExportData['columnDepedencies'], $columnExportData['columnExtraAttribute']), 'parties.xlsx');
    }

    private function isFK(string $table, string $column): bool
    {
        $fkColumns = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys($table);

        return collect($fkColumns)->map(function ($fkColumn) {
            return $fkColumn->getColumns();
        })->flatten()->contains($column);
    }

    private function formatDate($input, $output, $type)
    {
        preg_match_all('!\d+!', $input, $output);
        if ($type == 1) {
            return $output[0][2] . '-' . $output[0][0] . '-' . $output[0][1];
        } elseif ($type == 2) {
            return $output[0][1] . '/' . $output[0][0] . '/' . $output[0][2];
        } else {
            return $output[0][1] . '/' . $output[0][2] . '/' . $output[0][0];
        }
    }

    private function formatDateTime($input)
    {
        $data = explode(' ', $input);

        return $data[0];
    }

    private function rupiah($angka)
    {
        $hasil = number_format($angka, 0, ',', '.');
        return $hasil;
    }

}
