<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use App\Helpers\IndonesiaitLib;
use App\Models\Module;
use Request as Req;

class ProfileController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['titleHeader'] = Module::where('url', '=',Req::segment(1))->first(['icon', 'name', 'url']);
        $this->data['page_title'] = "Edit Profile Page";
    }

    public function index()
    {
        $this->data['user'] = Auth::user();

        return view('backend.pages.profile.profile_edit',$this->data);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'user_email' => 'required|email:rfc,dns|unique:users,email,'.$user->id,',id',
            'user_confirm_password' => 'same:user_password'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        
        $user_login = Auth::user();

        if($request->user_password==""){
            User::where('id', $user->id)
            ->update([
                      'name' => $request->user_name,
                      'email' => $request->user_email,
                      'updated_by' => $user_login->email
            ]);
        }else{
            User::where('id', $user->id)
            ->update([
                      'name' => $request->user_name,
                      'email' => $request->user_email,
                      'password' => Hash::make($request->user_password),
                      'updated_by' => $user_login->email
            ]);
        }

        \Session::flash('flash_message', 'The record updated successfully'); 

        return redirect()->route('profile.index');
    }
}
