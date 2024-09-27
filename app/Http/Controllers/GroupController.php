<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupHasUser;
use App\Models\User;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class GroupController extends Controller
{
    protected GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

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
            'group_has_user',
            'child_groups:id,parent_group_id',
        ]);
        $totalRecords = $groupsQuery->count();
        $groups = $groupsQuery->paginate($request->paginate);

        $formattedGroups = $groups->map(function($group) {
            $data = $group;
            $totalMemberCount = $this->getGroupTotalMembers($group);
            $data['member_count'] = $totalMemberCount;

            return $data;
        });

        return response()->json([
            'groups' => $formattedGroups,
            'totalRecords' => $totalRecords,
            'currentPage' => $groups->currentPage(),
        ]);
    }

    public function addGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'color' => ['required'],
            'leader' => ['required'],
        ])->setAttributeNames([
            'name' => trans('public.name'),
            'color' => trans('public.color'),
            'leader' => trans('public.leader'),
        ]);
        $validator->validate();

        $leader_id = $request->leader['id'];
        $group_of_leader = GroupHasUser::where('user_id', $leader_id)
            ->first();

        if ($group_of_leader) {
            $parent_group_id = $group_of_leader->group_id;
        } else {
            $parent_group_id = Group::first()->id;
        }

        $group = Group::create([
            'name' => $request->name,
            'color' => strtoupper($request->color),
            'group_leader_id' => $leader_id,
            'parent_group_id' => $parent_group_id,
            'edited_by' => Auth::id(),
        ]);

        $group_id = $group->id;
        $children_ids = User::find($leader_id)->getChildrenIds();
        $children_ids[] = $leader_id;

        foreach ($children_ids as $child_id) {
            $this->groupService->updateUserGroup($group_id, $child_id);
        }

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_create_group_success'),
            'type' => 'success',
        ]);
    }

    private function getGroupTotalMembers($group)
    {
        $memberCount = $group->group_has_user()->count();

        $childGroups = $group->child_groups;

        foreach ($childGroups as $childGroup) {
            $memberCount += $this->getGroupTotalMembers($childGroup);
        }

        return $memberCount;
    }
}
