<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\GHNService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected GHNService $ghn
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
        
        // Lấy thông tin điểm sử dụng từ session
        $usedPoints = session('used_points', 0);
        $pointsDiscount = session('point_discount', 0);
        
        $total = max(0, $subtotal + $shippingFee - $pointsDiscount);

        $provinceResponse = $this->ghn->getProvinces();

        /*
        |--------------------------------------------------------------------------
        | Chuẩn hóa dữ liệu tỉnh/thành GHN
        |--------------------------------------------------------------------------
        */

        // Nếu GHN trả: ['data' => [...]]
        if (
            isset($provinceResponse['data'])
            && is_array($provinceResponse['data'])
        ) {
            $provinceResponse = $provinceResponse['data'];
        }

        // Nếu GHN vô tình trả một tỉnh duy nhất: ['ProvinceID' => ..., 'ProvinceName' => ...]
        if (
            isset($provinceResponse['ProvinceID'])
            && isset($provinceResponse['ProvinceName'])
        ) {
            $provinceResponse = [$provinceResponse];
        }

        // Chỉ giữ item hợp lệ
        $provinces = collect($provinceResponse)
            ->filter(function ($province) {
                return is_array($province)
                    && isset($province['ProvinceID'])
                    && isset($province['ProvinceName']);
            })
            ->values()
            ->all();

        return view('checkout.index', compact(
            'user',
            'items',
            'subtotal',
            'shippingFee',
            'usedPoints',
            'pointsDiscount',
            'total',
            'provinces'
        ));
    }

    public function districts(Request $request)
    {
        $data = $request->validate([
            'province_id' => ['required', 'integer'],
        ]);

        return response()->json(
            $this->ghn->getDistricts((int) $data['province_id'])
        );
    }

    public function wards(Request $request)
    {
        $data = $request->validate([
            'district_id' => ['required', 'integer'],
        ]);

        return response()->json(
            $this->ghn->getWards((int) $data['district_id'])
        );
    }

    public function calculateShippingFee(Request $request)
    {
        $data = $request->validate([
            'district_id' => ['required', 'integer'],
            'ward_code' => ['required', 'string'],
        ]);

        $subtotal = $this->cart->total();
        $weight = 500;

        $feeData = $this->ghn->calculateFee(
            (int) $data['district_id'],
            $data['ward_code'],
            $weight,
            (int) $subtotal
        );

        $shippingFee = $this->extractShippingFee($feeData);

        if ($shippingFee <= 0) {
            return response()->json([
                'message' => 'Không tính được phí vận chuyển GHN.',
                'ghn' => $feeData,
            ], 422);
        }

        $pointsDiscount = session('point_discount', 0);
        $finalTotal = max(0, $subtotal + $shippingFee - $pointsDiscount);

        return response()->json([
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'points_discount' => $pointsDiscount,
            'total' => $finalTotal,
        ]);
    }

    /**
     * Áp dụng điểm tích lũy vào đơn hàng (AJAX)
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
            return response()->json(['message' => 'Vui lòng đăng nhập để dùng điểm.'], 401);
        }

        $requestedPoints = (int) $request->input('points');

        if ($requestedPoints > $user->points) {
            return response()->json([
                'message' => 'Bạn chỉ có tối đa ' . number_format($user->points) . ' điểm.',
            ], 422);
        }

        $subtotal = $this->cart->total();
        $pointRate = 1000; // 1 điểm = 1.000 VNĐ
        $calculatedDiscount = $requestedPoints * $pointRate;

        // Giới hạn giảm giá không vượt quá tổng tạm tính giỏ hàng
        if ($calculatedDiscount > $subtotal) {
            $requestedPoints = (int) ceil($subtotal / $pointRate);
            $calculatedDiscount = $subtotal;
        }

        session([
            'used_points' => $requestedPoints,
            'point_discount' => $calculatedDiscount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã áp dụng điểm tích lũy thành công!',
            'used_points' => $requestedPoints,
            'points_discount' => $calculatedDiscount,
            'discount_fmt' => number_format($calculatedDiscount) . 'đ',
        ]);
    }

    /**
     * Hủy sử dụng điểm tích lũy (AJAX)
     */
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
        $subtotal = $this->cart->total();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $usedPoints = session('used_points', 0);
        $pointsDiscount = session('point_discount', 0);

        if ($user && $usedPoints > $user->points) {
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
            'payment_method' => ['required', 'in:cod,bank'],
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

        /*
        |--------------------------------------------------------------------------
        | Tính phí vận chuyển
        |--------------------------------------------------------------------------
        */

        $weight = 500;

        $feeData = $this->ghn->calculateFee(
            (int) $data['to_district_id'],
            $data['to_ward_code'],
            $weight,
            (int) $subtotal
        );

        $shippingFee = $this->extractShippingFee($feeData);

        if ($shippingFee <= 0) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Không thể tính phí vận chuyển GHN. Vui lòng kiểm tra lại địa chỉ.'
                );
        }

        // Tính lại tổng tiền sau khi đã có phí ship và giảm giá điểm tích lũy
        $total = max(0, $subtotal + $shippingFee - $pointsDiscount);

        /*
        |--------------------------------------------------------------------------
        | Lấy tên tỉnh / huyện / xã từ GHN
        |--------------------------------------------------------------------------
        */

        $provinces = $this->normalizeGhnList(
            $this->ghn->getProvinces()
        );

        $districts = $this->normalizeGhnList(
            $this->ghn->getDistricts(
                (int) $data['province_id']
            )
        );

        $wards = $this->normalizeGhnList(
            $this->ghn->getWards(
                (int) $data['to_district_id']
            )
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
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Không thể xác định đầy đủ địa chỉ giao hàng.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Tạo Order + Order Items trong database
        |--------------------------------------------------------------------------
        */

        $dbOrder = DB::transaction(function () use (
            $data,
            $items,
            $subtotal,
            $shippingFee,
            $total,
            $provinceName,
            $districtName,
            $wardName
        ) {
            $order = Order::create([
                'user_id' => Auth::id(),

                'recipient_name' => $data['full_name'],
                'recipient_phone' => $data['phone'],
                'recipient_email' => $data['email'] ?? null,

                'province_name' => $provinceName,
                'district_name' => $districtName,
                'ward_name' => $wardName,
                'address_detail' => $data['address'],

                'ghn_province_id' => (int) $data['province_id'],
                'ghn_district_id' => (int) $data['to_district_id'],
                'ghn_ward_code' => $data['to_ward_code'],

                'subtotal' => (int) $subtotal,
                'shipping_fee' => (int) $shippingFee,
                'discount' => 0, // Bạn có thể update trường discount này nếu có cột lưu point discount
                'total' => (int) $total,

                'status' => 'pending',

                // Form dùng "bank", DB dùng "qr"
                'payment_method' => match ($data['payment_method']) {
                    'bank' => 'qr',
                    'momo' => 'momo',
                    default => 'cod',
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

            return $order;
        });

        /*
        |--------------------------------------------------------------------------
        | Mã thanh toán dùng cho QR / SePay
        |--------------------------------------------------------------------------
        */

        $paymentCode = 'MK' . now()->format('ymdHis');

        /*
        |--------------------------------------------------------------------------
        | Trừ điểm và ghi log điểm tích lũy nếu có sử dụng điểm
        |--------------------------------------------------------------------------
        */
        if ($user && $usedPoints > 0) {
            DB::transaction(function () use ($user, $usedPoints, $paymentCode) {
                /** @var \App\Models\User $user */
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

        /*
        |--------------------------------------------------------------------------
        | Session dùng cho các trang checkout hiện tại
        |--------------------------------------------------------------------------
        */

        session([
            'checkout_order' => [
                'id' => $dbOrder->id,
                'db_code' => $dbOrder->code,
                // Giữ code MK để QR + paymentStatus hiện tại tiếp tục hoạt động
                'code' => $paymentCode,
                'customer' => $data,
                'subtotal' => (int) $subtotal,
                'shipping_fee' => (int) $shippingFee,
                'points_used' => $usedPoints,
                'points_discount' => $pointsDiscount,
                'total' => (int) $total,
                'created_at' => now()->toDateTimeString(),
            ],
        ]);

        // Xóa session giảm giá điểm sau khi ghi nhận đơn
        session()->forget(['used_points', 'point_discount']);

        /*
        |--------------------------------------------------------------------------
        | COD
        |--------------------------------------------------------------------------
        */

        if ($data['payment_method'] === 'cod') {
            return redirect()
                ->route('checkout.success')
                ->with('cod_success', true);
        }

        /*
        |--------------------------------------------------------------------------
        | Chuyển khoản ngân hàng / SePay
        |--------------------------------------------------------------------------
        */

        Cache::put(
            'checkout_order_' . $paymentCode,
            [
                'code' => $paymentCode,
                // Quan trọng: nối mã MK với order thật trong DB
                'order_id' => $dbOrder->id,
                'order_code' => $dbOrder->code,
                'total' => (int) $total,
                'status' => 'pending',
            ],
            now()->addMinutes(20)
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
        $subtotal = $order['subtotal'];
        $shippingFee = $order['shipping_fee'];
        $pointsDiscount = $order['points_discount'] ?? 0;
        $total = $order['total'];

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
            'order', 'items', 'subtotal', 'shippingFee', 'pointsDiscount', 'total',
            'qrUrl', 'accountNo', 'accountName', 'transferContent'
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

        /*
        |--------------------------------------------------------------------------
        | Chỉ nhận giao dịch tiền vào
        |--------------------------------------------------------------------------
        */
        if (($data['transferType'] ?? null) !== 'in') {
            return response()->json([
                'success' => true,
            ]);
        }

        $amount = (int) ($data['transferAmount'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | SePay có thể trả mã trong "code" hoặc nằm trong "content"
        |--------------------------------------------------------------------------
        */
        $paymentText = strtoupper(
            trim(
                ($data['code'] ?? '')
                . ' '
                . ($data['content'] ?? '')
            )
        );

        /*
        |--------------------------------------------------------------------------
        | QR của MommyKids dùng:
        |
        | MOMMYKIDS MK260909123456
        |--------------------------------------------------------------------------
        */
        preg_match(
            '/MK\d{12}/',
            $paymentText,
            $matches
        );

        $orderCode = $matches[0] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Không nhận diện được đơn → vẫn trả 200 cho SePay
        |--------------------------------------------------------------------------
        */
        if (!$orderCode) {
            return response()->json([
                'success' => true,
            ]);
        }

        $cacheKey = 'checkout_order_' . $orderCode;

        $order = Cache::get($cacheKey);

        /*
        |--------------------------------------------------------------------------
        | Không còn đơn pending
        |--------------------------------------------------------------------------
        */
        if (!$order) {
            return response()->json([
                'success' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Đã xử lý webhook này trước đó
        |--------------------------------------------------------------------------
        */
        if (($order['status'] ?? null) === 'paid') {
            return response()->json([
                'success' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra đúng số tiền
        |--------------------------------------------------------------------------
        */
        if ($amount !== (int) $order['total']) {
            return response()->json([
                'success' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Thanh toán thành công
        |--------------------------------------------------------------------------
        */
        $order['status'] = 'paid';

        $order['paid_at'] =
            now()->toDateTimeString();

        $order['transaction_id'] =
            $data['id'] ?? null;

        $order['reference_code'] =
            $data['referenceCode'] ?? null;

        $order['gateway'] =
            $data['gateway'] ?? null;

        Cache::put(
            $cacheKey,
            $order,
            now()->addMinutes(30)
        );

        return response()->json([
            'success' => true,
        ]);
    }

    public function paymentStatus(string $code)
    {
        $order = session('checkout_order');

        if (
            !$order ||
            ($order['code'] ?? null) !== $code
        ) {
            return response()->json([
                'paid' => false,
            ], 404);
        }

        $payment = Cache::get(
            'checkout_order_' . $code
        );

        if (
            $payment &&
            ($payment['status'] ?? null) === 'paid'
        ) {
            /*
            |--------------------------------------------------------------------------
            | Lưu thông tin thanh toán vào session của khách
            |--------------------------------------------------------------------------
            */

            session([
                'checkout_payment' => [
                    'status' => 'paid',

                    'transaction_id' =>
                        $payment['transaction_id'] ?? null,

                    'paid_at' =>
                        $payment['paid_at']
                        ?? now()->toDateTimeString(),

                    'bank' => 'MB Bank',

                    'amount' =>
                        $order['total'],

                    'content' =>
                        'MOMMYKIDS ' . $code,
                ],
            ]);

            return response()->json([
                'paid' => true,

                'redirect' =>
                    route('checkout.success'),
            ]);
        }

        return response()->json([
            'paid' => false,
        ]);
    }

    public function success()
    {
        $order = session('checkout_order');

        if (!$order) {
            return redirect()->route('checkout.index');
        }

        $items = $this->cart->items();
        $subtotal = $order['subtotal'];
        $shippingFee = $order['shipping_fee'];
        $pointsDiscount = $order['points_discount'] ?? 0;
        $total = $order['total'];
        $payment = session('checkout_payment');

        return view('checkout.success', compact(
            'order', 'items', 'subtotal', 'shippingFee', 'pointsDiscount', 'total', 'payment'
        ));
    }
    
    private function normalizeGhnList(array $response): array
    {
        if (
            isset($response['data'])
            && is_array($response['data'])
        ) {
            return $response['data'];
        }

        if (
            isset($response[0])
            && is_array($response[0])
        ) {
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
                isset($item[$idKey])
                && (string) $item[$idKey] === (string) $wantedId
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