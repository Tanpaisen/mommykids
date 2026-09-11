<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUlids;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'dob',
        'gender',
        'loyalty_code',
        'tier',
        'points',
        'total_spent',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'points'            => 'integer',
        'total_spent'       => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->loyalty_code)) {
                do {
                    $code = '893' . mt_rand(1000000000, 9999999999);
                } while (static::where('loyalty_code', $code)->exists());

                $user->loyalty_code = $code;
            }
        });
    }

    public function pointLogs()
    {
        return $this->hasMany(PointLog::class)->latest();
    }

    public function getTierNameAttribute(): string
    {
        return match($this->tier) {
            'silver'  => 'Hạng Bạc',
            'gold'    => 'Hạng Vàng',
            'diamond' => 'Hạng Kim Cương',
            default   => 'Thành viên',
        };
    }

    /**
     * Cập nhật phân hạng dựa trên tổng chi tiêu hiện tại
     */
    public function calculateTier(): string
    {
        if ($this->total_spent >= 10000000) {
            return 'diamond';
        } elseif ($this->total_spent >= 5000000) {
            return 'gold';
        } elseif ($this->total_spent >= 2000000) {
            return 'silver';
        }

        return 'member';
    }

    /**
     * Cộng tiền, tích điểm, ghi log lịch sử và tự động nâng hạng
     */
    public function rewardLoyaltyForOrder($order): void
    {
        $orderTotal = is_object($order) ? (float) $order->total_amount : (float) $order;
        $orderId    = is_object($order) ? $order->id : null;

        DB::transaction(function () use ($orderTotal, $orderId) {
            $pointsEarned = (int) floor($orderTotal / 10000);

            // 1. Cập nhật dữ liệu trên Model Memory
            $this->total_spent += $orderTotal;
            if ($pointsEarned > 0) {
                $this->points += $pointsEarned;
            }

            // 2. Tự động tính toán lại Tier
            $this->tier = $this->calculateTier();

            // 3. Lưu toàn bộ thay đổi của User trong 1 query duy nhất
            $this->save();

            // 4. Ghi log tích điểm
            if ($pointsEarned > 0) {
                $this->pointLogs()->create([
                    'order_id'    => $orderId,
                    'points'      => $pointsEarned,
                    'type'        => 'earn',
                    'description' => $orderId ? "Tích điểm từ đơn hàng #{$orderId}" : "Tích điểm từ đơn hàng",
                ]);
            }
        });
    }

    public function getNextTierProgressAttribute(): array
    {
        $spent = (float) $this->total_spent;

        if ($spent < 2000000) {
            $nextTier = 'Hạng Bạc';
            $target   = 2000000;
            $percent  = min(100, round(($spent / $target) * 100));
        } elseif ($spent < 5000000) {
            $nextTier = 'Hạng Vàng';
            $target   = 5000000;
            $percent  = min(100, round((($spent - 2000000) / 3000000) * 100));
        } elseif ($spent < 10000000) {
            $nextTier = 'Hạng Kim Cương';
            $target   = 10000000;
            $percent  = min(100, round((($spent - 5000000) / 5000000) * 100));
        } else {
            return ['next_tier' => 'Cao nhất', 'needed' => 0, 'percent' => 100];
        }

        return [
            'next_tier' => $nextTier,
            'needed'    => max(0, $target - $spent),
            'percent'   => $percent,
        ];
    }
}