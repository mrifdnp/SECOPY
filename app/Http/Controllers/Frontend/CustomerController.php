<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Core\FrontendController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SalesTransaction;

class CustomerController extends FrontendController
{
    public function index()
    {
        $customerId = Auth::guard('customer')->id();
        
        $this->data['orderHistory'] = SalesTransaction::where('customer_id', $customerId)
            ->withSum('items', 'qty')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.pages.customer.account', $this->data);
    }

    public function profile()
    {
        $this->data['customer'] = Auth::guard('customer')->user();
        return view('frontend.pages.customer.profile', $this->data);
    }

    public function updateProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();

    Customer::where('id', $customer->id)->update([
        'email'      => $request->email,
        'first_name' => $request->first_name,
        'last_name'  => $request->last_name,
        'phone'      => $request->phone_full, // full number +62
        'birthday'   => $request->date_of_birth,
    ]);

    return back()->with('success', 'Profile updated successfully.');
    }

    public function address()
    {
        $this->data['customer'] = Auth::guard('customer')->user();
        return view('frontend.pages.customer.address', $this->data);
    }

    public function updateAddress(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        Customer::where('id', $customer->id)->update([
        'postcode'      => $request->postal_code,
        'sub_district' => $request->sub_district,
        'district'  => $request->district,
        'city'      => $request->city,
        'province'   => $request->province,
        'address'   => $request->full_location,
    ]);
    
    return back()->with('success', 'Profile updated successfully.');
    }

    public function change_password_form()
    {
        // $this->data['customer'];
        return view('frontend.pages.customer.password', $this->data);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
        'old-password'     => 'required',
        'new-password'     => 'required|min:6',
        'confirm-password' => 'required|same:new-password',
    ], [
        'old-password.required'     => 'Password lama wajib diisi',
        'new-password.required'     => 'Password baru wajib diisi',
        'new-password.min'          => 'Password baru minimal 6 karakter',
        'confirm-password.required' => 'Konfirmasi password wajib diisi',
        'confirm-password.same'     => 'Password baru dan konfirmasi tidak sama'
    ]);

    $customer = Auth::guard('customer')->user();

    // cek password lama
    if (!Hash::check($request->input('old-password'), $customer->password)) {
        return back()->withErrors(['old-password' => 'Password lama salah']);
    }

    // update password
    Customer::where('id', $customer->id)->update([
        'password' => Hash::make($request->input('new-password')),
    ]);

    return redirect()->route('customer.changePassword')
        ->with('success', 'Password berhasil diperbarui.');
    }

   public function order_history()
    {
        $customerId = Auth::guard('customer')->id();

        $upcomingStatuses = ['pending', 'paid', 'shipping'];
        $this->data['upcomingOrders'] = SalesTransaction::where('customer_id', $customerId)
            ->whereIn('payment_status', $upcomingStatuses)
            ->with(['items.productDetail.product', 'items.productDetail.color', 'items.productDetail.variant'])
            ->withSum('items', 'qty')
            ->orderBy('created_at', 'desc')
            ->get();

        $previousStatuses = ['completed', 'failed', 'canceled'];
        $this->data['previousOrders'] = SalesTransaction::where('customer_id', $customerId)
            ->whereIn('payment_status', $previousStatuses)
            ->with(['items.productDetail.product', 'items.productDetail.color', 'items.productDetail.variant'])
            ->withSum('items', 'qty')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.pages.customer.order-history', $this->data);
    }


    public function wishlist()
    {
        return view('frontend.pages.customer.wishlist', $this->data);
    }
}