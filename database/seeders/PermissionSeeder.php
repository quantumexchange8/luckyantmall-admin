<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name' => 'super_admin'
        ]);

        Role::create([
            'name' => 'admin'
        ]);

        Role::create([
            'name' => 'demo_fund'
        ]);

        Role::create([
            'name' => 'special_fund'
        ]);

        Role::create([
            'name' => 'special_demo_fund'
        ]);

        Role::create([
            'name' => 'BP'
        ]);

        Role::create([
            'name' => 'SBP'
        ]);

        Role::create([
            'name' => 'LBP'
        ]);

        User::find(1)->syncRoles('super_admin');
    }
}
