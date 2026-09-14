<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class VoucherValidationService
{
    /**
     * Hàm main xử lý toàn bộ logic kiểm duyệt
     */
    public function validate(Voucher $voucher, User $user, Collection $cartItems)
    {
        // 1. KIỂM TRA THỜI GIAN & TRẠNG THÁI
        if ($voucher->status !== 'active') {
            throw new Exception('Mã ưu đãi không tồn tại hoặc chưa được kích hoạt.');
        }
        if (now()->lessThan($voucher->starts_at) || now()->greaterThan($voucher->expires_at)) {
            throw new Exception('Mã ưu đãi chưa diễn ra hoặc đã hết hạn.');
        }

        // 2. KIỂM TRA ĐỐI TƯỢNG (TIER & USER)
        $this->validateTargetAudience($voucher, $user);

        // 3. LỌC SẢN PHẨM HỢP LỆ & TÍNH TỔNG TIỀN ĐỦ ĐIỀU KIỆN
        $eligibleItems = $this->getEligibleCartItems($voucher, $cartItems);
        
        if ($eligibleItems->isEmpty()) {
            throw new Exception('Giỏ hàng của bạn không có sản phẩm nào nằm trong danh mục áp dụng mã ưu đãi này.');
        }

        // Giả sử $item->total_price là (giá * số lượng) của từng món
        $eligibleSubtotal = $eligibleItems->sum('total_price');

        // 4. KIỂM TRA MIN ORDER DỰA TRÊN SẢN PHẨM HỢP LỆ
        if ($eligibleSubtotal < $voucher->min_order_amount) {
            $missing = $voucher->min_order_amount - $eligibleSubtotal;
            throw new Exception("Bạn cần mua thêm " . number_format($missing) . "đ các sản phẩm hợp lệ để dùng mã này.");
        }

        // 5. KIỂM TRA LƯỢT DÙNG TỔNG & NGÂN SÁCH (Chống Race Condition)
        $usageStats = $voucher->usages()
            ->whereIn('status', ['reserved', 'applied', 'completed'])
            ->selectRaw('COUNT(*) as total_used, SUM(discount_amount) as total_discounted')
            ->first();

        if ($voucher->total_quantity && $usageStats->total_used >= $voucher->total_quantity) {
            throw new Exception('Rất tiếc, số lượng mã ưu đãi đã được sử dụng hết.');
        }
        if ($voucher->total_budget && $usageStats->total_discounted >= $voucher->total_budget) {
            throw new Exception('Ngân sách của chương trình khuyến mãi đã đạt giới hạn.');
        }

        // 6. KIỂM TRA LƯỢT CÁ NHÂN
        $userUsageCount = $voucher->usages()
            ->where('user_id', $user->id)
            ->whereIn('status', ['reserved', 'applied', 'completed'])
            ->count();

        if ($userUsageCount >= $voucher->usage_limit_per_user) {
            throw new Exception('Bạn đã hết lượt sử dụng mã ưu đãi này.');
        }

        // Trả về danh sách sản phẩm hợp lệ và tổng tiền hợp lệ để bước sau (CalculateDiscount) tính toán
        return [
            'is_valid' => true,
            'eligible_items' => $eligibleItems,
            'eligible_subtotal' => $eligibleSubtotal
        ];
    }

    /**
     * BỘ LỌC ĐA MƯU: Bóc tách chính xác sản phẩm nào được phép giảm giá
     */
    private function getEligibleCartItems(Voucher $voucher, Collection $cartItems)
    {
        $conditions = $voucher->conditions()->whereIn('type', ['product', 'category'])->get();

        // Nếu không có điều kiện ràng buộc nào -> Toàn bộ giỏ hàng đều hợp lệ
        if ($conditions->isEmpty()) {
            return $cartItems;
        }

        // Phân loại các nhóm điều kiện để xử lý logic AND/OR
        $excludedCategories = $conditions->where('type', 'category')->where('is_include', false)->pluck('value')->toArray();
        $excludedProducts   = $conditions->where('type', 'product')->where('is_include', false)->pluck('value')->toArray();
        
        $includedCategories = $conditions->where('type', 'category')->where('is_include', true)->pluck('value')->toArray();
        $includedProducts   = $conditions->where('type', 'product')->where('is_include', true)->pluck('value')->toArray();

        // Dùng Collection Filter để duyệt qua từng món trong giỏ
        return $cartItems->filter(function ($item) use ($excludedCategories, $excludedProducts, $includedCategories, $includedProducts) {
            // Lưu ý: Đảm bảo relation 'product' đã được load cùng cartItem
            $productId = (string) $item->product_id;
            $categoryId = (string) $item->product->category_id; 

            // 1. RULE TỐI THƯỢNG: Danh sách bị cấm (Blacklist) luôn ghi đè tất cả
            if (in_array($categoryId, $excludedCategories) || in_array($productId, $excludedProducts)) {
                return false;
            }

            // 2. Kiểm tra danh sách được phép (Whitelist)
            // Nếu mảng include trống nghĩa là không giới hạn type đó (Mặc định Pass)
            $categoryPass = empty($includedCategories) || in_array($categoryId, $includedCategories);
            $productPass  = empty($includedProducts) || in_array($productId, $includedProducts);

            // Vì khác type nên dùng logic AND (Phải khớp cả rule Category VÀ rule Product nếu có)
            return $categoryPass && $productPass;
        });
    }

    /**
     * Xử lý riêng logic về phân hạng và quyền người dùng
     */
    private function validateTargetAudience(Voucher $voucher, User $user)
    {
        // Khách hàng cụ thể
        if ($voucher->apply_to === 'specific_users') {
            if (!$voucher->allowedUsers()->where('users.id', $user->id)->exists()) {
                throw new Exception('Mã ưu đãi này không dành cho tài khoản của bạn.');
            }
        }

        // Khách hàng mới
        if ($voucher->apply_to === 'new_user') {
            $hasCompletedOrders = $user->orders()->where('status', 'completed')->exists();
            if ($hasCompletedOrders) {
                throw new Exception('Mã ưu đãi này chỉ dành cho khách hàng mới.');
            }
        }

        // Hạng thành viên (Tiers) thông qua voucher_conditions
        $tierConditions = $voucher->conditions()->where('type', 'tier')->get();
        
        if ($tierConditions->isNotEmpty()) {
            $userTier = $user->tier ?? 'member';
            
            // Lấy danh sách các hạng được phép áp dụng (is_include = true)
            $validTiers = $tierConditions->where('is_include', true)->pluck('value')->toArray();
            
            if (!empty($validTiers) && !in_array($userTier, $validTiers)) {
                throw new Exception('Hạng thành viên của bạn chưa đủ điều kiện để áp dụng mã này.');
            }
        }
    }
}