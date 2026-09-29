<?php

namespace App\Http\Controllers\Frontend\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Customer;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $customer = Customer::where('oauth_provider', 'google')
                ->where('oauth_id', $googleUser->getId())
                ->first();

            if (!$customer && $googleUser->getEmail()) {
                $customer = Customer::where('email', $googleUser->getEmail())->first();

                if ($customer) {
                    $customer->update([
                        'oauth_provider'      => 'google',
                        'oauth_id'            => $googleUser->getId(),
                        'oauth_token'         => $googleUser->token,
                        'oauth_refresh_token' => $googleUser->refreshToken ?? null,
                    ]);
                }
            }

            if (!$customer) {
                $customer = Customer::create([
                    'first_name'          => $googleUser->user['given_name'] ?? '',
                    'last_name'           => $googleUser->user['family_name'] ?? '',
                    'email'               => $googleUser->getEmail(),
                    'oauth_provider'      => 'google',
                    'oauth_id'            => $googleUser->getId(),
                    'oauth_token'         => $googleUser->token,
                    'oauth_refresh_token' => $googleUser->refreshToken ?? null,
                    'password'            => bcrypt(Str::random(16)), // random password biar gak bisa login manual sembarangan
                ]);
            }

            Auth::guard('customer')->login($customer);

            return redirect()->route('frontend.index');
        } catch (\Exception $e) {
            return redirect('/login-customer')->withErrors([
                'msg' => 'Login dengan Google gagal: ' . $e->getMessage()
            ]);
        }
    }

}
