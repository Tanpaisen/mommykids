<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'campaign_type_id',

        /*
         * Giữ cột type để tương thích OrderItem / code cũ.
         * Giá trị này lấy từ campaign_types.code, không còn fix cứng.
         */
        'type',

        'description',
        'starts_at',
        'ends_at',
        'is_active',
        'allow_voucher',
        'priority',
        'config',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'allow_voucher' => 'boolean',
        'priority' => 'integer',
        'config' => 'array',
    ];

    public function campaignType(): BelongsTo
    {
        return $this->belongsTo(CampaignType::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'campaign_products'
        )
            ->withPivot([
                'sale_price',
                'discount_percent',
                'stock_limit',
                'sold_quantity',
                'max_per_user',
            ])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }
}
