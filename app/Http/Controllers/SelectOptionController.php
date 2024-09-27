<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Group;
use App\Models\User;

class SelectOptionController extends Controller
{
    public function getCountries()
    {
        $countries = Country::select('id', 'name', 'phone_code', 'iso2', 'emoji', 'translations')
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
}
