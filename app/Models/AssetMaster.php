<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetMaster extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'meta_login',
        'asset_name',
        'category',
        'type',
        'total_investors',
        'minimum_investment',
        'minimum_investment_period_type',
        'minimum_investment_period',
        'status',
        'edited_by',
    ];
}
