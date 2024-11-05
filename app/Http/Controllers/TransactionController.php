<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function pending_deposit()
    {
        $pendingCounts = Transaction::where('transaction_type', 'deposit')
            ->where('status', 'processing')
            ->count();

        return Inertia::render('Transaction/Pending/Deposit/PendingDeposit', [
            'pendingDepositCounts' => $pendingCounts
        ]);
    }

    public function getRecentApprovals()
    {
        $recentApprovals = Transaction::query()
            ->select(
                'id',
                'transaction_type',
                'transaction_number',
                'approval_at',
                'status',
                'handle_by'
            )->with('approval_by:id,name')
            ->whereNot('status', 'processing')
            ->where('approval_at', '>=', Carbon::now()->subDay())
            ->orderByDesc('approval_at')
            ->get();

        return response()->json([
            'recentApprovals' => $recentApprovals,
            'pendingDeposits' => Transaction::where('status', 'processing')->count()
        ]);
    }

    public function getPendingDeposits(Request $request)
    {
        $pendingDeposits = Transaction::with([
            'user:id,name,email,username',
            'user.group.group.group_leader:id,name',
            'media'
        ])
            ->where('status', 'processing')
            ->latest();

        return response()->json([
            'pendingDeposits' => $pendingDeposits->get(),
            'totalPendingDeposit' => $pendingDeposits->sum('amount'),
        ]);
    }

    public function pendingApproval(Request $request)
    {
        Validator::make($request->all(), [
            'remarks' => ['required_if:action,reject'],
        ])->setAttributeNames([
            'remarks' => trans('public.remarks'),
        ])->validate();

        $transaction = Transaction::find($request->id);
        $status = $request->action == 'approve' ? 'success' : 'fail';

        $transaction->update([
            'status' => $status,
            'remarks' => $request->remarks,
            'approval_at' => now(),
            'handle_by' => \Auth::id()
        ]);

        if ($transaction->status == 'success') {
            $wallet = Wallet::find($transaction->to_wallet_id);

            $wallet->balance += $transaction->transaction_amount;

            if ($transaction->fund_type == 'real_fund') {
                $wallet->real_fund += $transaction->transaction_amount;
            } else {
                $wallet->demo_fund += $transaction->transaction_amount;
            }

            $wallet->save();

            $transaction->new_wallet_amount = $wallet->balance;
            $transaction->save();
        }

        $toast_message = $transaction->status == 'success' ? trans('public.toast_approve_transaction_success') : trans('public.toast_reject_transaction_success');

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => $toast_message,
            'type' => 'success',
        ]);
    }

    public function deposit_history()
    {
        $depositHistoryCount = Transaction::where('transaction_type', 'deposit')
            ->whereNot('status', 'processing')
            ->count();

        return Inertia::render('Transaction/History/Deposit/DepositHistory', [
            'depositHistoryCount' => $depositHistoryCount
        ]);
    }

    public function getHighestDeposit(Request $request)
    {
        // current month
        $endOfMonth = \Illuminate\Support\Carbon::now()->endOfMonth();

        // last month
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $transactionQuery = Transaction::where('transaction_type', 'deposit');

        // current month success deposit
        $current_month_success_deposit = (clone $transactionQuery)
            ->where('status', 'success')
            ->whereDate('approval_at', '<=', $endOfMonth)
            ->sum('transaction_amount');

        // current month fail deposit
        $current_month_fail_deposit = (clone $transactionQuery)
            ->where('status', 'fail')
            ->whereDate('approval_at', '<=', $endOfMonth)
            ->sum('transaction_amount');

        // last month success deposit
        $last_month_success_deposit =  (clone $transactionQuery)
            ->where('status', 'success')
            ->whereDate('approval_at', '<=', $endOfLastMonth)
            ->sum('transaction_amount');

        // comparison % of success deposit vs last month
        $last_month_success_deposit_comparison = $last_month_success_deposit > 0
            ? (($current_month_success_deposit - $last_month_success_deposit) / $last_month_success_deposit) * 100
            : ($current_month_success_deposit > 0 ? 100 : 0);

        // Get and format top 3 users by total deposit
        $topThreeUser = Transaction::select('user_id', DB::raw('SUM(transaction_amount) as total_deposit'))
            ->where('transaction_type', 'deposit')
            ->where('status', 'success')
            ->groupBy('user_id')
            ->orderByDesc('total_deposit')
            ->take(3)
            ->with(['user:id,name', 'user.media'])
            ->get();

        return response()->json([
            'currentSuccessDeposit' => $current_month_success_deposit,
            'lastMonthSuccessDepositComparison' => $last_month_success_deposit_comparison,
            'currentFailDeposit' => $current_month_fail_deposit,
            'topThreeUser' => $topThreeUser,
        ]);
    }

    public function getDepositHistoryData()
    {
        $depositHistories = Transaction::with([
            'user:id,name,email,username',
            'user.group.group.group_leader:id,name',
            'media',
            'to_wallet:id,type,address'
        ])
            ->where('transaction_type', 'deposit')
            ->whereNot('status', 'processing')
            ->latest()
            ->get();

        return response()->json([
            'depositHistories' => $depositHistories,
        ]);
    }
}
