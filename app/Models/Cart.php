<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = ['uuid', 'user_id', 'status'];

    protected static function booted(): void
    {
        $clearCache = fn (Cart $cart) => Cache::forget('cart_count_' . $cart->id);
        
        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}