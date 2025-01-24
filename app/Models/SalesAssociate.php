<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesAssociate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'target_amount',
        'bonus_amount',
    ];
}
