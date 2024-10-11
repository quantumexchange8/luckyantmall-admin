<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'item_id',
        'category_id',
        'descriptions',
        'quantity',
        'base_price',
        'discount_type',
        'discount_percentage',
        'discount_price',
        'final_price_type',
        'final_price',
    ];
}
