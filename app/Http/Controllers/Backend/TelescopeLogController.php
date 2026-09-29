<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\ChangeLog;
use App\Models\Role;
use App\Models\RolePrivilege;
use App\Models\UserPrivilege;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use App\Helpers\IndonesiaitLib;
use Request as Req;

class TelescopeLogController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "System Log Page";
    }

    public function index()
    {
        $this->data["title"]= "System Log Page";

        return view('backend.pages.telescope.index', $this->data);
    }
}
