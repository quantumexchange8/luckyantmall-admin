<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCustomerRequest;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        return Inertia::render('Customer/Listing/CustomerListing', [
            'customerCounts' => User::count()
        ]);
    }

    public function getCustomersData(Request $request)
    {
        $user = User::with([
            'country:id,name,emoji',
            'rank:id,name'
        ])->get();

        return response()->json([
            'users' => $user,
            'total_users' => User::count()
        ]);
    }

    public function addNewCustomer(AddCustomerRequest $request)
    {
        $dial_code = $request->dial_code;
        $country = Country::find($request->country['id']);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'dial_code' => $dial_code['phone_code'],
            'phone' => $request->phone,
            'phone_number' => $request->phone_number,
            'country_id' => $country->id,
            'nationality' => $country->nationality,
            'password' => Hash::make($request->password),
        ]);

        $user->setReferralId();
        $user->assignGroup(1);

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_create_customer_success_message"),
            'type' => 'success',
        ]);
    }
}
