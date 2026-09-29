<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use App\Helpers\IndonesiaitLib;
use App\Models\Module;
use Request as Req;
use Illuminate\Support\Facades\File;
use Image;
use App\Models\Role;

class ThemeController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['titleHeader'] = Module::where('url', '=',Req::segment(1))->first(['icon', 'name', 'url']);
        $this->data['page_title'] = "Theme Page";
    }

    public function index()
    {
        $this->data['user'] = Auth::user();
        $themes = Setting::where('key','theme')->first();
        if (!$themes) {
                Setting::create([
                    'key' => 'theme',
                    'value' => '[]'
                ]);
                $themes = Setting::where('key','theme')->first();
        }
        $this->data['currentTheme'] = json_decode($themes->value);

        return view('backend.pages.theme.theme_edit',$this->data);

    }

    public function change_layout(Request $request)
    {
        if ($request->roleName) {
            $roleName = $request->roleName;
            $arr = [];
            $tmpArr = [];
            $role = Role::where('name',$roleName)->first();
            if (json_decode($role->theme_setting)) {
                $arr = json_decode($role->theme_setting);
                $key = $request->attr;
                $arr->$key = $request->val;
                $role->theme_setting = json_encode($arr);
                $role->save();
            }else {
                $arr[$request->attr] = $request->val;
                $role->theme_setting = json_encode($arr);
                $role->save();
            }
        } else {
            $arr = [];
            $tmpArr = [];
            $setting = Setting::where('key','theme')->first();
            if (!$setting) {
                Setting::create([
                    'key' => 'theme',
                    'value' => '[]'
                ]);

                $setting = Setting::where('key','theme')->first();
            }
            if (json_decode($setting->value)) {
                $arr = json_decode($setting->value);
                $key = $request->attr;
                $arr->$key = $request->val;
                $setting->value = json_encode($arr);
                $setting->save();
            }else {
                $arr[$request->attr] = $request->val;
                $setting->value = json_encode($arr);
                $setting->save();
            }
        }

        return json_encode(array('data' => 1));
    }

    public function change_image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo_light' => 'nullable|image|mimes:png|max:2000',
            'logo_light_sm' => 'nullable|image|mimes:png|max:2000',
            'logo_dark_sm' => 'nullable|image|mimes:png|max:2000',
            'logo_dark' => 'nullable|image|mimes:png|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()
                        ->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        $pathImage = '/build/images/';
        $path = public_path($pathImage);
        if(!File::exists($path)){
            mkdir($path,0777,true);
        }

        if ($request->logo_light) {
            $fotoLama = $path.'logo-light.png';
            if (file_exists($fotoLama)) {
                unlink($fotoLama);
            }
            $fotoBaru = $request->file('logo_light');
            $imageName = 'logo-light.png';
            $fotoBaru->move($path, $imageName);
        }

        if ($request->logo_light_sm) {
            $fotoLama = $path.'logo-sm.png';
            if (file_exists($fotoLama)) {
                unlink($fotoLama);
            }
            $fotoBaru = $request->file('logo_light_sm');
            $imageName = 'logo-sm.png';
            $fotoBaru->move($path, $imageName);
        }

        if ($request->logo_dark) {
            $fotoLama = $path.'logo-dark.png';
            if (file_exists($fotoLama)) {
                unlink($fotoLama);
            }
            $fotoBaru = $request->file('logo_dark');
            $imageName = 'logo-dark.png';
            $fotoBaru->move($path, $imageName);
        }

        if ($request->logo_dark_sm) {
            $fotoLama = $path.'logo-sm.png';
            if (file_exists($fotoLama)) {
                unlink($fotoLama);
            }
            $fotoBaru = $request->file('logo_dark_sm');
            $imageName = 'logo-sm.png';
            $fotoBaru->move($path, $imageName);
        }

        \Session::flash('flash_message', 'Logo is updated successfully!');

        return redirect()->back();
    }
}
