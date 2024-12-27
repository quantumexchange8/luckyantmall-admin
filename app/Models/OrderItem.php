<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'trading_master_id',
        'price_per_unit',
        'quantity',
        'delivered_at',
        'cancelled_at',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function trading_master(): BelongsTo
    {
        return $this->belongsTo(TradingMaster::class, 'trading_master_id', 'id');
    }

}
