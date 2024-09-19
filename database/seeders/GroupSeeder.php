<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        Group::create([
            'name' => 'Public',
            'group_leader_id' => 2,
            'color' => '839DD1',
            'edited_by' => 1
        ]);
    }
}
