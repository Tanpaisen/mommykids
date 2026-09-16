<?php

namespace App\Services;

use App\Models\User;
use App\Models\Voucher;
use Exception;
use Illuminate\Support\Collection;

class VoucherValidationService
{
    /**
     * Kiểm tra toàn bộ điều kiện sử dụng voucher.
     *
     * @return array{is_valid: bool, eligible_items: Collection, eligible_subtotal: int, total_discounted: int}
     */
    public function validate(Voucher $voucher, ?User $user, Collection $cartItems): array
    {
        if ($voucher->status !== 'active') {
            throw new Exception('Mã ưu đãi không tồn tại hoặc chưa được kích hoạt.');
        }

        if ($voucher->starts_at && now()->lt($voucher->starts_at)) {
            throw new Exception('Mã ưu đãi chưa đến thời gian sử dụng.');
        }

        if ($voucher->expires_at && now()->gt($voucher->expires_at)) {
            throw new Exception('Mã ưu đãi đã hết hạn.');
        }

        // V2: type quyết định slot. Mã shipping chỉ dùng free_shipping,
        // mã order không được dùng discount_type free_shipping.
        if ($voucher->type === 'shipping' && $voucher->discount_type !== 'free_shipping') {
            throw new Exception('Mã vận chuyển đang có cấu hình giảm giá không hợp lệ.');
        }

        if ($voucher->type === 'order' && $voucher->discount_type === 'free_shipping') {
            throw new Exception('Mã đơn hàng đang có cấu hình giảm giá không hợp lệ.');
        }

        $this->validateTargetAudience($voucher, $user);

        $eligibleItems = $this->getEligibleCartItems($voucher, $cartItems);

        if ($eligibleItems->isEmpty()) {
            throw new Exception('Giỏ hàng không có sản phẩm thuộc phạm vi áp dụng của mã này.');
        }

        $eligibleSubtotal = (int) $eligibleItems->sum(function ($item) {
            $price = (int) ($item->product?->price ?? $item->price ?? 0);
            $quantity = max(0, (int) ($item->quantity ?? 0));

            return $price * $quantity;
        });

        if ($eligibleSubtotal < (int) $voucher->min_order_amount) {
            $missing = (int) $voucher->min_order_amount - $eligibleSubtotal;
            throw new Exception(
                'Bạn cần mua thêm ' . number_format($missing, 0, ',', '.') .
                'đ sản phẩm hợp lệ để dùng mã này.'
            );
        }

        // Chỉ tính reservation còn hiệu lực; applied/completed luôn được tính.
        $activeUsageQuery = $voucher->usages()
            ->where(function ($query) {
                $query->whereIn('status', ['applied', 'completed'])
                    ->orWhere(function ($reserved) {
                        $reserved->where('status', 'reserved')
                            ->where(function ($time) {
                                $time->whereNull('reserved_until')
                                    ->orWhere('reserved_until', '>', now());
                            });
                    });
            });

        $usageStats = (clone $activeUsageQuery)
            ->selectRaw('COUNT(*) as total_used, COALESCE(SUM(discount_amount), 0) as total_discounted')
            ->first();

        $totalUsed = (int) ($usageStats->total_used ?? 0);
        $totalDiscounted = (int) ($usageStats->total_discounted ?? 0);

        if ($voucher->total_quantity !== null && $totalUsed >= (int) $voucher->total_quantity) {
            throw new Exception('Rất tiếc, số lượng mã ưu đãi đã được sử dụng hết.');
        }

        if ($voucher->total_budget !== null && $totalDiscounted >= (int) $voucher->total_budget) {
            throw new Exception('Ngân sách của chương trình khuyến mãi đã đạt giới hạn.');
        }

        if ($user) {
            $userUsageCount = (clone $activeUsageQuery)
                ->where('user_id', $user->id)
                ->count();

            if ($userUsageCount >= (int) $voucher->usage_limit_per_user) {
                throw new Exception('Bạn đã hết lượt sử dụng mã ưu đãi này.');
            }
        }

        return [
            'is_valid' => true,
            'eligible_items' => $eligibleItems,
            'eligible_subtotal' => $eligibleSubtotal,
            'total_discounted' => $totalDiscounted,
        ];
    }

    private function getEligibleCartItems(Voucher $voucher, Collection $cartItems): Collection
    {
        $conditions = $voucher->conditions()
            ->whereIn('type', ['product', 'category'])
            ->get();

        if ($conditions->isEmpty()) {
            return $cartItems;
        }

        $excludedCategories = $conditions->where('type', 'category')
            ->where('is_include', false)
            ->pluck('value')
            ->map(fn ($value) => (string) $value)
            ->all();

        $excludedProducts = $conditions->where('type', 'product')
            ->where('is_include', false)
            ->pluck('value')
            ->map(fn ($value) => (string) $value)
            ->all();

        $includedCategories = $conditions->where('type', 'category')
            ->where('is_include', true)
            ->pluck('value')
            ->map(fn ($value) => (string) $value)
            ->all();

        $includedProducts = $conditions->where('type', 'product')
            ->where('is_include', true)
            ->pluck('value')
            ->map(fn ($value) => (string) $value)
            ->all();

        return $cartItems->filter(function ($item) use (
            $excludedCategories,
            $excludedProducts,
            $includedCategories,
            $includedProducts
        ) {
            if (!$item->product) {
                return false;
            }

            $productId = (string) $item->product_id;
            $categoryId = (string) $item->product->category_id;

            if (
                in_array($categoryId, $excludedCategories, true) ||
                in_array($productId, $excludedProducts, true)
            ) {
                return false;
            }

            $categoryPass = empty($includedCategories) ||
                in_array($categoryId, $includedCategories, true);

            $productPass = empty($includedProducts) ||
                in_array($productId, $includedProducts, true);

            return $categoryPass && $productPass;
        })->values();
    }

    private function validateTargetAudience(Voucher $voucher, ?User $user): void
    {
        // Module usage hiện bắt buộc user_id, đồng thời tier/specific user
        // cũng cần tài khoản để kiểm tra chính xác.
        if (!$user) {
            throw new Exception('Vui lòng đăng nhập để sử dụng mã ưu đãi.');
        }

        if ($voucher->apply_to === 'specific_users') {
            if (!$voucher->allowedUsers()->where('users.id', $user->id)->exists()) {
                throw new Exception('Mã ưu đãi này không dành cho tài khoản của bạn.');
            }
        }

        if ($voucher->apply_to === 'new_user') {
            $hasCompletedOrders = $user->orders()
                ->where('status', 'completed')
                ->exists();

            if ($hasCompletedOrders) {
                throw new Exception('Mã ưu đãi này chỉ dành cho khách hàng mới.');
            }
        }

        $tierConditions = $voucher->conditions()
            ->where('type', 'tier')
            ->get();

        if ($tierConditions->isNotEmpty()) {
            $userTier = (string) ($user->tier ?? 'member');

            $validTiers = $tierConditions->where('is_include', true)
                ->pluck('value')
                ->map(fn ($value) => (string) $value)
                ->all();

            if (!empty($validTiers) && !in_array($userTier, $validTiers, true)) {
                throw new Exception('Hạng thành viên của bạn chưa đủ điều kiện để áp dụng mã này.');
            }
        }
    }
}
