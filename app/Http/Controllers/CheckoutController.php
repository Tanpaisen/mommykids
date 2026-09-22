<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Services\CalculateDiscountService;
use App\Services\CartService;
use App\Services\GHNService;
use App\Services\VoucherValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected GHNService $ghn,
        protected VoucherValidationService $voucherValidation,
        protected CalculateDiscountService $discountCalculator,
    ) {}

    public function index()
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->total();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $shippingFee = 0;
        $usedPoints = (int) session('used_points', 0);
        $pointsDiscount = (int) session('point_discount', 0);

        $voucherBreakdown = $this->calculateVoucherBreakdown(
            $items,
            (int) $subtotal,
            $shippingFee,
            $user,
            false,
            false
        );

        $pointsDiscount = min(
            $pointsDiscount,
            max(0, (int) $subtotal - $voucherBreakdown['order_discount'])
        );

        $total = max(
            0,
            (int) $subtotal
            - $voucherBreakdown['order_discount']
            + $shippingFee
            - $voucherBreakdown['shipping_discount']
            - $pointsDiscount
        );

        $provinceResponse = $this->ghn->getProvinces();

        if (
            isset($provinceResponse['data']) &&
            is_array($provinceResponse['data'])
        ) {
            $provinceResponse = $provinceResponse['data'];
        }

        if (
            isset($provinceResponse['ProvinceID']) &&
            isset($provinceResponse['ProvinceName'])
        ) {
            $provinceResponse = [$provinceResponse];
        }

        $provinces = collect($provinceResponse)
            ->filter(function ($province) {
                return is_array($province)
                    && isset($province['ProvinceID'])
                    && isset($province['ProvinceName']);
            })
            ->values()
            ->all();

        $checkoutVouchers = session('checkout_vouchers', []);

        // Chỉ hiển thị các voucher hiện có trong ví của người dùng và
        // đang hợp lệ với giỏ hàng hiện tại. Người dùng không cần nhớ mã.
        $availableOrderVouchers = $this->getAvailableCheckoutVouchers(
            $user,
            $items,
            'order'
        );
        $availableShippingVouchers = $this->getAvailableCheckoutVouchers(
            $user,
            $items,
            'shipping'
        );

        return view('checkout.index', compact(
            'user',
            'items',
            'subtotal',
            'shippingFee',
            'usedPoints',
            'pointsDiscount',
            'total',
            'provinces',
            'voucherBreakdown',
            'checkoutVouchers',
            'availableOrderVouchers',
            'availableShippingVouchers'
        ));
    }

    public function districts(Request $request)
    {
        $data = $request->validate([
            'province_id' => ['required', 'integer'],
        ]);

        session()->forget('checkout_shipping_fee');

        $response = $this->ghn->getDistricts((int) $data['province_id']);

        return response()->json(
            $this->normalizeGhnList($response)
        );
    }

    public function wards(Request $request)
    {
        $data = $request->validate([
            'district_id' => ['required', 'integer'],
        ]);

        session()->forget('checkout_shipping_fee');

        $response = $this->ghn->getWards((int) $data['district_id']);

        return response()->json(
            $this->normalizeGhnList($response)
        );
    }

    public function calculateShippingFee(Request $request)
    {
        $data = $request->validate([
            'district_id' => ['required', 'integer'],
            'ward_code' => ['required', 'string'],
        ]);

        $items = $this->cart->items();
        $subtotal = (int) $this->cart->total();
        if ($this->cart->hasMissingWeights()) {
    return response()->json([
        'message' =>
            'Có sản phẩm chưa khai báo khối lượng. '
            . 'Không thể tính chính xác phí GHN.',
    ], 422);
}

$weight = $this->cart->totalWeightGrams();

if ($weight <= 0) {
    return response()->json([
        'message' =>
            'Tổng khối lượng giỏ hàng không hợp lệ.',
    ], 422);
}

        $feeData = $this->ghn->calculateFee(
            (int) $data['district_id'],
            $data['ward_code'],
            $weight,
            $subtotal
        );

        $shippingFee = $this->extractShippingFee($feeData);

        if ($shippingFee <= 0) {
            return response()->json([
                'message' => 'Không tính được phí vận chuyển GHN.',
                'ghn' => $feeData,
            ], 422);
        }

        session(['checkout_shipping_fee' => $shippingFee]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $voucherBreakdown = $this->calculateVoucherBreakdown(
            $items,
            $subtotal,
            $shippingFee,
            $user,
            false,
            false
        );
$pointsDiscount = min(
    (int) session('point_discount', 0),
    max(0, $subtotal - $voucherBreakdown['order_discount'])
);

$finalTotal = max(
    0,
    $subtotal
    - $voucherBreakdown['order_discount']
    + $shippingFee
    - $voucherBreakdown['shipping_discount']
    - $pointsDiscount
);

return response()->json([
    'subtotal' => $subtotal,
    'shipping_fee' => $shippingFee,
    'order_voucher_discount' => $voucherBreakdown['order_discount'],
    'shipping_voucher_discount' => $voucherBreakdown['shipping_discount'],
    'points_discount' => $pointsDiscount,
    'total' => $finalTotal,
    'voucher_messages' => $voucherBreakdown['messages'],
]);
}

    /**
     * Áp dụng đúng 1 voucher vào một trong hai slot: order hoặc shipping.
     */
    public function applyVoucher(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng mã ưu đãi.',
            ], 401);
        }

        $data = $request->validate([
            'voucher_id' => ['required', 'string'],
            'type'       => ['required', 'in:order,shipping'],
        ]);

        $type = $data['type'];
        $user = Auth::user();
        $items = $this->cart->items();
        $subtotal = (int) $this->cart->total();
        $shippingFee = (int) session('checkout_shipping_fee', 0);

        try {
            // 🔒 Toàn bộ trong transaction + lock row voucher
            $validationResult = DB::transaction(function () use (
                $data, $type, $user, $items, $subtotal, $shippingFee
            ) {
                $voucher = Voucher::where('id', $data['voucher_id'])
                    ->where('type', $type)
                    ->active()
                    ->lockForUpdate() // 🔒 Khóa row chống 2 request cùng lúc
                    ->first();

                if (!$voucher) {
                    throw new \RuntimeException('Mã ưu đãi không tồn tại hoặc đã hết hạn.');
                }

                $isSaved = $user->savedVouchers()->where('vouchers.id', $voucher->id)->exists();
                $isAutoApply = !$voucher->require_save_to_user && $voucher->is_public;
                if (!$isSaved && !$isAutoApply) {
                    throw new \RuntimeException('Mã ưu đãi này không có trong ví của bạn.');
                }

                $validation = $this->voucherValidation->validate($voucher, $user, $items);

                // ✅ SỬA: tách đúng amount / shipping_discount
                $discount = $this->discountCalculator->calculate(
                    $voucher,
                    (float) $validation['eligible_subtotal'],
                    (float) $shippingFee
                );

                $totalDiscount = (int) $discount['amount'] + (int) $discount['shipping_discount'];
                if ($totalDiscount <= 0 && $type !== 'shipping') {
                    throw new \RuntimeException('Mã này không tạo được mức giảm.');
                }

                // Lưu vào session (1 slot = 1 mã, ghi đè mã cũ cùng loại)
                $slots = session('checkout_vouchers', []);
                $slots[$type] = [
                    'id'   => (string) $voucher->id,
                    'code' => $voucher->code,
                    'type' => $voucher->type,
                    'name' => $voucher->name,
                ];
                session(['checkout_vouchers' => $slots]);

                return compact('voucher', 'discount', 'totalDiscount');
            });

            $voucher = $validationResult['voucher'];
            $discount = $validationResult['discount'];

            // Tính lại tổng tiền (không cần lock nữa)
            $breakdown = $this->calculateVoucherBreakdown($items, $subtotal, $shippingFee, $user, false, false);
            $pointsDiscount = min(
                (int) session('point_discount', 0),
                max(0, $subtotal - $breakdown['order_discount'])
            );
            $total = max(
                0,
                $subtotal
                - $breakdown['order_discount']
                + $shippingFee
                - $breakdown['shipping_discount']
                - $pointsDiscount
            );

            $message = $type === 'shipping' && $shippingFee <= 0
                ? 'Đã lưu mã vận chuyển. Mức giảm sẽ tính sau khi có phí ship.'
                : 'Áp dụng mã ưu đãi thành công.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'voucher' => [
                    'id'   => (string) $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                    'type' => $voucher->type,
                    'discount' => $discount,
                ],
                'pricing' => [
                    'subtotal'                 => $subtotal,
                    'shipping_fee'             => $shippingFee,
                    'order_voucher_discount'   => $breakdown['order_discount'],
                    'shipping_voucher_discount'=> $breakdown['shipping_discount'],
                    'points_discount'          => $pointsDiscount,
                    'total'                    => $total,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function removeVoucher(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:order,shipping'],
        ]);

        $slots = session('checkout_vouchers', []);
        unset($slots[$data['type']]);
        session(['checkout_vouchers' => $slots]);

        $items = $this->cart->items();
        $subtotal = (int) $this->cart->total();
        $shippingFee = (int) session('checkout_shipping_fee', 0);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $breakdown = $this->calculateVoucherBreakdown(
            $items,
            $subtotal,
            $shippingFee,
            $user,
            false,
            false
        );

        $pointsDiscount = min(
            (int) session('point_discount', 0),
            max(0, $subtotal - $breakdown['order_discount'])
        );

        $total = max(
            0,
            $subtotal
            - $breakdown['order_discount']
            + $shippingFee
            - $breakdown['shipping_discount']
            - $pointsDiscount
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã gỡ mã ưu đãi.',
            'pricing' => [
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'order_voucher_discount' => $breakdown['order_discount'],
                'shipping_voucher_discount' => $breakdown['shipping_discount'],
                'points_discount' => $pointsDiscount,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Áp dụng điểm tích lũy vào đơn hàng (AJAX).
     */
    public function applyPoints(Request $request)
    {
        $request->validate([
            'points' => ['required', 'integer', 'min:1'],
        ], [
            'points.required' => 'Vui lòng nhập số điểm.',
            'points.integer' => 'Số điểm phải là số nguyên.',
            'points.min' => 'Số điểm sử dụng phải lớn hơn 0.',
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Vui lòng đăng nhập để dùng điểm.',
            ], 401);
        }

        $requestedPoints = (int) $request->input('points');

        if ($requestedPoints > (int) $user->points) {
            return response()->json([
                'message' => 'Bạn chỉ có tối đa ' . number_format($user->points) . ' điểm.',
            ], 422);
        }

        $items = $this->cart->items();
        $subtotal = (int) $this->cart->total();
        $shippingFee = (int) session('checkout_shipping_fee', 0);

        $breakdown = $this->calculateVoucherBreakdown(
            $items,
            $subtotal,
            $shippingFee,
            $user,
            false,
            false
        );

        $pointRate = 1000;
        $calculatedDiscount = $requestedPoints * $pointRate;

        // Giữ hành vi cũ: điểm chỉ giảm trên tiền hàng, không ăn vào phí ship.
        $maxPointDiscount = max(
            0,
            $subtotal - $breakdown['order_discount']
        );

        if ($calculatedDiscount > $maxPointDiscount) {
            $requestedPoints = (int) floor($maxPointDiscount / $pointRate);
            $calculatedDiscount = $requestedPoints * $pointRate;
        }

        if ($requestedPoints <= 0 || $calculatedDiscount <= 0) {
            return response()->json([
                'message' => 'Giá trị đơn hàng còn lại không đủ để sử dụng điểm.',
            ], 422);
        }

        session([
            'used_points' => $requestedPoints,
            'point_discount' => $calculatedDiscount,
        ]);

        $total = max(
            0,
            $subtotal
            - $breakdown['order_discount']
            + $shippingFee
            - $breakdown['shipping_discount']
            - $calculatedDiscount
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã áp dụng điểm tích lũy thành công!',
            'used_points' => $requestedPoints,
            'points_discount' => $calculatedDiscount,
            'discount_fmt' => number_format($calculatedDiscount) . 'đ',
            'total' => $total,
        ]);
    }

    public function removePoints()
    {
        session()->forget(['used_points', 'point_discount']);

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy áp dụng điểm tích lũy.',
        ]);
    }

    public function store(Request $request)
    {
        $items = $this->cart->items();
        $subtotal = (int) $this->cart->total();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $usedPoints = (int) session('used_points', 0);
        $requestedPointsDiscount = (int) session('point_discount', 0);

        if ($user && $usedPoints > (int) $user->points) {
            return back()->withInput()
                ->with('error', 'Số điểm tích lũy của bạn không đủ để thực hiện giao dịch.');
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'province_id' => ['required', 'integer'],
            'to_district_id' => ['required', 'integer'],
            'to_ward_code' => ['required', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:cod,bank,zalopay,stripe,paypal'],
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'email.email' => 'Email không đúng định dạng.',
            'province_id.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'to_district_id.required' => 'Vui lòng chọn quận/huyện.',
            'to_ward_code.required' => 'Vui lòng chọn phường/xã.',
            'address.required' => 'Vui lòng nhập địa chỉ nhận hàng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        if ($this->cart->hasMissingWeights()) {
    return back()
        ->withInput()
        ->with(
            'error',
            'Có sản phẩm chưa khai báo khối lượng. '
            . 'Không thể tính chính xác phí vận chuyển GHN.'
        );
}

$weight = $this->cart->totalWeightGrams();

if ($weight <= 0) {
    return back()
        ->withInput()
        ->with(
            'error',
            'Tổng khối lượng đơn hàng không hợp lệ.'
        );
}
        $feeData = $this->ghn->calculateFee(
            (int) $data['to_district_id'],
            $data['to_ward_code'],
            $weight,
            $subtotal
        );

        $shippingFee = $this->extractShippingFee($feeData);

        if ($shippingFee <= 0) {
            return back()->withInput()
                ->with(
                    'error',
                    'Không thể tính phí vận chuyển GHN. Vui lòng kiểm tra lại địa chỉ.'
                );
        }

        $provinces = $this->normalizeGhnList($this->ghn->getProvinces());
        $districts = $this->normalizeGhnList(
            $this->ghn->getDistricts((int) $data['province_id'])
        );
        $wards = $this->normalizeGhnList(
            $this->ghn->getWards((int) $data['to_district_id'])
        );

        $provinceName = $this->findGhnName(
            $provinces,
            'ProvinceID',
            $data['province_id'],
            'ProvinceName'
        );

        $districtName = $this->findGhnName(
            $districts,
            'DistrictID',
            $data['to_district_id'],
            'DistrictName'
        );

        $wardName = $this->findGhnName(
            $wards,
            'WardCode',
            $data['to_ward_code'],
            'WardName'
        );

        if (!$provinceName || !$districtName || !$wardName) {
            return back()->withInput()
                ->with('error', 'Không thể xác định đầy đủ địa chỉ giao hàng.');
        }

        try {
            $checkoutResult = DB::transaction(function () use (
                $data,
                $items,
                $subtotal,
                $shippingFee,
                $requestedPointsDiscount,
                $provinceName,
                $districtName,
                $wardName,
                $user
            ) {
                // Re-validate voucher ngay trước khi tạo đơn và khóa row voucher
                // để hạn chế race condition về lượt dùng/ngân sách.
                $voucherBreakdown = $this->calculateVoucherBreakdown(
                    $items,
                    $subtotal,
                    $shippingFee,
                    $user,
                    true,
                    true
                );

                $pointsDiscount = min(
                    $requestedPointsDiscount,
                    max(0, $subtotal - $voucherBreakdown['order_discount'])
                );

                // 1 điểm = 1.000đ, vì vậy không tiêu một phần điểm.
                $pointsDiscount = intdiv(max(0, $pointsDiscount), 1000) * 1000;
                $effectiveUsedPoints = intdiv($pointsDiscount, 1000);

                $total = max(
                    0,
                    $subtotal
                    - $voucherBreakdown['order_discount']
                    + $shippingFee
                    - $voucherBreakdown['shipping_discount']
                    - $pointsDiscount
                );

                $aggregateDiscount =
                    $voucherBreakdown['order_discount']
                    + $voucherBreakdown['shipping_discount']
                    + $pointsDiscount;

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'recipient_name' => $data['full_name'],
                    'recipient_phone' => $data['phone'],
                    'recipient_email' => $data['email'] ?? null,
                    'province_name' => $provinceName,
                    'district_name' => $districtName,
                    'points_used' => $effectiveUsedPoints,
                    'points_discount' => $pointsDiscount,
                    'ward_name' => $wardName,
                    'address_detail' => $data['address'],
                    'ghn_province_id' => (int) $data['province_id'],
                    'ghn_district_id' => (int) $data['to_district_id'],
                    'ghn_ward_code' => $data['to_ward_code'],
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'discount' => $aggregateDiscount,
                    'total' => $total,
                    'status' => 'pending',
                    'payment_method' => match ($data['payment_method']) {
                        'bank' => 'qr',
                        default => $data['payment_method'],
                    },
                    'payment_status' => 'unpaid',
                    'note' => $data['note'] ?? null,
                ]);

                foreach ($items as $item) {
                    $product = $item->product;

                    if (!$product) {
                        continue;
                    }

                    $price = (int) $product->price;
                    $quantity = (int) $item->quantity;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku ?? null,
                        'price' => $price,
                        'quantity' => $quantity,
                        'subtotal' => $price * $quantity,
                    ]);
                }

                if ($user) {
                    foreach ($voucherBreakdown['details'] as $detail) {
                        /** @var Voucher $voucher */
                        $voucher = $detail['voucher'];
                        $discount = (int) $detail['discount'];

                        if ($discount <= 0 && $voucher->type !== 'shipping') {
                            continue;
                        }

                        // Khi khách bấm Đặt hàng, chỉ GIỮ CHỖ voucher.
                        // Chưa tăng used_count cho tới khi đơn COD được xác nhận
                        // hoặc thanh toán online đã thực sự thành công.
                        $voucher->usages()->create([
                            'user_id' => $user->id,
                            'order_id' => $order->id,
                            'voucher_code' => $voucher->code,
                            'voucher_name' => $voucher->name,
                            'discount_type' => $voucher->discount_type,
                            'discount_value' => (int) $voucher->discount_value,
                            'order_subtotal' => $subtotal,
                            'shipping_fee' => $shippingFee,
                            'discount_amount' => $discount,
                            'final_total' => $total,
                            'status' => 'reserved',
                            'reserved_until' => now()->addMinutes(15),
                        ]);
                    }
                }

                return [
                    'order' => $order,
                    'voucher_breakdown' => $voucherBreakdown,
                    'points_discount' => $pointsDiscount,
                    'used_points' => $effectiveUsedPoints,
                    'total' => $total,
                ];
            });
        } catch (Throwable $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }

        /** @var Order $dbOrder */
        $dbOrder = $checkoutResult['order'];
        $voucherBreakdown = $checkoutResult['voucher_breakdown'];
        $pointsDiscount = (int) $checkoutResult['points_discount'];
        $usedPoints = (int) $checkoutResult['used_points'];
        $total = (int) $checkoutResult['total'];

        $paymentCode = 'MK' . now()->format('ymdHis');

        if ($user && $usedPoints > 0 && $pointsDiscount > 0) {
            DB::transaction(function () use ($user, $usedPoints, $paymentCode) {
                $user->decrement('points', $usedPoints);

                if (method_exists($user, 'pointLogs')) {
                    $user->pointLogs()->create([
                        'points' => -$usedPoints,
                        'type' => 'redeem',
                        'description' => "Thanh toán đơn hàng #{$paymentCode}",
                    ]);
                }
            });
        }

        session([
            'checkout_order' => [
                'id' => $dbOrder->id,
                'db_code' => $dbOrder->code,
                'code' => $paymentCode,
                'customer' => $data,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'order_voucher_code' => $voucherBreakdown['order']['code'] ?? null,
                'order_voucher_discount' => $voucherBreakdown['order_discount'],
                'shipping_voucher_code' => $voucherBreakdown['shipping']['code'] ?? null,
                'shipping_voucher_discount' => $voucherBreakdown['shipping_discount'],
                'points_used' => $usedPoints,
                'points_discount' => $pointsDiscount,
                'total' => $total,
                'created_at' => now()->toDateTimeString(),
            ],
        ]);

        session()->forget([
            'used_points',
            'point_discount',
            'checkout_vouchers',
            'checkout_shipping_fee',
        ]);

        if ($data['payment_method'] === 'cod') {
            return redirect()->route('checkout.success');
        }

        if ($data['payment_method'] === 'zalopay') {
            return redirect()->route('zalopay.create');
        }

        if ($data['payment_method'] === 'stripe') {
            return redirect()->route('stripe.create');
        }
        if ($data['payment_method'] === 'paypal') {
        return redirect()->route('paypal.create');
        }

        Cache::put(
            'checkout_order_' . $paymentCode,
            [
                'order_id' => (string) $dbOrder->id,
                'order_code' => $dbOrder->code,
                'total' => $total,
                'status' => 'pending',
            ],
            now()->addMinutes(15)
        );

        return redirect()->route('checkout.qr');
    }

    public function qr()
    {
        $order = session('checkout_order');

        if (!$order || ($order['customer']['payment_method'] ?? null) !== 'bank') {
            return redirect()->route('checkout.index');
        }

        $items = $this->cart->items();
        $subtotal = (int) ($order['subtotal'] ?? 0);
        $shippingFee = (int) ($order['shipping_fee'] ?? 0);
        $pointsDiscount = (int) ($order['points_discount'] ?? 0);
        $total = (int) ($order['total'] ?? 0);

        $bankId = config('services.vietqr.bank_id', '970422');
        $accountNo = config('services.vietqr.account_no');
        $accountName = config('services.vietqr.account_name', 'MOMMYKIDS');

        if (!$accountNo) {
            return redirect()->route('checkout.index')
                ->with('error', 'Chưa cấu hình số tài khoản VietQR.');
        }

        $transferContent = 'MOMMYKIDS ' . $order['code'];

        $qrUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%s&addInfo=%s&accountName=%s',
            urlencode($bankId),
            urlencode($accountNo),
            urlencode((string) $total),
            urlencode($transferContent),
            urlencode($accountName)
        );

        return view('checkout.qr', compact(
            'order',
            'items',
            'subtotal',
            'shippingFee',
            'pointsDiscount',
            'total',
            'qrUrl',
            'accountNo',
            'accountName',
            'transferContent'
        ));
    }

    public function confirmTransfer(Request $request)
    {
        $order = session('checkout_order');

        if (!$order) {
            return redirect()->route('checkout.index');
        }

        $payment = [
            'status' => 'paid',
            'transaction_id' => 'FT' . strtoupper(Str::random(12)),
            'paid_at' => now()->toDateTimeString(),
            'bank' => 'MB Bank',
            'amount' => $order['total'],
            'content' => 'MOMMYKIDS ' . $order['code'],
        ];

        session(['checkout_payment' => $payment]);

        return redirect()->route('checkout.success');
    }



    public function sepayWebhook(Request $request)
    {
        $data = $request->all();

        if (($data['transferType'] ?? null) !== 'in') {
            return response()->json(['success' => true]);
        }

        $amount = (int) ($data['transferAmount'] ?? 0);

        $paymentText = strtoupper(
            trim(($data['code'] ?? '') . ' ' . ($data['content'] ?? ''))
        );

        preg_match('/MK\d{12}/', $paymentText, $matches);
        $orderCode = $matches[0] ?? null;

        if (!$orderCode) {
            return response()->json(['success' => true]);
        }

        $cacheKey = 'checkout_order_' . $orderCode;
        $order = Cache::get($cacheKey);

        if (!$order || ($order['status'] ?? null) === 'paid') {
            return response()->json(['success' => true]);
        }

        if ($amount !== (int) $order['total']) {
            return response()->json(['success' => true]);
        }

        $order['status'] = 'paid';
        $order['paid_at'] = now()->toDateTimeString();
        $order['transaction_id'] = $data['id'] ?? null;
        $order['reference_code'] = $data['referenceCode'] ?? null;
        $order['gateway'] = $data['gateway'] ?? null;

        Cache::put($cacheKey, $order, now()->addMinutes(30));

        return response()->json(['success' => true]);
    }

    public function paymentStatus(string $code)
    {
        $order = session('checkout_order');

        if (!$order || ($order['code'] ?? null) !== $code) {
            return response()->json(['paid' => false], 404);
        }

        $payment = Cache::get('checkout_order_' . $code);

        if ($payment && ($payment['status'] ?? null) === 'paid') {
            session([
                'checkout_payment' => [
                    'status' => 'paid',
                    'transaction_id' => $payment['transaction_id'] ?? null,
                    'paid_at' => $payment['paid_at'] ?? now()->toDateTimeString(),
                    'bank' => 'MB Bank',
                    'amount' => $order['total'],
                    'content' => 'MOMMYKIDS ' . $code,
                ],
            ]);

            return response()->json([
                'paid' => true,
                'redirect' => route('checkout.success'),
            ]);
        }

        return response()->json(['paid' => false]);
    }

    public function success()
{
    $order = session('checkout_order');

    if (!$order) {
        return redirect()->route('checkout.index');
    }

    $dbOrderId = $order['id'] ?? $order['order_id'] ?? null;
    $dbOrder = $dbOrderId ? Order::find($dbOrderId) : null;

    $isCompleted =
        $dbOrder &&
        (
            $dbOrder->payment_method === 'cod' ||
            $dbOrder->payment_status === 'paid'
        );

    /*
    |--------------------------------------------------------------------------
    | Chốt voucher khi đơn đã hoàn tất
    |--------------------------------------------------------------------------
    */
    if ($isCompleted) {
        $this->finalizeReservedVoucherUsages($dbOrder);
    }

    /*
    |--------------------------------------------------------------------------
    | PHẢI lấy items trước khi xóa giỏ
    |--------------------------------------------------------------------------
    | Để trang "Thanh toán thành công" vẫn hiển thị sản phẩm vừa mua.
    */
    $items = $this->cart->items();

    $subtotal = (int) ($order['subtotal'] ?? 0);
    $shippingFee = (int) ($order['shipping_fee'] ?? 0);
    $pointsDiscount = (int) ($order['points_discount'] ?? 0);
    $total = (int) ($order['total'] ?? 0);
    $payment = session('checkout_payment');

    /*
    |--------------------------------------------------------------------------
    | Xóa giỏ sau khi đặt/thanh toán thành công
    |--------------------------------------------------------------------------
    */
    if ($isCompleted) {
        $this->cart->clear();
    }

    return view('checkout.success', compact(
        'order',
        'items',
        'subtotal',
        'shippingFee',
        'pointsDiscount',
        'total',
        'payment'
    ));
}

    /**
     * Lấy các voucher người dùng đã lưu/có trong ví và còn dùng được
     * với giỏ hàng hiện tại. Danh sách này chỉ phục vụ UI chọn voucher;
     * applyVoucher() vẫn xác thực lại toàn bộ ở backend.
     */
    private function getAvailableCheckoutVouchers(
        $user,
        Collection $items,
        string $type
    ): Collection {
        if (!$user) {
            return collect();
        }

        $now = now();
        $savedIds = $user->savedVouchers()
            ->where('vouchers.type', $type)
            ->pluck('vouchers.id');

        $vouchers = Voucher::where('type', $type)
            ->where(function ($q) use ($savedIds, $now) {
                // Loại 1: Đã lưu vào tài khoản
                $q->whereIn('id', $savedIds)
                // Loại 2: Không cần lưu + công khai + đủ thời gian
                ->orWhere(function ($q2) use ($now) {
                    $q2->where('require_save_to_user', false)
                        ->where('is_public', true)
                        ->where('status', 'active')
                        ->where(function ($qTime) use ($now) {
                            $qTime->whereNull('starts_at')
                                ->orWhere('starts_at', '<=', $now);
                        })
                        ->where(function ($qTime) use ($now) {
                            $qTime->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', $now);
                        })
                        ->where(fn ($q) => $q
                            ->whereNull('total_quantity')
                            ->orWhereColumn('total_quantity', '>', 'used_count')
                        );
                });
            })
            ->orderBy('expires_at')
            ->get();

        // Lọc tiếp theo điều kiện áp dụng thực tế
        return $vouchers
            ->filter(function (Voucher $voucher) use ($user, $items) {
                try {
                    $this->voucherValidation->validate(
                        $voucher,
                        $user,
                        $items
                    );
                    return true;
                } catch (Throwable $e) {
                    return false;
                }
            })
            ->map(function (Voucher $voucher) {
                $benefit = match ($voucher->discount_type) {
                    'percent' => 'Giảm ' . (int) $voucher->discount_value . '%'
                        . ($voucher->max_discount_amount
                            ? ' · tối đa ' . number_format((int) $voucher->max_discount_amount, 0, ',', '.') . 'đ'
                            : ''),
                    'fixed' => 'Giảm ' . number_format((int) $voucher->discount_value, 0, ',', '.') . 'đ',
                    'free_shipping' => (int) $voucher->max_discount_amount > 0
                        ? 'Giảm phí ship tối đa ' . number_format((int) $voucher->max_discount_amount, 0, ',', '.') . 'đ'
                        : 'Miễn phí vận chuyển',
                    default => 'Ưu đãi',
                };
                return [
                    'id' => (string) $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                    'benefit' => $benefit,
                    'expires_at' => $voucher->expires_at?->format('d/m/Y'),
                    'is_auto' => !$voucher->require_save_to_user, // Đánh dấu loại tự động
                ];
            })
            ->values();
    }

    /**
     * Tính lại cả hai slot voucher từ session. Không tin số discount lưu ở frontend/session.
     * Khi $strict=true, voucher invalid sẽ throw để chặn tạo Order.
     * Khi $lock=true, khóa row voucher trong transaction checkout.
     */
    private function calculateVoucherBreakdown(
        Collection $items,
        int $subtotal,
        int $shippingFee,
        $user,
        bool $strict,
        bool $lock
    ): array {
        $result = [
            'order_discount' => 0,
            'shipping_discount' => 0,
            'order' => null,
            'shipping' => null,
            'details' => [],
            'messages' => [],
        ];

        $slots = session('checkout_vouchers', []);
        $changed = false;

        foreach (['order', 'shipping'] as $type) {
            $slot = $slots[$type] ?? null;

            if (!$slot || empty($slot['id'])) {
                continue;
            }

            $query = Voucher::query()
                ->whereKey($slot['id']);

            if ($lock) {
                $query->lockForUpdate();
            }

            $voucher = $query->first();

            try {
                if (!$voucher || $voucher->type !== $type) {
                    throw new \RuntimeException('Mã ưu đãi đã thay đổi hoặc không còn tồn tại.');
                }

                $isSaved = $user && $user->savedVouchers()->where('vouchers.id', $voucher->id)->exists();
                $isAutoApply = $voucher && !$voucher->require_save_to_user && $voucher->is_public;

                if (!$isSaved && !$isAutoApply) {
                    throw new \RuntimeException('Mã ưu đãi này không còn trong ví của bạn.');
                }

                $validation = $this->voucherValidation->validate(
                    $voucher,
                    $user,
                    $items
                );

                $discount = $this->discountCalculator->calculate(
                    $voucher,
                    (float) $validation['eligible_subtotal'],
                    (float) $shippingFee
                );

                $orderDiscount = (int) $discount['amount'];
                $shipDiscount  = (int) $discount['shipping_discount'];
                $totalDiscount = $orderDiscount + $shipDiscount;

                if ($totalDiscount <= 0 && $voucher->type !== 'shipping') {
                    throw new \RuntimeException('Mã này không tạo được mức giảm cho đơn hàng.');
                }

                if (
                    $voucher->total_budget !== null &&
                    ((int) $validation['total_discounted'] + $totalDiscount) > (int) $voucher->total_budget
                ) {
                    throw new \RuntimeException('Ngân sách còn lại của mã ưu đãi không đủ.');
                }

                if ($type === 'order') {
                    $result['order_discount'] = $orderDiscount;
                } else {
                    $result['shipping_discount'] = $shipDiscount;
                }

                $result[$type] = [
                    'id'   => (string) $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                ];

                $result['details'][$type] = [
                    'voucher'           => $voucher,
                    'discount'          => $totalDiscount, 
                    'order_discount'    => $orderDiscount,
                    'shipping_discount' => $shipDiscount,
                    'eligible_subtotal' => (int) $validation['eligible_subtotal'],
                ];
            } catch (Throwable $e) {
                if ($strict) {
                    throw $e;
                }

                unset($slots[$type]);
                $changed = true;
                $result['messages'][$type] = $e->getMessage();
            }
        }

        if ($changed) {
            session(['checkout_vouchers' => $slots]);
        }

        return $result;
    }

    /**
     * Chốt các voucher đã reserve của một đơn hàng.
     * Idempotent: chỉ xử lý usage đang ở trạng thái reserved, nên gọi lại không
     * làm tăng used_count lần thứ hai.
     */
    private function finalizeReservedVoucherUsages(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $usages = VoucherUsage::query()
                ->where('order_id', $order->id)
                ->where('status', 'reserved')
                ->lockForUpdate()
                ->get();

            foreach ($usages as $usage) {
                $voucher = Voucher::query()
                    ->whereKey($usage->voucher_id)
                    ->lockForUpdate()
                    ->first();

                if (!$voucher) {
                    $usage->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancel_reason' => 'Voucher không còn tồn tại khi chốt đơn.',
                    ]);

                    continue;
                }

                $usage->update([
                    'status' => 'applied',
                    'reserved_until' => null,
                    'applied_at' => now(),
                ]);

                $voucher->increment('used_count');
            }
        });
    }

    private function normalizeGhnList(array $response): array
    {
        if (isset($response['data']) && is_array($response['data'])) {
            return $response['data'];
        }

        if (isset($response[0]) && is_array($response[0])) {
            return $response;
        }

        if (!empty($response)) {
            return [$response];
        }

        return [];
    }

    private function findGhnName(
        array $items,
        string $idKey,
        string|int $wantedId,
        string $nameKey
    ): ?string {
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (
                isset($item[$idKey]) &&
                (string) $item[$idKey] === (string) $wantedId
            ) {
                return isset($item[$nameKey])
                    ? (string) $item[$nameKey]
                    : null;
            }
        }

        return null;
    }

    private function extractShippingFee(array $feeData): int
    {
        if (isset($feeData['total'])) {
            return (int) $feeData['total'];
        }

        if (isset($feeData['service_fee'])) {
            return (int) $feeData['service_fee'];
        }

        return 0;
    }
}
