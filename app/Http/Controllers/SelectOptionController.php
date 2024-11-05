<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Models\Group;
use App\Models\GroupRankSetting;
use App\Models\Item;
use App\Models\TradingMaster;
use App\Models\User;

class SelectOptionController extends Controller
{
    public function getCountries()
    {
        $countries = Country::select('id', 'name', 'phone_code', 'iso2', 'emoji', 'translations', 'currency', 'currency_symbol')
            ->get();

        return response()->json($countries);
    }

    public function getUsers()
    {
        $users = User::where('role', 'user')
            ->select('id', 'name', 'username')
            ->get();

        return response()->json($users);
    }

    public function getAvailableLeader()
    {
        $group_leader_ids = Group::pluck('group_leader_id')->toArray();

        $users = User::where('role', 'user')
            ->whereNotIn('id', $group_leader_ids)
            ->select('id', 'name', 'username')
            ->get()
            ->map(function($user) {
               $data = $user;
               $data['total_group_members'] = count($user->getChildrenIds());

               return $data;
            });

        return response()->json($users);
    }

    public function getSettingRanks()
    {
        $ranks = GroupRankSetting::select('id', 'rank_name', 'lot_rebate_amount', 'min_group_sales')
            ->where('group_id', 1)
            ->where('rank_position', '>', 1)
            ->get()
            ->map(function($rank) {
                $rank->lot_rebate_amount = intval($rank->lot_rebate_amount);
                $rank->min_group_sales = intval($rank->min_group_sales);
                return $rank;
            });

        return response()->json($ranks);
    }

    public function getItems()
    {
        $items = Item::where('status', 'active')
            ->get()
            ->map(function($item) {
                $item->item_name = $item->getTranslation('name', app()->getLocale());;

                return $item;
            });

        return response()->json($items);
    }

    public function getGroupLeaders()
    {
        $groups = Group::whereNotNull('parent_group_id')
            ->get();

        return response()->json($groups);
    }

    public function getCategories()
    {
        $categories = Category::where('status', 'active')
            ->get();

        return response()->json($categories);
    }

    public function getMasters()
    {
        $categories = TradingMaster::where('status', 'active')
            ->select('id', 'master_name', 'meta_login')
            ->get();

        return response()->json($categories);
    }
}
