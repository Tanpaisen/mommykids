<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(protected CartService $cart)
    {
    }

    public function index()
    {
        $items = $this->cart->items();
        $total = $this->cart->total();

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        return view('checkout.index', compact('items', 'total'));
    }

    public function store(Request $request)
    {
        $items = $this->cart->items();
        $total = $this->cart->total();

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:cod,bank'],
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'address.required' => 'Vui lòng nhập địa chỉ nhận hàng.',
            'email.email' => 'Email không đúng định dạng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        $orderCode = 'MK' . now()->format('ymdHis');

        session([
            'checkout_order' => [
                'code' => $orderCode,
                'customer' => $data,
                'total' => $total,
                'created_at' => now()->toDateTimeString(),
            ],
        ]);

        if ($data['payment_method'] === 'cod') {
            return redirect()->route('checkout.success')->with('cod_success', true);
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
        $total = $this->cart->total();

        $bankId = config('services.vietqr.bank_id', '970407');
        $accountNo = config('services.vietqr.account_no');
        $accountName = config('services.vietqr.account_name', 'MOMMYKIDS');

        if (!$accountNo) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Chưa cấu hình số tài khoản VietQR.');
        }

        $transferContent = 'MOMMYKIDS ' . $order['code'];

        $qrUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%s&addInfo=%s&accountName=%s',
            urlencode($bankId),
            urlencode($accountNo),
            urlencode((string) $total),
            urlencode($transferContent),
            urlencode($accountName),
        );

        return view('checkout.qr', compact(
            'order',
            'items',
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
        $total = $order['total'];
        $payment = session('checkout_payment');

        return view('checkout.success', compact('order', 'items', 'total', 'payment'));
    }
}
