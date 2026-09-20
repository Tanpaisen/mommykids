<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VoucherApplicationService
{
    public function __construct(
        protected VoucherValidationService $validator,
        protected CalculateDiscountService $calculator
    ) {}

    /**
     * Áp dụng mã tại trang thanh toán → tạo bản ghi reserved (giữ chỗ 15 phút).
     * Trả về mảng thông tin giảm giá để hiển thị ngay.
     */
    public function apply(string $code, User $user, Collection $cartItems, float $shippingFee = 0): array
    {
        $code = strtoupper(trim($code));

        $voucher = Voucher::where('code', $code)->first();
        if (!$voucher) {
            throw new Exception('Mã giảm giá không tồn tại.');
        }

        // 1. Validate đầy đủ (sẽ throw nếu không hợp lệ)
        $result = $this->validator->validate($voucher, $user, $cartItems);

        // 2. Tính giảm giá thực tế
        $discount = $this->calculator->calculate(
            $voucher,
            $result['eligible_subtotal'],
            $shippingFee
        );

        $totalDiscount = $discount['amount'] + $discount['shipping_discount'];
        if ($totalDiscount <= 0) {
            throw new Exception('Mã này không tạo được mức giảm cho đơn hàng của bạn.');
        }

        return DB::transaction(function () use ($voucher, $user, $result, $discount, $totalDiscount, $shippingFee) {
            // Hủy các reserved cũ của user cho cùng slot (order/shipping) — 1 slot 1 mã
            $this->cancelReservedBySlot($user, $voucher->type);

            // Tạo bản ghi giữ chỗ
            $usage = VoucherUsage::create([
                'voucher_id'      => $voucher->id,
                'user_id'         => $user->id,
                'order_id'        => null, // sẽ gắn khi tạo đơn
                'voucher_code'    => $voucher->code,
                'voucher_name'    => $voucher->name,
                'discount_type'   => $voucher->discount_type,
                'discount_value'  => $voucher->discount_value,
                'order_subtotal'  => $result['eligible_subtotal'],
                'shipping_fee'    => $shippingFee,
                'discount_amount' => $totalDiscount,
                'final_total'     => null,
                'status'          => 'reserved',
                'reserved_until'  => now()->addMinutes(15),
            ]);

            // Tăng đếm lượt dùng (reserved cũng tính, tránh chạy quá số lượng)
            $voucher->increment('used_count');

            return [
                'usage_id'          => $usage->id,
                'code'              => $voucher->code,
                'name'              => $voucher->name,
                'type'              => $voucher->type,
                'discount_amount'   => $discount['amount'],
                'shipping_discount' => $discount['shipping_discount'],
                'total_discount'    => $totalDiscount,
                'message'           => 'Áp dụng mã thành công!',
            ];
        });
    }

    /**
     * Hủy mã đang giữ chỗ tại checkout.
     */
    public function remove(string $code, User $user): array
    {
        $code = strtoupper(trim($code));

        $usage = VoucherUsage::where('voucher_code', $code)
            ->where('user_id', $user->id)
            ->where('status', 'reserved')
            ->whereNull('order_id')
            ->latest()
            ->first();

        if (!$usage) {
            throw new Exception('Không tìm thấy mã đang áp dụng.');
        }

        DB::transaction(function () use ($usage) {
            $usage->update([
                'status'        => 'cancelled',
                'cancelled_at'  => now(),
                'cancel_reason' => 'Người dùng hủy tại trang thanh toán',
            ]);

            Voucher::where('id', $usage->voucher_id)->decrement('used_count');
        });

        return ['message' => 'Đã hủy áp dụng mã.'];
    }

    /**
     * Khi tạo đơn hàng thành công → gắn order_id + chuyển reserved → applied.
     * Gọi trong CheckoutController@store sau khi tạo Order.
     */
    public function attachToOrder(Order $order, User $user): array
    {
        $usages = VoucherUsage::where('user_id', $user->id)
            ->where('status', 'reserved')
            ->whereNull('order_id')
            ->where(function ($q) {
                $q->whereNull('reserved_until')->orWhere('reserved_until', '>', now());
            })
            ->get();

        if ($usages->isEmpty()) {
            return ['applied' => 0, 'total_discount' => 0];
        }

        $totalDiscount = 0;

        DB::transaction(function () use ($usages, $order, &$totalDiscount) {
            foreach ($usages as $usage) {
                $usage->update([
                    'order_id'    => $order->id,
                    'final_total' => $order->total,
                    'status'      => 'applied',
                    'applied_at'  => now(),
                ]);
                $totalDiscount += (int) $usage->discount_amount;
            }
        });

        return ['applied' => $usages->count(), 'total_discount' => $totalDiscount];
    }

    /**
     * Khi thanh toán thành công / đơn hoàn tất → chuyển applied → completed.
     */
    public function complete(Order $order): void
    {
        VoucherUsage::where('order_id', $order->id)
            ->where('status', 'applied')
            ->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
    }

    /**
     * Hủy đơn → hủy toàn bộ voucher usage của đơn, hoàn lại used_count.
     */
    public function cancelByOrder(Order $order, string $reason = 'Đơn hàng bị hủy'): void
    {
        $usages = VoucherUsage::where('order_id', $order->id)
            ->whereIn('status', ['reserved', 'applied'])
            ->get();

        DB::transaction(function () use ($usages, $reason) {
            foreach ($usages as $usage) {
                $usage->update([
                    'status'        => 'cancelled',
                    'cancelled_at'  => now(),
                    'cancel_reason' => $reason,
                ]);
                Voucher::where('id', $usage->voucher_id)->decrement('used_count');
            }
        });
    }

    /**
     * Dọn các reserved hết hạn (chạy bằng Scheduler mỗi 5 phút).
     * Trả về số bản ghi đã thu hồi.
     */
    public function releaseExpiredReservations(): int
    {
        $expired = VoucherUsage::where('status', 'reserved')
            ->where('reserved_until', '<', now())
            ->get();

        DB::transaction(function () use ($expired) {
            foreach ($expired as $usage) {
                $usage->update([
                    'status'        => 'cancelled',
                    'cancelled_at'  => now(),
                    'cancel_reason' => 'Hết thời gian giữ chỗ',
                ]);
                Voucher::where('id', $usage->voucher_id)->decrement('used_count');
            }
        });

        return $expired->count();
    }

    /**
     * Lấy danh sách mã đang giữ chỗ của user (để hiển thị ở checkout).
     */
    public function getActiveReserved(User $user): Collection
    {
        return VoucherUsage::where('user_id', $user->id)
            ->where('status', 'reserved')
            ->whereNull('order_id')
            ->where(function ($q) {
                $q->whereNull('reserved_until')->orWhere('reserved_until', '>', now());
            })
            ->get();
    }

    /**
     * Hủy reserved cũ theo slot (mỗi slot order/shipping chỉ giữ 1 mã).
     */
    protected function cancelReservedBySlot(User $user, string $type): void
    {
        $oldUsages = VoucherUsage::where('user_id', $user->id)
            ->where('status', 'reserved')
            ->whereNull('order_id')
            ->whereHas('voucher', fn ($q) => $q->where('type', $type))
            ->get();

        foreach ($oldUsages as $old) {
            $old->update([
                'status'        => 'cancelled',
                'cancelled_at'  => now(),
                'cancel_reason' => 'Thay bằng mã khác cùng loại',
            ]);
            Voucher::where('id', $old->voucher_id)->decrement('used_count');
        }
    }
}