<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'user_id',
        'note',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'stock_before' => 'integer',
        'stock_after'  => 'integer',
    ];

    /**
     * Sản phẩm liên quan
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Người thực hiện thao tác
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Đối tượng tham chiếu (Order / PurchaseOrder) — đa hình
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: Lọc theo loại biến động
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Kiểm tra xem là nhập (+) hay xuất (-)
     */
    public function getIsIncomingAttribute(): bool
    {
        return $this->quantity > 0;
    }
}
