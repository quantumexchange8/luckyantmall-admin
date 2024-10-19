<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
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
            'pendingDeposits'
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
            ->latest()
            ->get();

        return response()->json([
            'pendingDeposits' => $pendingDeposits,
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
        $status = $request->action ? 'success' : 'fail';

        $transaction->update([
            'status' => $status,
            'remarks' => $request->remarks,
            'approval_at' => now(),
            'handle_by' => \Auth::id()
        ]);

        if ($transaction->status == 'success') {
            $wallet = Wallet::find($transaction->to_wallet_id);

            $wallet->balance += $transaction->transaction_amount;
            $wallet->save();
        }

        $toast_message = $transaction->status == 'success' ? trans('public.toast_approve_transaction_success') : trans('public.toast_reject_transaction_success');

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => $toast_message,
            'type' => 'success',
        ]);
    }
}
