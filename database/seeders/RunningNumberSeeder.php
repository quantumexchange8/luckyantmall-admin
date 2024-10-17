<?php

namespace Database\Seeders;

use App\Models\RunningNumber;
use Illuminate\Database\Seeder;

class RunningNumberSeeder extends Seeder
{
    public function run(): void
    {
        RunningNumber::create([
            'type' => 'transaction',
            'prefix' => 'LM-TXN',
            'digits' => 7,
            'last_number' => 0,
        ]);

        RunningNumber::create([
            'type' => 'e_wallet',
            'prefix' => 'LM-EW',
            'digits' => 7,
            'last_number' => 0,
        ]);

        RunningNumber::create([
            'type' => 'bonus_wallet',
            'prefix' => 'LM-BW',
            'digits' => 7,
            'last_number' => 0,
        ]);

        RunningNumber::create([
            'type' => 'cash_wallet',
            'prefix' => 'LM-CW',
            'digits' => 7,
            'last_number' => 0,
        ]);

        RunningNumber::create([
            'type' => 'point_wallet',
            'prefix' => 'LM-PW',
            'digits' => 7,
            'last_number' => 0,
        ]);
    }
}
