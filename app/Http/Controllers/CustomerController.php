<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCustomerRequest;
use App\Models\Country;
use App\Models\DeliveryAddress;
use App\Models\Group;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\GroupService;
use App\Services\RunningNumberService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
                'group.group:id,name,group_leader_id,color',
                'group.group.group_leader:id,name',
                'upline:id,name,email,upline_id',
            ])
            ->whereNotIn('role', ['super_admin', 'admin'])
            ->latest()
            ->get()
            ->map(function($user) {
                return $user;
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

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'e_wallet',
            'address' => RunningNumberService::getID('e_wallet'),
            'currency' => 'CNY',
            'currency_symbol' => '¥'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'bonus_wallet',
            'address' => RunningNumberService::getID('bonus_wallet'),
            'currency' => 'CNY',
            'currency_symbol' => '¥'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'cash_wallet',
            'address' => RunningNumberService::getID('cash_wallet'),
            'currency' => 'CNY',
            'currency_symbol' => '¥'
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'point_wallet',
            'address' => RunningNumberService::getID('point_wallet'),
            'currency' => 'point',
            'currency_symbol' => 'point'
        ]);

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_create_customer_success_message"),
            'type' => 'success',
        ]);
    }

    public function detail($id_number)
    {
        $user = User::where('id_number', $id_number)
            ->select(['id', 'name', 'id_number', 'role'])
            ->withCount('delivery_addresses')
            ->withCount('wallets')
            ->first();

        $transactionsCount = Transaction::where('user_id', $user->id)
            ->where('status', 'success')
            ->whereIn('transaction_type', ['deposit', 'withdrawal'])
            ->count();

        return Inertia::render('Customer/Listing/Detail/CustomerDetail', [
            'user' => $user,
            'transactionsCount' => $transactionsCount,
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

    public function getWalletData($id_number)
    {
        $user = User::firstWhere('id_number', $id_number);
        $wallets = $user->wallets;

        return response()->json($wallets);
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

    public function upgradeRank(Request $request)
    {
        $user = User::find($request->user_id);
        $user->setting_rank_id = $request->rank['id'];
        $user->save();

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_update_rank_success_message"),
            'type' => 'success',
        ]);
    }

    public function upgradeRole(Request $request)
    {
        $user = User::find($request->user_id);

        if ($request->role != 'user') {
            if ($request->role == 'user') {
                $user->syncRoles('');
            }
            $user->syncRoles($request->role);
        } else {
            $user->syncRoles('');
        }

        $user->role = $request->role;
        $user->save();

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_update_role_success_message"),
            'type' => 'success',
        ]);
    }

    public function getDeliveryAddresses($id_number)
    {
        $user = User::firstWhere('id_number', $id_number);
        $addresses = DeliveryAddress::with('country:id,name,translations')
            ->where('user_id', $user->id)
            ->orderByDesc('default_address')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($addresses);
    }

    public function walletAdjustment(Request $request)
    {
        Validator::make($request->all(), [
            'action' => ['required',],
            'fund_type' => ['required'],
            'amount' => ['required', 'numeric'],
            'remarks' => ['required'],
        ])->setAttributeNames([
            'action' => trans('public.action'),
            'fund_type' => trans('public.fund_type'),
            'amount' => trans('public.amount'),
            'remarks' => trans('public.remarks'),
        ])->validate();

        if (in_array($request->user_role, ['demo_fund', 'special_fund', 'special_demo_fund']) && $request->fund_type == 'real_fund') {
            throw ValidationException::withMessages(['fund_type' => trans('public.real_fund_is_unavailable_for_this_user')]);
        }

        $action = $request->action;
        $amount = $request->amount;
        $wallet = Wallet::find($request->wallet_id);

        if ($action == 'withdrawal' && $wallet->balance < $amount) {
            throw ValidationException::withMessages(['amount' => trans('public.insufficient_balance')]);
        }

        $new_balance = $action == 'withdrawal' ? $wallet->balance - $amount : $wallet->balance + $amount;

        Transaction::create([
            'user_id' => $wallet->user_id,
            'category' => $wallet->type,
            'transaction_type' => $action,
            'from_wallet_id' => $action == 'withdrawal' ? $wallet->id : null,
            'to_wallet_id' => $action == 'deposit' ? $wallet->id : null,
            'transaction_number' => RunningNumberService::getID('transaction'),
            'amount' => $amount,
            'from_currency' => $wallet->currency,
            'to_currency' => $wallet->currency,
            'transaction_amount' => $amount,
            'fund_type' => $request->fund_type,
            'old_wallet_amount' => $wallet->balance,
            'new_wallet_amount' => $new_balance,
            'status' => 'success',
            'remarks' => $request->remarks,
            'approval_at' => now(),
            'handle_by' => Auth::id(),
        ]);

        $wallet->balance = $new_balance;
        $wallet->save();

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_wallet_adjustment_success_message"),
            'type' => 'success',
        ]);
    }

    public function getCustomerOverview(Request $request)
    {
        // current month
        $endOfMonth = \Illuminate\Support\Carbon::now()->endOfMonth();

        // last month
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $userQuery = User::query();

        // current month join user
        $current_month_join_user = (clone $userQuery)
            ->whereDate('created_at', '<=', $endOfMonth)
            ->count();

        // last month join user
        $last_month_join_user =  (clone $userQuery)
            ->whereDate('created_at', '<=', $endOfLastMonth)
            ->count();

        // comparison of success deposit vs last month
        $last_month_join_user_comparison = $current_month_join_user - $last_month_join_user;

        $verified_user = (clone $userQuery)
            ->where('kyc_status', 'verified')
            ->count();

        $unverified_user = (clone $userQuery)
            ->where('kyc_status', 'unverified')
            ->count();

        return response()->json([
            'currentJoinUser' => $current_month_join_user,
            'lastMonthJoinUserComparison' => $last_month_join_user_comparison,
            'verifiedUser' => $verified_user,
            'unverifiedUser' => $unverified_user,
        ]);
    }

    public function getRecentTransactionData($id_number)
    {
        $user = User::firstWhere('id_number', $id_number);

        $transactionQuery = Transaction::where('user_id', $user->id)
            ->where('status', 'success');

        $total_deposit = (clone $transactionQuery)
            ->where('transaction_type', 'deposit')
            ->sum('amount');

        $total_withdrawal = (clone $transactionQuery)
            ->where('transaction_type', 'withdrawal')
            ->sum('amount');

        $transaction = (clone $transactionQuery)
            ->whereIn('transaction_type', ['deposit', 'withdrawal'])
            ->orderByDesc('approval_at')
            ->get();

        return response()->json([
            'total_deposit' => $total_deposit,
            'total_withdrawal' => $total_withdrawal,
            'transaction' => $transaction,
        ]);
    }
}
