<?php

namespace App\Console\Commands;

use App\Models\CurrencyConversionRate;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Illuminate\Console\Command;

class UpdateCurrencyRateCommand extends Command
{
    protected $signature = 'update:currency-rate';

    protected $description = 'Update currency rate daily';

    public function handle(): void
    {
        $currencyArrays = CurrencyConversionRate::whereNot('base_currency', 'USDT')
            ->get()
            ->pluck('base_currency')
            ->toArray();

        $exchangeRates = app(ExchangeRate::class);

        $results = $exchangeRates->exchangeRate('USD', $currencyArrays);

        foreach ($results as $currency => $rate) {
            $baseCurrency = CurrencyConversionRate::where('base_currency', $currency)->first();
            $depositRate = $rate * 1.03; // Deposit +3%
            $withdrawalRate = $rate * 0.985; // Withdrawal -1.5%

            $baseCurrency->update([
                'deposit_rate' => $depositRate,
                'withdrawal_rate' => $withdrawalRate,
            ]);
        }
    }
}
