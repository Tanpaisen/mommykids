<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'code','user_id','recipient_name','recipient_phone','recipient_email',
        'province_name','district_name','ward_name','address_detail',
        'ghn_province_id','ghn_district_id','ghn_ward_code',
        'subtotal','shipping_fee','discount','total',
        'status','payment_method','payment_status','note',
    ];

    // Auto-generate code khi tạo
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->code = 'ORD-' . now()->format('Y') .
                str_pad(static::whereYear('created_at', now()->year)->count() + 1, 5, '0', STR_PAD_LEFT);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    // Labels màu cho status
    public function statusLabel(): array
    {
        return match($this->status) {
            'pending'    => ['text' => 'Chờ xác nhận', 'color' => 'yellow'],
            'confirmed'  => ['text' => 'Đã xác nhận',  'color' => 'blue'],
            'processing' => ['text' => 'Đang xử lý',   'color' => 'indigo'],
            'shipping'   => ['text' => 'Đang giao',     'color' => 'orange'],
            'delivered'  => ['text' => 'Đã giao',       'color' => 'green'],
            'cancelled'  => ['text' => 'Đã huỷ',        'color' => 'red'],
            'refunded'   => ['text' => 'Đã hoàn tiền',  'color' => 'gray'],
            default      => ['text' => $this->status,   'color' => 'gray'],
        };
    }
}