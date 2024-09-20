<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function index()
    {
        return Inertia::render('Group/GroupListing', [
            'groupCount' => Group::count()
        ]);
    }

    public function getGroupsData(Request $request)
    {
        $groupsQuery = Group::query()->with([
            'group_leader:id,name,email,upline_id,hierarchyList',
            'group_has_user'
        ]);
        $totalRecords = $groupsQuery->count();
        $groups = $groupsQuery->paginate($request->paginate);

        $formattedGroups = $groups->map(function($group) {
            return [
                'name' => $group->name,
                'color' => $group->color,
                'leader_name' => $group->group_leader->name,
                'leader_email' => $group->group_leader->email,
                'member_count' => $group->group_has_user()->count(),
            ];
        });

        return response()->json([
            'groups' => $formattedGroups,
            'totalRecords' => $totalRecords,
            'currentPage' => $groups->currentPage(),
        ]);
    }
}
