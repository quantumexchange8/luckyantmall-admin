<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'order_number',
        'sub_total',
        'delivery_fee',
        'discount_price',
        'total_price',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'postal_code',
        'country_id',
        'completed_at',
        'cancelled_at',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
}
