<?php

namespace App\Http\Controllers;

use App\Models\DepositProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class DepositProfileController extends Controller
{
    public function deposit_profile()
    {
        return Inertia::render('Settings/DepositProfile/DepositProfile', [
            'profileCounts' => DepositProfile::count(),
        ]);
    }

    public function addDepositProfile(Request $request)
    {
        Validator::make($request->all(), [
            'name' => ['required'],
            'account_number' => ['required'],
            'bank_name' => ['required_if:type,bank'],
            'bank_branch' => ['required_if:type,bank'],
            'crypto_tether' => ['required_if:type,crypto'],
            'crypto_network' => ['required_if:type,crypto'],
            'currency' => ['required'],
        ])->setAttributeNames([
            'name' => trans('public.name'),
            'account_number' => trans('public.account_number'),
            'bank_name' => trans('public.bank_name'),
            'bank_branch' => trans('public.bank_branch'),
            'crypto_tether' => trans('public.tether'),
            'crypto_network' => trans('public.network'),
            'currency' => trans('public.currency'),
        ])->validate();

        $currency = $request->currency;

        DepositProfile::create([
            'type' => $request->type,
            'name' => $request->name,
            'account_number' => $request->account_number,
            'bank_name' => $request->bank_name,
            'bank_branch' => $request->bank_branch,
            'crypto_tether' => $request->crypto_tether,
            'crypto_network' => json_encode($request->crypto_network) ?? null,
            'country_id' => $request->type === 'bank' ? $currency['id'] : null,
            'currency' => $request->type === 'bank' ? $currency['currency'] : $currency,
            'edited_by' => \Auth::id(),
        ]);

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_create_deposit_profile_success'),
            'type' => 'success',
        ]);
    }

    public function getDepositProfileData()
    {
        $deposit_profiles = DepositProfile::latest()->get();

        return response()->json($deposit_profiles);
    }

    public function updateDepositProfileStatus(Request $request)
    {
        $deposit_profile = DepositProfile::find($request->id);

        $deposit_profile->status = $deposit_profile->status == 'active' ? 'inactive' : 'active';
        $deposit_profile->save();

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => $deposit_profile->status == 'active' ? trans("public.toast_deposit_profile_has_activated") : trans("public.toast_deposit_profile_has_deactivated"),
            'type' => 'success',
        ]);
    }
}
