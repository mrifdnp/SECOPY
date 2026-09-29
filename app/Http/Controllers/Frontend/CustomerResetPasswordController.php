<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Core\FrontendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CustomerResetPasswordController extends FrontendController
{
    public function showResetForm(Request $request, $token = null)
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('frontend.index');
        }
        $data = $this->data ?? [];

        return view('frontend.template.reset')->with(array_merge($data, [
            'token' => $token,
            'email' => $request->email,
        ]));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($customer, $password) {
                $customer->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('loginForm.customer')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

}
