<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function index()
    {
        return Inertia::render('Order/OrderListing', [
            'orderCounts' => Order::count(),
        ]);
    }

    public function getOrderOverview()
    {
        $orderStats = Order::query()
        ->selectRaw('
            status,
            COUNT(*) as count,
            SUM(total_price) as total_amount
        ')
        ->groupBy('status')
        ->get();

        $result = [
            'completedCount' => 0,
            'completedAmount' => 0,
            'pendingCount' => 0,
            'pendingAmount' => 0,
            'cancelCount' => 0,
            'cancelAmount' => 0,
            'refundCount' => 0,
            'refundAmount' => 0,
        ];

        foreach ($orderStats as $stat) {
            switch ($stat->status) {
                case 'completed':
                    $result['completedCount'] = $stat->count;
                    $result['completedAmount'] = $stat->total_amount;
                    break;
                case 'pending':
                    $result['pendingCount'] = $stat->count;
                    $result['pendingAmount'] = $stat->total_amount;
                    break;
                case 'cancelled':
                    $result['cancelCount'] = $stat->count;
                    $result['cancelAmount'] = $stat->total_amount;
                    break;
                case 'refunded':
                    $result['refundCount'] = $stat->count;
                    $result['refundAmount'] = $stat->total_amount;
                    break;
            }
        }

        return response()->json($result);
    }

    public function getOrderHistory(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);
            
            $query = Order::with([
                'user',
                'transaction',
                'orderItems',
            ]);
                // ->where('status', $data['filters']['status']['value']);

            // $query = Transaction::with([
            //     'user',
            //     'from_wallet:id,type,name,wallet_address',
            //     'to_wallet:id,type,name,wallet_address',
            //     'from_meta_login:id,meta_login',
            //     'to_meta_login:id,meta_login',
            // ])
            //     ->where('transaction_type', $data['filters']['type']['value'])
            //     ->whereNot('status', 'Processing');

            // if ($data['filters']['type']['value'] == 'Withdrawal') {
            //     $query->addSelect([
            //         'profitAmount' => DB::table('wallet_logs')
            //             ->selectRaw('SUM(amount)')
            //             ->whereColumn('wallet_logs.user_id', 'transactions.user_id')
            //             ->where('wallet_logs.purpose', 'ProfitSharing'),

            //         'bonusAmount' => DB::table('wallet_logs')
            //             ->selectRaw('SUM(amount)')
            //             ->whereColumn('wallet_logs.user_id', 'transactions.user_id')
            //             ->whereIn('wallet_logs.purpose', ['PerformanceIncentive', 'SameLevelRewards', 'LotSizeRebate']),
            //     ]);

            //     if (!empty($data['filters']['start_approval_date']['value']) && !empty($data['filters']['end_approval_date']['value'])) {
            //         $start_date = Carbon::parse($data['filters']['start_approval_date']['value'])->addDay()->startOfDay();
            //         $end_date = Carbon::parse($data['filters']['end_approval_date']['value'])->addDay()->endOfDay();

            //         $query->whereBetween('approval_at', [$start_date, $end_date]);
            //     }
            // }

            // if ($data['filters']['type']['value'] == 'Transfer') {
            //     $query->with([
            //         'from_wallet.user:id,name',
            //         'to_wallet.user:id,name',
            //     ]);
            // }

            if ($data['filters']['global']['value']) {
                $query->whereHas('user', function($q) use ($data) {
                    $q->where(function ($query) use ($data) {
                        $keyword = $data['filters']['global']['value'];

                        $query->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
                    });
                });
            }
            
            // $leaderId = $data['filters']['leader_id']['value']['id'] ?? null;

            // // Filter by leaderId if provided
            // if ($leaderId) {
            //     // Load users under the specified leader
            //     $usersUnderLeader = User::where('leader_status', 1)
            //         ->where('id', $leaderId)
            //         ->orWhere('hierarchyList', 'like', "%-$leaderId-%")
            //         ->pluck('id');

            //     $query->whereIn('user_id', $usersUnderLeader);
            // }

            if (!empty($data['filters']['start_date']['value']) && !empty($data['filters']['end_date']['value'])) {
                $start_date = Carbon::parse($data['filters']['start_date']['value'])->addDay()->startOfDay();
                $end_date = Carbon::parse($data['filters']['end_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
            
            // if ($data['filters']['fund_type']['value']) {
            //     $query->where('fund_type', $data['filters']['fund_type']['value']);
            // }

            // if ($data['filters']['status']['value']) {
            //     $query->where('status', $data['filters']['status']['value']);
            // }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->latest();
            }
            
            $authUser = Auth::user();

            if ($authUser->hasRole('admin') && $authUser->leader_status == 1) {
                $childrenIds = $authUser->getChildrenIds();
                $childrenIds[] = $authUser->id;
                $query->whereIn('user_id', $childrenIds);
            } elseif ($authUser->hasRole('super_admin')) {
                // Super-admin logic, no need to apply whereIn
            } elseif (!empty($authUser->getFirstLeader()) && $authUser->getFirstLeader()->hasRole('admin')) {
                $childrenIds = $authUser->getFirstLeader()->getChildrenIds();
                $query->whereIn('user_id', $childrenIds);
            } else {
                // No applicable conditions, set whereIn to empty array
                $query->whereIn('user_id', []);
            }

            
            // Export logic
            if ($request->has('exportStatus') && $request->exportStatus) {
                return Excel::download(new OrdersExport($query), now() . '-'. $data['filters']['status']['value'] . 'report.xlsx');
            }
            
            // Calculate totals before pagination
            // $totalAmount = (clone $query)->sum('amount');
            // $successAmount = (clone $query)->where('status', 'Success')->sum('amount');
            // $rejectedAmount = (clone $query)->where('status', 'Rejected')->sum('amount');

            $orders = $query->paginate($data['rows']);

            // $userHierarchyLists = $orders->pluck('user.hierarchyList')
            //     ->filter()
            //     ->flatMap(fn($list) => explode('-', trim($list, '-')))
            //     ->unique()
            //     ->toArray();

            // Load all potential leaders in bulk
            // if ($leaderId > 0) {
            //     $leaderQuery = User::where('id', $leaderId)
            //         ->where('leader_status', 1);
            // } else {
            //     $leaderQuery = User::whereIn('id', $userHierarchyLists)
            //         ->where('leader_status', 1);
            // }

            // $leaders = $leaderQuery->get()->keyBy('id');

            // $orders->each(function ($transaction) use ($userService, $leaders) {
            //     $firstLeader = $userService->getFirstLeader($transaction->user?->hierarchyList, $leaders);

            //     $transaction->first_leader_id = $firstLeader?->id;
            //     $transaction->first_leader_name = $firstLeader?->name;
            //     $transaction->first_leader_email = $firstLeader?->email;
            // });

            return response()->json([
                'success' => true,
                'data' => $orders,
                // 'totalAmount' => $totalAmount,
                // 'successAmount' => $successAmount,
                // 'rejectedAmount' => $rejectedAmount,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function updateOrderStatus(Request $request)
    {
        $order = Order::find($request->id);

        $order->status = $request->status;
        if ($request->status == 'completed') {
            $order->completed_at = now();
        }
        elseif ($request->status == 'cancelled') {
            $order->cancelled_at = now();
        }

        $order->save();

        $order_items = OrderItem::where('order_id', $order->id)
                ->get();

        foreach ($order_items as $order_item) {
            $updateData = [];
        
            $updateData['status'] = $request->status;
            if ($request->status == 'completed') {
                $updateData['status'] = 'delivered';
                $updateData['delivered_at'] = now();
            } elseif ($request->status == 'cancelled') {
                $updateData['status'] = 'cancelled';
                $updateData['cancelled_at'] = now();
            }
        
            $order_item->update($updateData);
        }

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_order_has_updated"),
            'type' => 'success',
        ]);
    }

    public function updateOrderItemStatus(Request $request)
    {
        $item = OrderItem::find($request->id);

        $item->status = $request->status;
        if ($request->status == 'completed') {
            $item->status = 'delivered';
            $item->delivered_at = now();
        }
        elseif ($request->status == 'cancelled') {
            $item->cancelled_at = now();
        }
        $item->save();

        return back()->with('toast', [
            'title' => trans("public.success"),
            'message' => trans("public.toast_order_item_has_updated"),
            'type' => 'success',
        ]);
    }
}
