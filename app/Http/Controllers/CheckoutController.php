<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\GHNService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $shippingFee = 0;
        $total = $subtotal;
      $provinceResponse = $this->ghn->getProvinces();

/*
|--------------------------------------------------------------------------
| Chuẩn hóa dữ liệu tỉnh/thành GHN
|--------------------------------------------------------------------------
*/

// Nếu GHN trả:
// ['data' => [...]]
if (
    isset($provinceResponse['data'])
    && is_array($provinceResponse['data'])
) {
    $provinceResponse = $provinceResponse['data'];
}

// Nếu GHN vô tình trả một tỉnh duy nhất:
// ['ProvinceID' => ..., 'ProvinceName' => ...]
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
    'items',
    'subtotal',
    'shippingFee',
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

        return response()->json([
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total' => $subtotal + $shippingFee,
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

        $weight = 500;

        $feeData = $this->ghn->calculateFee(
            (int) $data['to_district_id'],
            $data['to_ward_code'],
            $weight,
            (int) $subtotal
        );

        $shippingFee = $this->extractShippingFee($feeData);

        if ($shippingFee <= 0) {
            return back()->withInput()
                ->with('error', 'Không thể tính phí vận chuyển GHN. Vui lòng kiểm tra lại địa chỉ.');
        }

        $total = $subtotal + $shippingFee;
        $orderCode = 'MK' . now()->format('ymdHis');

        session([
            'checkout_order' => [
                'code' => $orderCode,
                'customer' => $data,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'created_at' => now()->toDateTimeString(),
            ],
        ]);

        if ($data['payment_method'] === 'cod') {
            return redirect()->route('checkout.success')
                ->with('cod_success', true);
        }

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
        $total = $order['total'];

        $bankId = config('services.vietqr.bank_id', '970407');
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
            'order', 'items', 'subtotal', 'shippingFee', 'total',
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
            'bank' => 'Techcombank',
            'amount' => $order['total'],
            'content' => 'MOMMYKIDS ' . $order['code'],
        ];

        session(['checkout_payment' => $payment]);

        return redirect()->route('checkout.success');
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
        $total = $order['total'];
        $payment = session('checkout_payment');

        return view('checkout.success', compact(
            'order', 'items', 'subtotal', 'shippingFee', 'total', 'payment'
        ));
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
