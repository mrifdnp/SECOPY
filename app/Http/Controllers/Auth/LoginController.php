<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        logout as performLogout;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request)
    {
        $this->performLogout($request);
        return redirect('/login');
    }

    public function showLoginForm()
    {
        $data['setting_theme'] = DB::table('settings')
            ->where('key', 'theme')
            ->first();

        return view('auth.login', $data);
    }

    public function loginCustom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        } else {
            $tmpUser = User::where([
                'email' => $request->email,
            ])->first();

            if ($tmpUser) {
                if (Auth::attempt([
                    'email'    => $request->email,
                    'password' => $request->password,
                ])) {
                    $user                = $tmpUser;
                    $user->last_login_at = date('Y-m-d H:i:s');
                    $user->last_login_ip = $request->ip();
                    $user->save();

                    $default_role = Role::where('id', $user->default_role)
                        ->first(['id', 'name']);

                    session()->put('current_role', $default_role);

                    $userStoreIdList = json_decode($user->store_id ?? '[]', true) ?: [];
                    $firstUserStoreId = $userStoreIdList[0] ?? null;

                    // Set default store aktif user login
                    $defaultStore = $firstUserStoreId
                        ? Store::whereNull('deleted_at')->find($firstUserStoreId)
                        : null;

                    session()->forget('store');
                    
                    if ($defaultStore) {
                        session(['store' => ['id' => (string) $defaultStore->id, 'name' => $defaultStore->name]]);
                    }

                    return redirect()->route('home');
                }

                return redirect()->back()->withErrors(['email' => 'The credentials do not match!']);
            }

            return redirect()->back()->withErrors(['email' => 'We cannot find your account!']);
        }
    }
}
