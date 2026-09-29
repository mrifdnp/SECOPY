<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Core\FrontendController;
use App\Providers\RouteServiceProvider;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class RegisterController extends FrontendController
{
    // function __construct()
    // {
    //     parent::__construct();
    // }

    public function index()
    {
        return view('frontend.pages.register', $this->data);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['required', 'date'],
            'postal_code' => ['required', 'string', 'max:20'],
            'sub_district' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'city_regency' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'full_location' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $customer = Customer::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'birthday' => $request->date_of_birth,
            'postcode' => $request->postal_code,
            'sub_district' => $request->sub_district,
            'district' => $request->district,
            'city' => $request->city_regency,
            'province' => $request->province,
            'address' => $request->full_location,
            'onboarding_channel' => 'ecommerce',
        ]);

        Auth::guard('customer')->login($customer);

        // === Pindahkan cart lama kalau ada ===
        $customerId = $customer->id;
        $oldCartKey = 'cart_';
        $newCartKey = 'cart_' . $customerId;

        if (Session::has($oldCartKey)) {
            $cart = Session::get($oldCartKey, []);
            Session::put($newCartKey, $cart);
            Session::forget($oldCartKey);
        }

        return redirect()->intended(route('frontend.index'))
            ->with('success', 'Akun berhasil dibuat dan login otomatis!');
    }

    public function searchDestination(Request $request)
    {
        $search = $request->input('search');

        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
            'search' => $request->input('search'),
            'limit' => 10,
            'offset' => 0
        ]);

        return $response->json();
    }

}