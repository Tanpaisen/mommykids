<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'image', 'price',
        'old_price', 'discount_percent', 'stock', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'old_price' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Shape expected by resources/views/components/product-card.blade.php
     * Lets controllers do: $products->map->toCardArray()
     */
    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image ?: 'https://via.placeholder.com/300?text=' . urlencode($this->name),
            'price' => $this->price,
            'old_price' => $this->old_price,
            'discount' => $this->discount_percent,
            'url' => route('product.show', $this->slug),
        ];
    }
}
