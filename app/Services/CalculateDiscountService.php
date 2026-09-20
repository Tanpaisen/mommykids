<?php

namespace App\Services;

use App\Models\Voucher;
use Exception;

class CalculateDiscountService
{
    /**
     * Tính giảm cho 1 voucher — giữ nguyên logic gốc của bạn
     * 
     * @return array{amount: int, shipping_discount: int}
     */
    public function calculate(Voucher $voucher, float $eligibleSubtotal, float $shippingFee = 0): array
    {
        $discountAmount = 0;
        $shippingDiscount = 0;

        switch ($voucher->discount_type) {
            case 'fixed':
                // Giảm cố định — không vượt quá tổng tiền hàng hợp lệ
                $discountAmount = min((int)$voucher->discount_value, (int)$eligibleSubtotal);
                break;

            case 'percent':
                // Giảm theo %
                $calculated = $eligibleSubtotal * ((int)$voucher->discount_value / 100);
                
                if ($voucher->max_discount_amount) {
                    $discountAmount = min($calculated, (int)$voucher->max_discount_amount);
                } else {
                    $discountAmount = $calculated;
                }
                // Không giảm lố
                $discountAmount = min($discountAmount, (int)$eligibleSubtotal);
                break;

            case 'free_shipping':
                $maxShipping = (int)$voucher->max_discount_amount;
                $shippingDiscount = $maxShipping > 0
                    ? min((int)$shippingFee, $maxShipping)
                    : (int)$shippingFee;
                break;

            default:
                throw new Exception('Loại mã giảm giá không được hệ thống hỗ trợ.');
        }

        // Chuẩn hóa VNĐ
        return [
            'amount'            => (int)round($discountAmount),
            'shipping_discount' => (int)round($shippingDiscount),
        ];
    }

    /**
     * Tính tổng giảm cho NHIỀU voucher — hỗ trợ is_stackable
     * 
     * @param Voucher[] $vouchers
     * @return array{total_discount: int, shipping_discount: int, applied: array}
     */
    public function calculateMany(array $vouchers, float $eligibleSubtotal, float $shippingFee = 0): array
    {
        $stackableTotal = 0;
        $soloBest = ['amount' => 0, 'shipping_discount' => 0];
        $applied = [];

        foreach ($vouchers as $voucher) {
            ['amount' => $amt, 'shipping_discount' => $ship] = $this->calculate(
                $voucher,
                $eligibleSubtotal,
                $shippingFee
            );

            if ($voucher->is_stackable) {
                // Cộng dồn các mã cho phép chồng
                $stackableTotal += $amt;
                $applied[] = ['voucher' => $voucher, 'discount' => $amt, 'shipping_discount' => $ship, 'mode' => 'stacked'];
            } else {
                // Chọn mã đơn lẻ tốt nhất
                $totalCurrent = $amt + $ship;
                $totalBest = $soloBest['amount'] + $soloBest['shipping_discount'];
                if ($totalCurrent > $totalBest) {
                    $soloBest = ['amount' => $amt, 'shipping_discount' => $ship];
                    $applied = array_filter($applied, fn($a) => $a['mode'] !== 'solo');
                    $applied[] = ['voucher' => $voucher, 'discount' => $amt, 'shipping_discount' => $ship, 'mode' => 'solo'];
                }
            }
        }

        // Giới hạn tổng không vượt quá tiền hàng
        $totalDiscount = min($stackableTotal + $soloBest['amount'], (int)$eligibleSubtotal);
        $shippingDiscount = max($stackableTotal > 0 ? 0 : $soloBest['shipping_discount'], 0);

        return [
            'total_discount'    => $totalDiscount,
            'shipping_discount' => $shippingDiscount,
            'applied'           => $applied,
        ];
    }
}