<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'group_leader_id',
        'color',
        'edited_by',
    ];

    // Relations
    public function group_leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'group_leader_id', 'id');
    }
}
