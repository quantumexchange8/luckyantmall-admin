<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\GroupHasUser;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        Group::create([
            'name' => 'Luckyant Mall',
            'group_leader_id' => 2,
            'color' => '839DD1',
            'edited_by' => 1
        ]);

        GroupHasUser::create([
            'group_id' => 1,
            'user_id' => 2,
        ]);
    }
}
