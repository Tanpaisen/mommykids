<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PayPalController extends Controller
{
    /**
     * Tạo PayPal Order và chuyển người dùng sang PayPal Sandbox.
     */
    public function create()
    {
        if (!config('services.paypal.enabled')) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'PayPal hiện chưa được kích hoạt.');
        }

        $checkoutOrder = session('checkout_order');

        $orderId = $checkoutOrder['id']
            ?? $checkoutOrder['order_id']
            ?? null;

        if (!$orderId) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Không tìm thấy đơn hàng cần thanh toán.');
        }

        $order = Order::find($orderId);

        if (!$order) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Đơn hàng không tồn tại.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success');
        }

        if ($order->payment_method !== 'paypal') {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Đơn hàng này không sử dụng PayPal.');
        }

        try {
            $accessToken = $this->getAccessToken();

            $baseUrl = rtrim(
                (string) config('services.paypal.base_url'),
                '/'
            );

            $currency = (string) config(
                'services.paypal.currency',
                'USD'
            );

            $paypalAmount = $this->convertVndToPaypalAmount(
                (int) $order->total
            );

            $response = Http::timeout(30)
                ->withToken($accessToken)
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'PayPal-Request-Id' =>
                        'mommykids-' . $order->id . '-' . Str::uuid(),
                ])
                ->post(
                    $baseUrl . '/v2/checkout/orders',
                    [
                        'intent' => 'CAPTURE',

                        'purchase_units' => [
                            [
                                'reference_id' => (string) $order->id,

                                'custom_id' => (string) $order->code,

                                'description' =>
                                    'Thanh toán đơn hàng MommyKids '
                                    . $order->code,

                                'amount' => [
                                    'currency_code' => $currency,
                                    'value' => $paypalAmount,
                                ],
                            ],
                        ],

                        'payment_source' => [
                            'paypal' => [
                                'experience_context' => [
                                    'payment_method_preference' =>
                                        'IMMEDIATE_PAYMENT_REQUIRED',

                                    'landing_page' => 'LOGIN',

                                    'shipping_preference' =>
                                        'NO_SHIPPING',

                                    'user_action' => 'PAY_NOW',

                                    'return_url' =>
                                        route('paypal.capture'),

                                    'cancel_url' =>
                                        route('paypal.cancel'),
                                ],
                            ],
                        ],
                    ]
                );

            $result = $response->json();

            if (
                !$response->successful()
                || empty($result['id'])
            ) {
                Log::warning('PayPal create order failed', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'response' => $result,
                ]);

                return redirect()
                    ->route('checkout.index')
                    ->with(
                        'error',
                        'Không tạo được giao dịch PayPal.'
                    );
            }

            $approvalUrl = collect($result['links'] ?? [])
                ->first(function ($link) {
                    return in_array(
                        $link['rel'] ?? '',
                        ['payer-action', 'approve'],
                        true
                    );
                })['href'] ?? null;

            if (!$approvalUrl) {
                Log::warning('PayPal missing approval URL', [
                    'order_id' => $order->id,
                    'response' => $result,
                ]);

                return redirect()
                    ->route('checkout.index')
                    ->with(
                        'error',
                        'PayPal không trả về đường dẫn thanh toán.'
                    );
            }

            session([
                'paypal_order_id' => $result['id'],
                'paypal_db_order_id' => (string) $order->id,
                'paypal_amount' => $paypalAmount,
                'paypal_currency' => $currency,
            ]);

            return redirect()->away($approvalUrl);
        } catch (Throwable $e) {
            Log::error('PayPal create exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Không thể kết nối PayPal. Vui lòng thử lại.'
                );
        }
    }

    /**
     * PayPal redirect về đây sau khi người mua Approve.
     * Server sẽ Capture giao dịch.
     */
    public function capture(Request $request)
    {
        $paypalOrderId = (string) $request->query('token', '');

        $sessionPayPalOrderId = (string) session(
            'paypal_order_id',
            ''
        );

        if (
            $paypalOrderId === ''
            || $sessionPayPalOrderId === ''
            || !hash_equals(
                $sessionPayPalOrderId,
                $paypalOrderId
            )
        ) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Mã giao dịch PayPal không hợp lệ.'
                );
        }

        $dbOrderId = session('paypal_db_order_id');

        $order = $dbOrderId
            ? Order::find($dbOrderId)
            : null;

        if (!$order) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Không tìm thấy đơn hàng PayPal.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success');
        }

        try {
            $accessToken = $this->getAccessToken();

            $baseUrl = rtrim(
                (string) config('services.paypal.base_url'),
                '/'
            );

            /*
             * PayPal yêu cầu capture bằng POST:
             * /v2/checkout/orders/{id}/capture
             */
            $response = Http::timeout(30)
                ->withToken($accessToken)
                ->acceptJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->send(
                    'POST',
                    $baseUrl
                        . '/v2/checkout/orders/'
                        . urlencode($paypalOrderId)
                        . '/capture',
                    [
                        'body' => '{}',
                    ]
                );

            $result = $response->json();

            if (
                !$response->successful()
                || ($result['status'] ?? null) !== 'COMPLETED'
            ) {
                Log::warning('PayPal capture failed', [
                    'order_id' => $order->id,
                    'paypal_order_id' => $paypalOrderId,
                    'status' => $response->status(),
                    'response' => $result,
                ]);

                return redirect()
                    ->route('checkout.index')
                    ->with(
                        'error',
                        'PayPal chưa xác nhận giao dịch thành công.'
                    );
            }

            $capture = data_get(
                $result,
                'purchase_units.0.payments.captures.0'
            );

            $paidValue = (string) data_get(
                $capture,
                'amount.value',
                ''
            );

            $paidCurrency = (string) data_get(
                $capture,
                'amount.currency_code',
                ''
            );

            $expectedValue = (string) session(
                'paypal_amount',
                ''
            );

            $expectedCurrency = (string) session(
                'paypal_currency',
                ''
            );

            if (
                $paidValue === ''
                || $paidCurrency === ''
                || $paidValue !== $expectedValue
                || $paidCurrency !== $expectedCurrency
            ) {
                Log::warning('PayPal amount mismatch', [
                    'order_id' => $order->id,
                    'expected_value' => $expectedValue,
                    'paid_value' => $paidValue,
                    'expected_currency' => $expectedCurrency,
                    'paid_currency' => $paidCurrency,
                ]);

                return redirect()
                    ->route('checkout.index')
                    ->with(
                        'error',
                        'Số tiền PayPal không khớp với đơn hàng.'
                    );
            }

            DB::transaction(function () use ($order) {
                $lockedOrder = Order::query()
                    ->lockForUpdate()
                    ->find($order->id);

                if (
                    !$lockedOrder
                    || $lockedOrder->payment_method !== 'paypal'
                ) {
                    throw new \RuntimeException(
                        'Đơn hàng PayPal không hợp lệ.'
                    );
                }

                if ($lockedOrder->payment_status !== 'paid') {
                    $lockedOrder->payment_status = 'paid';
                    $lockedOrder->save();
                }
            });

            $captureId = (string) (
                $capture['id']
                ?? $paypalOrderId
            );

            session([
                'checkout_payment' => [
                    'status' => 'paid',
                    'transaction_id' => $captureId,
                    'paid_at' => now()->toDateTimeString(),
                    'bank' => 'PayPal',
                    'amount' => (int) $order->total,
                    'content' => $order->code,
                ],
            ]);

            session()->forget([
                'paypal_order_id',
                'paypal_db_order_id',
                'paypal_amount',
                'paypal_currency',
            ]);

            return redirect()->route('checkout.success');
        } catch (Throwable $e) {
            Log::error('PayPal capture exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Không thể xác nhận thanh toán PayPal.'
                );
        }
    }

    /**
     * Người dùng hủy trên PayPal.
     */
    public function cancel()
    {
        session()->forget([
            'paypal_order_id',
            'paypal_db_order_id',
            'paypal_amount',
            'paypal_currency',
        ]);

        return redirect()
            ->route('checkout.index')
            ->with(
                'warning',
                'Bạn đã hủy thanh toán PayPal.'
            );
    }

    /**
     * Lấy OAuth 2.0 access token.
     */
    private function getAccessToken(): string
    {
        $clientId = (string) config(
            'services.paypal.client_id'
        );

        $clientSecret = (string) config(
            'services.paypal.client_secret'
        );

        $baseUrl = rtrim(
            (string) config('services.paypal.base_url'),
            '/'
        );

        if (
            $clientId === ''
            || $clientSecret === ''
            || $baseUrl === ''
        ) {
            throw new \RuntimeException(
                'Cấu hình PayPal chưa đầy đủ.'
            );
        }

        $response = Http::timeout(30)
            ->withBasicAuth(
                $clientId,
                $clientSecret
            )
            ->asForm()
            ->post(
                $baseUrl . '/v1/oauth2/token',
                [
                    'grant_type' => 'client_credentials',
                ]
            );

        if (
            !$response->successful()
            || !$response->json('access_token')
        ) {
            throw new \RuntimeException(
                'Không lấy được PayPal access token.'
            );
        }

        return (string) $response->json('access_token');
    }

    /**
     * Sandbox:
     * Quy đổi VND của MommyKids sang USD vì PayPal không hỗ trợ VND.
     */
    private function convertVndToPaypalAmount(int $vnd): string
    {
        $rate = (float) config(
            'services.paypal.vnd_per_usd',
            25000
        );

        if ($rate <= 0) {
            throw new \RuntimeException(
                'Tỷ giá PayPal không hợp lệ.'
            );
        }

        $usd = $vnd / $rate;

        /*
         * PayPal không nhận amount = 0.
         */
        $usd = max(0.01, $usd);

        return number_format(
            $usd,
            2,
            '.',
            ''
        );
    }
}