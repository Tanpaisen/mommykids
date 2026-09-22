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

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        $clearCache = function (Product $product) {
            Cache::forget('product_' . $product->id);
            Cache::forget('related_' . $product->id);
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

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

        // Hình ảnh
        'image',
        'images',

        // Giá / tồn kho
        'price',
        'old_price',
        'discount_percent',
        'stock',

        /*
         * Không đưa sold_count vào fillable.
         *
         * Lượt bán được hệ thống tự cập nhật khi
         * đơn hàng được giao thành công.
         */

        // Thông tin đóng gói dùng cho GHN
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

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        // Trạng thái
        'is_active' => 'boolean',
        'is_featured' => 'boolean',

        // Giá / kho / lượt bán
        'price' => 'integer',
        'old_price' => 'integer',
        'discount_percent' => 'integer',
        'stock' => 'integer',
        'sold_count' => 'integer',

        // Đóng gói
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
        return $this->belongsTo(
            Category::class
        );
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
            ProductReview::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }

    public function scopeFeatured($query)
    {
        return $query->where(
            'is_featured',
            true
        );
    }

    public function scopeLowStock(
        $query,
        int $threshold = 10
    ) {
        return $query->where(
            'stock',
            '<=',
            $threshold
        );
    }

    /*
     * Sản phẩm bán chạy.
     *
     * Ví dụ:
     *
     * Product::bestSelling()->get();
     */
    public function scopeBestSelling($query)
    {
        return $query
            ->orderByDesc('sold_count')
            ->orderByDesc('id');
    }

    /*
     * Lấy thống kê review ngay trong query.
     *
     * reviews_count
     * reviews_avg_rating
     *
     * Giúp card sản phẩm không phát sinh
     * một query review cho từng sản phẩm.
     */
    public function scopeWithReviewStats($query)
    {
        return $query
            ->withCount('reviews')
            ->withAvg(
                'reviews',
                'rating'
            );
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
        $rating = round(
            (float) (
                $this->reviews_avg_rating ?? 0
            ),
            1
        );

        $reviewCount = (int) (
            $this->reviews_count ?? 0
        );

        $soldCount = max(
            0,
            (int) (
                $this->sold_count ?? 0
            )
        );

        return [
            'id' => $this->id,

            'name' => $this->name,

            'image' => $this->image,

            'price' => (int) $this->price,

            'old_price' => $this->old_price
                ? (int) $this->old_price
                : null,

            'discount' => $this->discount_percent
                ? (int) $this->discount_percent
                : null,

            // Review thật
            'rating' => $rating,

            'review_count' => $reviewCount,

            // Lượt bán thật
            'sold_count' => $soldCount,

            'url' => route(
                'product.show',
                $this->slug
            ),
        ];
    }
}