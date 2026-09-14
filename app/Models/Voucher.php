<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_public'    => 'boolean',
        'auto_apply'   => 'boolean',
        'is_stackable' => 'boolean',
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
        'metadata'     => 'array', // Tự động convert JSON từ DB sang Array trong PHP
    ];

    /**
     * Lấy danh sách các điều kiện áp dụng (Sản phẩm, Danh mục, Hạng thẻ...)
     */
    public function conditions()
    {
        return $this->hasMany(VoucherCondition::class);
    }

    /**
     * Lấy danh sách khách hàng được chỉ định dùng mã này (specific_users)
     */
    public function allowedUsers()
    {
        // Quan hệ nhiều-nhiều qua bảng trung gian voucher_users
        return $this->belongsToMany(User::class, 'voucher_users');
    }

    /**
     * Lịch sử sử dụng của voucher này
     */
    public function usages()
    {
        return $this->hasMany(VoucherUsage::class);
    }
}