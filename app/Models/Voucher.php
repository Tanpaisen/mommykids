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
        'require_save_to_user'=> 'boolean',
        'is_stackable' => 'boolean',
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
        'metadata'     => 'array', 
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
    public function savedUsers()
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

    /**
     * SCOPE: Lọc voucher đang HOẠT ĐỘNG (trong thời gian + còn lượt)
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
            ->where(fn ($q) => $q->whereNull('total_quantity')->orWhereColumn('total_quantity', '>', 'used_count'));
    }

    // Scope: Voucher cần lưu vào tài khoản
    public function scopeRequireSaved($query)
    {
        return $query->where('require_save_to_user', true);
    }

    // Scope: Voucher áp dụng ngay không cần lưu
    public function scopeAutoApplyAvailable($query)
    {
        return $query->where('require_save_to_user', false)
            ->where('is_public', true)
            ->active();
    }

    // /**
    //  * KIỂM TRA: Voucher này có áp dụng được cho đơn không?
    //  */
    public function isApplicable($subtotal, $userId = null, $userTier = null): bool
    {
        $now = now();

        // 1. Trạng thái & thời gian
        if ($this->status !== 'active') return false;
        if ($this->starts_at && $this->starts_at->gt($now)) return false;
        if ($this->expires_at && $this->expires_at->lt($now)) return false;

        // 2. Giá trị đơn tối thiểu
        if ($subtotal < $this->min_order_amount) return false;

        // 3. Còn lượt dùng toàn hệ thống
        if ($this->total_quantity && $this->used_count >= $this->total_quantity) return false;

        // 4. Giới hạn mỗi người dùng
        if ($userId && $this->usage_limit_per_user > 0) {
            $used = $this->usages()->where('user_id', $userId)->count();
            if ($used >= $this->usage_limit_per_user) return false;
        }

        // 5. Kiểm tra hạng thành viên (nếu có điều kiện tier)
        if ($userTier && $this->conditions()->where('type', 'tier')->exists()) {
            $allowedTiers = $this->conditions()->where('type', 'tier')->pluck('value')->toArray();
            if (!in_array($userTier, $allowedTiers)) return false;
        }

        return [
            'saved' => $savedVouchers,
            'recommended' => $autoVouchers,
        ];
    }
}