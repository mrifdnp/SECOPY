<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Core\FrontendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;

class CustomerForgotPasswordController extends FrontendController
{
    public function showLinkRequestForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('frontend.index');
        }
        return view('frontend.template.password', $this->data);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('customers')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

}
