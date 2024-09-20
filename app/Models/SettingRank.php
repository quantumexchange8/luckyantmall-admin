<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingRank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'position',
        'lot_rebate_currency',
        'lot_rebate_amount',
        'min_direct_referral',
        'min_direct_referral_rank_id',
        'group_sales_currency',
        'max_capped_per_line',
        'min_group_sales',
    ];
}
