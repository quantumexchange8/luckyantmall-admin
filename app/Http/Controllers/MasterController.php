<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\TradingAccount;
use App\Models\TradingMaster;
use App\Models\TradingMasterToGroup;
use App\Models\TradingSubscription;
use App\Models\User;
use App\Services\MetaFiveService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class MasterController extends Controller
{
    public function index()
    {
        return Inertia::render('Master/MasterListing', [
            'masterCounts' => TradingMaster::count(),
        ]);
    }

    public function getMasterOverview()
    {
        // current month
        $endOfMonth = \Illuminate\Support\Carbon::now()->endOfMonth();

        // last month
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $subscription_query = TradingSubscription::where('status', 'active');

        // current month assets
        $current_month_assets = (clone $subscription_query)
            ->whereDate('approval_at', '<=', $endOfMonth)
            ->sum('subscription_amount');

        // current month investors
        $current_month_investors = (clone $subscription_query)
            ->whereDate('approval_at', '<=', $endOfMonth)
            ->count();

        // last month assets
        $last_month_assets = (clone $subscription_query)
            ->whereDate('approval_at', '<=', $endOfLastMonth)
            ->sum('subscription_amount');

        // last month investors
        $last_month_investors = (clone $subscription_query)
            ->whereDate('approval_at', '<=', $endOfLastMonth)
            ->count();

        // comparison % of assets vs last month
        $last_month_asset_comparison = $last_month_assets > 0
            ? (($current_month_assets - $last_month_assets) / $last_month_assets) * 100
            : ($current_month_assets > 0 ? 100 : 0);

        // comparison % of investors vs last month
        $last_month_investor_comparison = $current_month_investors - $last_month_investors;

        // Get and format top 3 masters by total fund
        $topThreeMaster = TradingSubscription::select(
            'master_meta_login',
            DB::raw('SUM(subscription_amount) as total_investment')
        )
            ->where('status', 'active')
            ->groupBy('master_meta_login')
            ->orderByDesc('total_investment')
            ->take(3)
            ->with(['trading_master:meta_login,master_name'])
            ->get();

        return response()->json([
            'currentAssets' => $current_month_assets,
            'lastMonthAssetComparison' => $last_month_asset_comparison,
            'currentInvestors' => $current_month_investors,
            'lastMonthInvestorComparison' => $last_month_investor_comparison,
            'topThreeMaster' => $topThreeMaster,
        ]);
    }

    public function addMaster(Request $request)
    {
        $form_step = $request->step;

        $rules = [
            'master_name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'owner' => ['required'],
            'type' => ['required'],
            'estimated_monthly_return' => ['nullable'],
            'estimated_lot_size' => ['nullable'],
            'max_drawdown' => ['nullable'],
        ];

        $attributeNames = [
            'master_name' => trans('public.full_name'),
        ];

        switch ($form_step) {
            case 1:
                Validator::make($request->all(), $rules)
                    ->setAttributeNames($attributeNames)
                    ->validate();
                return back();

            case 2:
                $rules['min_investment'] = ['required'];
                $rules['sharing_profit'] = ['required'];
                $rules['investment_period'] = ['nullable'];
                $rules['investment_period_type'] = ['nullable'];
                $rules['settlement_period'] = ['required'];
                $rules['settlement_period_type'] = ['required'];
                $rules['visible_to'] = ['required'];

                Validator::make($request->all(), $rules)
                    ->setAttributeNames($attributeNames)
                    ->validate();
                return back();

            default:
                $rules['management_fee'] = ['nullable'];

                Validator::make($request->all(), $rules)
                    ->setAttributeNames($attributeNames)
                    ->validate();
                break;
        }

        $user = $request->owner;

        $master = TradingMaster::create([
            'user_id' => $user['id'],
            'master_name' => $request->master_name,
            'type' => $request->type,
            'estimated_monthly_returns' => $request->estimated_monthly_returns,
            'estimated_lot_size' => $request->estimated_lot_size,
            'max_drawdown' => $request->max_drawdown,
            'min_investment' => $request->min_investment,
            'sharing_profit' => $request->sharing_profit,
            'join_period' => $request->investment_period,
            'join_period_type' => $request->investment_period_type,
            'settlement_period' => $request->settlement_period,
            'settlement_period_type' => $request->settlement_period_type,
            'handle_by' => \Auth::id()
        ]);

        $metaService = new MetaFiveService();
        $connection = $metaService->getConnectionStatus();

        if ($connection != 0) {
            return back()->with('toast', [
                'title' => trans('public.connection_error'),
                'message' => trans('public.meta_trader_connection_error'),
                'type' => 'error',
            ]);
        }

        $userModel = User::find($user['id']);
        $metaAccount = $metaService->createUser($userModel, 'JS', 500, $userModel->email, $master->master_name);

        $master->meta_login = $metaAccount['login'];
        $groupCounts = Group::whereNotNull('parent_group_id')->count();
        $groups = $request->visible_to;

        if (count($groups) == $groupCounts) {
            $master->visibility_type = 'public';
        } else {
            $master->visibility_type = 'private';

            foreach ($groups as $group) {
                TradingMasterToGroup::create([
                    'trading_master_id' => $master->id,
                    'group_id' => $group['id'],
                ]);
            }
        }
        $master->save();

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_create_deposit_profile_success'),
            'type' => 'success',
        ]);
    }

    public function getMasters(Request $request)
    {
        // fetch limit with default
        $limit = $request->input('limit', 12);

        // Fetch parameter from request
        $search = $request->input('search', '');
        $sortType = $request->input('sortType');
        $groups = $request->input('groups');
        $adminUser = $request->input('adminUser', '');
        $tag = $request->input('tag', '');
        $status = $request->input('status', '');

        // Fetch paginated masters
        $mastersQuery = TradingMaster::query()
            ->withCount('ongoing_subscriptions')
            ->withSum('ongoing_subscriptions', 'subscription_amount');

        // Apply search parameter to multiple fields
        if (!empty($search)) {
            $mastersQuery->where(function($query) use ($search) {
                $query->where('master_name', 'LIKE', "%$search%");
            });
        }

        // Apply sorting dynamically
        if (in_array($sortType, ['latest', 'popular', 'largest_fund', 'most_investors'])) {
            switch ($request->sortType) {
                case 'latest':
                    $mastersQuery->orderBy('created_at', 'desc');
                    break;

                case 'popular':
                    $mastersQuery->leftJoin('asset_master_user_favourites', 'asset_masters.id', '=', 'asset_master_user_favourites.asset_master_id')
                        ->select('asset_masters.*', \Illuminate\Support\Facades\DB::raw('COUNT(asset_master_user_favourites.id) as total_like_count'))
                        ->groupBy('asset_masters.id')
                        ->orderByDesc('total_likes_count');
                    break;

                case 'largest_fund':
                    $mastersQuery->leftJoin('asset_subscriptions', function ($join) {
                        $join->on('asset_masters.id', '=', 'asset_subscriptions.asset_master_id')
                            ->where('asset_subscriptions.status', 'ongoing');
                    })
                        ->select('asset_masters.*',
                            DB::raw('total_fund + COALESCE(SUM(asset_subscriptions.investment_amount), 0) AS total_fund_combined')
                        )
                        ->groupBy('asset_masters.id', 'total_fund')
                        ->orderBy(DB::raw('total_fund + COALESCE(SUM(asset_subscriptions.investment_amount), 0)'), 'desc');
                    break;

                case 'most_investors':
                    $mastersQuery->leftJoin('asset_subscriptions', function ($join) {
                        $join->on('asset_masters.id', '=', 'asset_subscriptions.asset_master_id')
                            ->where('asset_subscriptions.status', 'ongoing');
                    })
                        ->select('asset_masters.*',
                            DB::raw('total_investors + COALESCE(COUNT(asset_subscriptions.id), 0) AS total_investors_combined')
                        )
                        ->groupBy('asset_masters.id', 'total_investors')
                        ->orderBy(DB::raw('total_investors + COALESCE(COUNT(asset_subscriptions.id), 0)'), 'desc');
                    break;

                default:
                    return response()->json(['error' => 'Invalid filter'], 400);
            }
        }

        // // Apply groups filter
        if (!empty($groups)) {
            if ($groups == 'public') {
                $mastersQuery->where('type', 'public');
            } else {
                $mastersQuery->whereHas('visible_to_groups', function ($query) use ($groups) {
                    $query->whereIn('group_id', [$groups]);
                });
            }
        }

        // // Apply adminUser filter
        // if (!empty($adminUser)) {
        //     dd($request->all());
        // }

        // // Apply tag filter
        if (!empty($tag)) {
            switch ($tag) {
                case 'no_min_investment':
                    $mastersQuery->where('minimum_investment', 0);
                    break;

                case 'lock_free':
                    $mastersQuery->where('minimum_investment_period', 0);
                    break;

                case 'zero_fee':
                    $mastersQuery->where('performance_fee', 0);
                    break;

                default:
                    return response()->json(['error' => 'Invalid filter'], 400);
            }
        }

        // Apply status filter
        if (!empty($status)) {
            $mastersQuery->where('status', $status);
        }

        // Get total count of masters
        $totalRecords = $mastersQuery->count();

        // Fetch paginated results
        $masters = $mastersQuery->paginate($limit);

        // Format masters
        $formattedMasters = $masters->map(function($master) {

            return $master;
        });

        return response()->json([
            'masters' => $formattedMasters,
            'totalRecords' => $totalRecords,
            'currentPage' => $masters->currentPage(),
        ]);
    }

    public function getJoiningAccountsData(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $query = TradingSubscription::select([
            'id',
            'user_id',
            'meta_login',
            'master_meta_login',
            'subscription_amount',
            'approval_at',
            'status'
        ])
            ->with([
                'user:id,name,email',
                'user.group.group.group_leader:id,name',
            ])
            ->where('master_meta_login', $request->master_meta_login)
            ->where('status', 'active');

        if ($startDate && $endDate) {
            $start_date = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $accounts = $query
            ->orderByDesc('approval_at')
            ->get();

        return response()->json([
            'accounts' => $accounts,
        ]);
    }
}
