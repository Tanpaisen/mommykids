<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'images',
        'price',
        'old_price',
        'discount_percent',
        'stock',
        'is_active',

        // Audit soft delete
        'deleted_by',
        'restored_by',
        'restored_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'old_price' => 'integer',
        'discount_percent' => 'integer',
        'stock' => 'integer',
        'images' => 'array',

        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(
            Stage::class,
            'product_stage'
        )->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'product_tag'
        )->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query, int $threshold = 10)
    {
        return $query->where('stock', '<=', $threshold);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'discount' => $this->discount_percent,
            'url' => route('product.show', $this->slug),
        ];
    }
}