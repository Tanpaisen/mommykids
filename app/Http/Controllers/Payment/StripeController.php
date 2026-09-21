<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeController extends Controller
{
    public function create()
    {
        if (!config('services.stripe.enabled')) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Stripe hiện chưa được kích hoạt.');
        }

        $secret = (string) config('services.stripe.secret');

        if (!$secret) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Thiếu Stripe Secret Key.');
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

        if ($order->payment_method !== 'stripe') {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Đơn hàng này không sử dụng Stripe.');
        }

        $successUrl = (string) config('services.stripe.success_url');
        $cancelUrl = (string) config('services.stripe.cancel_url');

        if (!$successUrl || !$cancelUrl) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Thiếu URL Stripe success/cancel.');
        }

        try {
            $stripe = new StripeClient($secret);

            $checkoutSession = $stripe->checkout->sessions->create([
                'mode' => 'payment',

                'payment_method_types' => [
                    'card',
                ],

                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'vnd',

                            'unit_amount' => (int) $order->total,

                            'product_data' => [
                                'name' => 'Đơn hàng ' . $order->code,
                                'description' => 'Thanh toán đơn hàng MommyKids',
                            ],
                        ],

                        'quantity' => 1,
                    ],
                ],

                'client_reference_id' => (string) $order->id,

                'metadata' => [
                    'order_id' => (string) $order->id,
                    'order_code' => (string) $order->code,
                ],

                'payment_intent_data' => [
                    'metadata' => [
                        'order_id' => (string) $order->id,
                        'order_code' => (string) $order->code,
                    ],
                ],

                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            Log::info('Stripe Checkout Session created', [
                'order_id' => $order->id,
                'order_code' => $order->code,
                'session_id' => $checkoutSession->id,
                'amount' => $order->total,
            ]);

            session([
                'stripe_checkout_session_id' => $checkoutSession->id,
                'stripe_order_id' => (string) $order->id,
            ]);

            return redirect()->away($checkoutSession->url);
        } catch (\Throwable $e) {
            Log::error('Stripe create checkout exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Không thể kết nối tới Stripe. Vui lòng thử lại.'
                );
        }
    }

    public function success(Request $request)
    {
        $sessionId = (string) $request->query('session_id', '');

        if (!$sessionId) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Thiếu Stripe Checkout Session.');
        }

        $secret = (string) config('services.stripe.secret');

        if (!$secret) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Thiếu Stripe Secret Key.');
        }

        try {
            $stripe = new StripeClient($secret);

            $checkoutSession = $stripe
                ->checkout
                ->sessions
                ->retrieve($sessionId);

            $order = $this->syncOrderFromSession($checkoutSession);

            if (!$order) {
                return redirect()
                    ->route('checkout.index')
                    ->with(
                        'warning',
                        'Stripe chưa xác nhận giao dịch thành công.'
                    );
            }

            session([
    'checkout_order' => [
        'id' => (string) $order->id,
        'db_code' => $order->code,
        'code' => $order->code,

        'customer' => [
            'full_name' => $order->recipient_name,
            'phone' => $order->recipient_phone,
            'email' => $order->recipient_email,

            'province_name' => $order->province_name,
            'district_name' => $order->district_name,
            'ward_name' => $order->ward_name,
            'address' => $order->address_detail,

            'payment_method' => 'stripe',
        ],

        'subtotal' => (int) $order->subtotal,
        'shipping_fee' => (int) $order->shipping_fee,

        'points_used' => 0,
        'points_discount' => (int) ($order->discount ?? 0),

        'total' => (int) $order->total,

        'created_at' => $order->created_at
            ? $order->created_at->toDateTimeString()
            : now()->toDateTimeString(),
    ],

    'checkout_payment' => [
        'status' => 'paid',
        'transaction_id' => $checkoutSession->id,
        'paid_at' => now()->toDateTimeString(),
        'bank' => 'Stripe',
        'amount' => (int) $order->total,
        'content' => $order->code,
    ],
]);
            return redirect()->route('checkout.success');
        } catch (\Throwable $e) {
            Log::error('Stripe success exception', [
                'session_id' => $sessionId,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('checkout.index')
                ->with('error', 'Không thể xác minh giao dịch Stripe.');
        }
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();

        $signature = (string) $request->header(
            'Stripe-Signature',
            ''
        );

        $webhookSecret = (string) config(
            'services.stripe.webhook_secret'
        );

        if (!$webhookSecret) {
            Log::warning('Stripe webhook secret missing');

            return response()->json([
                'message' => 'Webhook secret missing',
            ], 500);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );
        } catch (UnexpectedValueException $e) {
            return response()->json([
                'message' => 'Invalid payload',
            ], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json([
                'message' => 'Invalid signature',
            ], 400);
        }

        if (
            $event->type === 'checkout.session.completed' ||
            $event->type === 'checkout.session.async_payment_succeeded'
        ) {
            $checkoutSession = $event->data->object;

            $order = $this->syncOrderFromSession(
                $checkoutSession
            );

            Log::info('Stripe webhook processed', [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'session_id' => $checkoutSession->id ?? null,
                'order_id' => $order?->id,
            ]);
        }

        return response()->json([
            'received' => true,
        ]);
    }

    private function syncOrderFromSession($checkoutSession): ?Order
    {
        if (
            ($checkoutSession->payment_status ?? null)
            !==
            'paid'
        ) {
            return null;
        }

        $orderId =
            $checkoutSession->metadata->order_id
            ?? $checkoutSession->client_reference_id
            ?? null;

        if (!$orderId) {
            return null;
        }

        $amount = (int) (
            $checkoutSession->amount_total
            ?? 0
        );

        $currency = strtolower(
            (string) (
                $checkoutSession->currency
                ?? ''
            )
        );

        return DB::transaction(
            function () use (
                $orderId,
                $amount,
                $currency,
                $checkoutSession
            ) {
                $order = Order::query()
                    ->lockForUpdate()
                    ->find($orderId);

                if (!$order) {
                    return null;
                }

                if ($order->payment_method !== 'stripe') {
                    Log::warning(
                        'Stripe payment method mismatch',
                        [
                            'order_id' => $order->id,
                            'payment_method' => $order->payment_method,
                        ]
                    );

                    return null;
                }

                if ($currency !== 'vnd') {
                    Log::warning(
                        'Stripe currency mismatch',
                        [
                            'order_id' => $order->id,
                            'currency' => $currency,
                        ]
                    );

                    return null;
                }

                if ((int) $order->total !== $amount) {
                    Log::warning(
                        'Stripe amount mismatch',
                        [
                            'order_id' => $order->id,
                            'db_amount' => $order->total,
                            'stripe_amount' => $amount,
                        ]
                    );

                    return null;
                }

                if ($order->payment_status !== 'paid') {
                    $order->payment_status = 'paid';
                    $order->save();
                }

                Log::info('Stripe payment completed', [
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'session_id' => $checkoutSession->id ?? null,
                    'amount' => $amount,
                    'currency' => $currency,
                ]);

                return $order;
            }
        );
    }
}