<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCustomerRequest;
use App\Models\Country;
use App\Models\Group;
use App\Models\Item;
use App\Models\User;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        $users = User::select([
            'id',
            'name',
            'email',
            'username',
            'upline_id',
            'country_id',
            'setting_rank_id',
            'role',
            'id_number',
            'kyc_status',
            'created_at'
        ])
            ->with([
                'country:id,name,emoji',
                'rank:id,rank_name',
                'group.group:id,name,color',
                'upline:id,name,email,upline_id',
            ])
            ->where('role', 'user')
            ->latest()
            ->get()
            ->map(function($user) {
                $data = $user;

                return $data;
            });

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

            if ($upline->group) {
                (new GroupService())->addUserToGroup($upline->group->group_id, $user->id);
                $group_rank_setting = $upline->group->group->group_rank_settings()->first();
                $user->setting_rank_id = $group_rank_setting->id;
            }
        } else {
            (new GroupService())->addUserToGroup(Group::first()->id, $user->id);
        }

        $user->setReferralId();

        $id_no = 'LID' . Str::padLeft($user->id - 1, 6, "0");
        $user->id_number = $id_no;
        $user->save();

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_create_customer_success_message"),
            'type' => 'success',
        ]);
    }

    public function detail($id_number)
    {
        $user = User::where('id_number', $id_number)
            ->select(['id', 'name', 'id_number'])
            ->first();

        return Inertia::render('Customer/Listing/Detail/CustomerDetail', [
            'user' => $user,
        ]);
    }

    public function getUserData($id_number)
    {
        $user = User::with([
            'country:id,name,emoji',
            'rank:id,rank_name',
            'group.group:id,name,color',
            'upline:id,name,email,upline_id',
        ])
            ->where('id_number', $id_number)
            ->first();

        return response()->json($user);
    }

    public function getCustomerContactInfo($id_number)
    {
        $user = User::with('country:id,name,emoji')
            ->select([
                'id', 'name', 'id_number', 'email', 'email_verified_at', 'username', 'dial_code', 'phone', 'country_id', 'referral_code'
            ])
            ->where('id_number', $id_number)
            ->first();

        return response()->json($user);
    }

    public function updateCustomerProfile(Request $request)
    {
        Validator::make($request->all(), [
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'username' => ['required'],
            'country' => ['required'],
            'dial_code' => ['required'],
            'phone' => ['required', 'max:255'],
            'phone_number' => ['required', 'max:255', Rule::unique(User::class)->ignore($request->user_id)],
        ])->setAttributeNames([
            'name' => trans('public.full_name'),
            'username' => trans('public.username'),
            'country' => trans('public.country'),
            'dial_code' => trans('public.phone_number'),
            'phone' => trans('public.phone_number'),
            'phone_number' => trans('public.phone_number'),
        ])->validate();

        $user = User::find($request->user_id);
        $dial_code = $request->dial_code;
        $country = Country::find($request->country['id']);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'dial_code' => $dial_code['phone_code'],
            'phone' => $request->phone,
            'phone_number' => $request->phone_number,
            'country_id' => $country->id,
            'nationality' => $country->nationality,
        ]);

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_update_customer_success_message"),
            'type' => 'success',
        ]);
    }
}
