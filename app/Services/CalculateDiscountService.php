<?php

namespace App\Services;

use App\Models\Voucher;
use Exception;

class CalculateDiscountService
{
    /**
     * Tính toán số tiền được giảm thực tế
     * 
     * @param Voucher $voucher
     * @param float $eligibleSubtotal Tổng tiền hàng của các món hợp lệ (từ Validation trả về)
     * @param float $shippingFee Phí ship của Giao Hàng Nhanh (nếu có)
     * @return int Số tiền được giảm
     */
    public function calculate(Voucher $voucher, float $eligibleSubtotal, float $shippingFee = 0): int
    {
        $discountAmount = 0;

        switch ($voucher->discount_type) {
            case 'fixed':
                // LUẬT 1: Giảm số tiền cố định. Không bao giờ được phép giảm lố qua số tiền hàng.
                // VD: Giỏ hàng hợp lệ 40K, mã giảm 50K -> Chỉ được giảm tối đa 40K.
                $discountAmount = min($voucher->discount_value, $eligibleSubtotal);
                break;

            case 'percent':
                // LUẬT 2: Giảm phần trăm.
                $calculatedDiscount = $eligibleSubtotal * ($voucher->discount_value / 100);
                
                // Nếu mã có cấu hình mức giảm tối đa (max_discount_amount), ta áp dụng "chốt chặn"
                if ($voucher->max_discount_amount) {
                    $discountAmount = min($calculatedDiscount, $voucher->max_discount_amount);
                } else {
                    $discountAmount = $calculatedDiscount;
                }
                
                // Vẫn phải đảm bảo quy tắc không giảm lố tiền hàng
                $discountAmount = min($discountAmount, $eligibleSubtotal);
                break;

            case 'free_shipping':
                // LUẬT 3: Mã miễn phí vận chuyển.
                // Giới hạn giảm ship lấy từ max_discount_amount (nếu có) hoặc discount_value
                $maxShippingDiscount = $voucher->max_discount_amount ?: $voucher->discount_value;
                
                // Số tiền giảm tối đa chỉ bằng đúng phí ship thực tế.
                // VD: Phí ship 25K, mã giảm ship 30K -> Chỉ giảm 25K.
                $discountAmount = min($shippingFee, $maxShippingDiscount);
                break;

            default:
                throw new Exception('Loại mã giảm giá không được hệ thống hỗ trợ.');
        }

        // VNĐ không có số thập phân lẻ, ép về int để lưu DB cho chuẩn
        return (int) round($discountAmount);
    }
}