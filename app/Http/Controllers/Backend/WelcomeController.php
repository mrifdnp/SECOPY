<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use App\Models\ChangeLog;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class WelcomeController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
        $this->data['page_title']  = "Welcome";
    }

    public function index()
    {
        $this->data['user'] = Auth::user();

        $greating_morning = [
            "Semoga pekerjaanmu di lancarkan untuk hari ini.",
            "Jangan lupa berdoa untuk memulai harimu hari ini.",
            "Semoga hari ini kamu menjadi pribadi yang produktif.",
        ];

        $greating_afternoon = [
            "Tetaplah semangat untuk menjalani pekerjaanmu.",
            "Jangan lupa makan siang, agar kondisimu tetap fit.",
            "Semangat, Masih ada separuh hari untuk menyelesaikan pekerjaanmu. ",
        ];

        $greating_evening = [
            "Cek kembali cheklist pekerjaanmu hari ini, jangan sampai ada yang kelewat.",
            "Jangan lupa istirahat, kamu sudah bekerja sepanjang hari.",
            "Semoga pekerjaanmu hari ini mendapat banyak keberkahan.",
        ];

        $greating_night = [
            "Tetap semangat lembur ya, semoga kerjaanmu cepat selesai.",
            "Sudah malam, jangan lupa istirahat dan makan saat lembur.",
            "Jangan lupa bersyukur atas produktifitas yang sudah kamu lakukan hari ini.",
        ];

        $datenow     = Carbon::now('Asia/Jakarta');
        $datenow_utc = Carbon::now();

        Carbon::setLocale('id');
        $hour = $datenow->format('H');

        if ($hour >= 6 && $hour <= 10) {
            $greating     = "Selamat Pagi";
            $greating_msg = $greating_morning[rand(0, 2)];
        } elseif ($hour >= 11 && $hour <= 15) {
            $greating     = "Selamat Siang";
            $greating_msg = $greating_afternoon[rand(0, 2)];
        } elseif ($hour >= 16 && $hour <= 18) {
            $greating     = "Selamat Sore";
            $greating_msg = $greating_evening[rand(0, 2)];
        } else {
            $greating     = "Selamat Malem";
            $greating_msg = $greating_night[rand(0, 2)];
        }

        $this->data['date_id']         = $datenow->isoFormat('dddd, D MMMM Y');
        $this->data['date_timeserver'] = $datenow->format('H:i:s');
        $this->data['datenow']         = strtotime($datenow_utc) * 1000;
        $this->data['greating']        = $greating;
        $this->data['greating_msg']    = $greating_msg;
        $this->data['changeLogs']      = ChangeLog::orderBy('created_date', 'desc')->get();

        // dd(session()->all());

        return view('backend.pages.welcome.welcome', $this->data);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'user_name'             => 'required',
            'user_email'            => 'required|email:rfc,dns|unique:users,email,' . $user->id, ',id',
            'user_confirm_password' => 'same:user_password',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user_login = Auth::user();

        if ($request->user_password == "") {
            User::where('id', $user->id)
                ->update([
                    'name'       => $request->user_name,
                    'email'      => $request->user_email,
                    'updated_by' => $user_login->email,
                ]);
        } else {
            User::where('id', $user->id)
                ->update([
                    'name'       => $request->user_name,
                    'email'      => $request->user_email,
                    'password'   => Hash::make($request->user_password),
                    'updated_by' => $user_login->email,
                ]);
        }

        Session::flash('flash_message', 'The record updated successfully');

        return redirect()->route('profile.index');
    }

    public function changeRole($id)
    {
        $selectedRole = DB::table('roles')
            ->where('id', $id)
            ->select(['roles.id', 'roles.name'])
            ->first();

        if ($selectedRole) {
            session()->put('current_role', $selectedRole);
        }

        return redirect()->route('home');
    }
}
