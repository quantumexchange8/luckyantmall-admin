<?php

namespace Database\Seeders;

use App\Models\AccountType;
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

        AccountType::create([
            'name' => 'JS',
            'slug' => 'js',
            'minimum_deposit' => 0,
            'leverage' => 500,
            'currency' => 'USD',
            'allow_create_account' => -1,
            'type' => 'dollar',
            'commission_structure' => 'self',
            'trade_open_duration' => '180',
            'maximum_account_number' => 3,
            'status' => 'active',
            'edited_by' => 1,
        ]);
    }
}
