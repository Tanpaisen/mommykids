<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price'];

    protected static function booted(): void
    {
        $clearCache = fn (CartItem $item) => Cache::forget('cart_count_' . $item->cart_id);
        
        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
}