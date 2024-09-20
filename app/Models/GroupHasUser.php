<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupHasUser extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'user_id',
    ];
}
