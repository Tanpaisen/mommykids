<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        $clearCache = function (Product $product) {
            Cache::forget('product_' . $product->id);
            Cache::forget('related_' . $product->id);
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',

        // Nội dung chi tiết sản phẩm
        'origin',
        'manufacturer',
        'ingredients',
        'usage_instructions',
        'storage_instructions',
        'warning',
        'highlights',

        'image',
        'images',

        'price',
        'old_price',
        'discount_percent',
        'stock',

        // Thông tin đóng gói dùng để tính phí vận chuyển GHN
        'weight_grams',
        'length_cm',
        'width_cm',
        'height_cm',

        // Trạng thái
        'is_active',
        'is_featured',

        // Audit soft delete
        'deleted_by',
        'restored_by',
        'restored_at',
    ];

    protected $casts = [
        // Trạng thái
        'is_active' => 'boolean',
        'is_featured' => 'boolean',

        // Giá / tồn kho
        'price' => 'integer',
        'old_price' => 'integer',
        'discount_percent' => 'integer',
        'stock' => 'integer',

        // Thông tin đóng gói
        'weight_grams' => 'integer',
        'length_cm' => 'integer',
        'width_cm' => 'integer',
        'height_cm' => 'integer',

        // JSON
        'images' => 'array',
        'highlights' => 'array',

        // Datetime
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    public function reviews(): HasMany
    {
        return $this->hasMany(
            \App\Models\ProductReview::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeLowStock($query, int $threshold = 10)
    {
        return $query->where('stock', '<=', $threshold);
    }

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Card data
    |--------------------------------------------------------------------------
    */

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
