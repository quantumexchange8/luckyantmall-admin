<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalSpecialBonus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category',
        'bonus_in_percentage',
        'bonus_in_amount',
        'is_recurring',
        'bonus_period_type',
        'bonus_period',
        'edited_by',
    ];
}
