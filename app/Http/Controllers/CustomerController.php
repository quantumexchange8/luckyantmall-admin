<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCustomerRequest;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        $users = User::with([
            'country:id,name,emoji',
            'rank:id,name'
        ])
            ->latest()
            ->get();

        return response()->json([
            'users' => $users,
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

        if ($request->upline) {
            $upline_id = $request->upline['id'];
            $upline = User::find($upline_id);

            if(empty($upline->hierarchyList)) {
                $hierarchyList = "-" . $upline_id . "-";
            } else {
                $hierarchyList = $upline->hierarchyList . $upline_id . "-";
            }

            $user->upline_id = $upline_id;
            $user->hierarchyList = $hierarchyList;
        }

        $user->setReferralId();
        $user->assignGroup(1);

        $id_no = 'LID' . Str::padLeft($user->id - 1, 6, "0");
        $user->id_number = $id_no;
        $user->save();

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_create_customer_success_message"),
            'type' => 'success',
        ]);
    }
}
