<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cart_id',
        'type',
        'product_id',
        'trading_master_id',
        'quantity',
        'price_per_unit',
        'total_price',
    ];
}
