<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Core\FrontendController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class LoginController extends FrontendController
{
    public function index(Request $request)
    {
        $this->data['defaultEmail'] = $request->input('email', '');
        return view('frontend.pages.login', $this->data);
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return back()->withErrors([
                'email' => 'Email tidak ditemukan',
            ])->onlyInput('email');
        }

        if (Auth::guard('customer')->attempt($credentials)) {
            $request->session()->regenerate();

            if (session()->has('url.intended')) {
                $intendedUrl = session('url.intended');

                $customerId = Auth::guard('customer')->id();
                $oldCartKey = 'cart_';
                $newCartKey = 'cart_' . $customerId;

                $cart = Session::get($oldCartKey, []);

                Session::put($newCartKey, $cart);

                Session::forget($oldCartKey);
            } 
            return redirect()->intended(route('frontend.index'))
                ->with('success', 'Login berhasil, selamat datang!');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('loginForm.customer');
    }
}
