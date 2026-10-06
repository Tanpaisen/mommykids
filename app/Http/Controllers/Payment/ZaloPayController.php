<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZaloPayController extends Controller
{
    /**
     * Tạo giao dịch ZaloPay.
     */
    public function create()
    {
        if (!config('services.zalopay.enabled')) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'ZaloPay hiện chưa được kích hoạt.');
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

        if ($order->payment_method !== 'zalopay') {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Đơn hàng này không sử dụng ZaloPay.');
        }

        /*
        |--------------------------------------------------------------------------
        | Cấu hình
        |--------------------------------------------------------------------------
        */

        $appId = (int) config('services.zalopay.app_id');
        $key1 = (string) config('services.zalopay.key1');

        $endpoint = (string) config(
            'services.zalopay.create_endpoint',
            'https://sb-openapi.zalopay.vn/v2/create'
        );

        $callbackUrl = (string) config(
            'services.zalopay.callback_url'
        );

        $redirectUrl = (string) config(
            'services.zalopay.redirect_url'
        );

        if (
            !$appId ||
            !$key1 ||
            !$endpoint ||
            !$callbackUrl ||
            !$redirectUrl
        ) {
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Cấu hình ZaloPay chưa đầy đủ.');
        }

        /*
        |--------------------------------------------------------------------------
        | app_trans_id
        |--------------------------------------------------------------------------
        |
        | yymmdd_ORDER_ULID_RANDOM6
        |
        */

        $appTransId =
            now('Asia/Ho_Chi_Minh')->format('ymd')
            . '_'
            . (string) $order->id
            . '_'
            . strtoupper(Str::random(6));

        $appTransId = substr($appTransId, 0, 40);

        $appTime = (int) floor(
            microtime(true) * 1000
        );

        $appUser = Auth::check()
            ? (string) Auth::id()
            : 'mommykids_guest';

        $amount = (int) $order->total;

        $item = '[]';

        /*
        |--------------------------------------------------------------------------
        | embed_data
        |--------------------------------------------------------------------------
        |
        | domestic_card + account:
        | phục vụ luồng test thanh toán nội địa.
        |
        */

        $embedData = json_encode([
            'redirecturl' => $redirectUrl,

            'preferred_payment_method' => [
    'zalopay_wallet',
],

            'order_id' => (string) $order->id,

        ], JSON_UNESCAPED_SLASHES);

        if ($embedData === false) {
            Log::error('ZaloPay embed_data encode failed', [
                'order_id' => $order->id,
            ]);

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Không thể tạo dữ liệu thanh toán ZaloPay.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | MAC Create Order
        |--------------------------------------------------------------------------
        */

        $macData = implode('|', [
            $appId,
            $appTransId,
            $appUser,
            $amount,
            $appTime,
            $embedData,
            $item,
        ]);

        $mac = hash_hmac(
            'sha256',
            $macData,
            $key1
        );

        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post($endpoint, [
                    'app_id' => $appId,

                    'app_user' => $appUser,

                    'app_trans_id' => $appTransId,

                    'app_time' => $appTime,

                    'amount' => $amount,

                    'description' =>
                        'Thanh toan don hang ' . $order->code,

                    'bank_code' => '',

                    'item' => $item,

                    'embed_data' => $embedData,

                    'callback_url' => $callbackUrl,

                    'mac' => $mac,

                    // 15 phút để test.
                    'expire_duration_seconds' => 900,
                ]);

            $result = $response->json();

            if (!is_array($result)) {
                $result = [];
            }

            /*
            |--------------------------------------------------------------------------
            | Log kết quả create
            |--------------------------------------------------------------------------
            */

            Log::info('ZaloPay create order', [
                'order_id' => $order->id,
                'order_code' => $order->code,
                'app_trans_id' => $appTransId,
                'http_status' => $response->status(),
                'response' => $result,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Kiểm tra create thành công
            |--------------------------------------------------------------------------
            */

            if (
                !$response->successful() ||
                (int) ($result['return_code'] ?? 0) !== 1
            ) {
                Log::warning('ZaloPay create failed', [
                    'order_id' => $order->id,
                    'app_trans_id' => $appTransId,
                    'response' => $result,
                ]);

                return redirect()
                    ->route('checkout.index')
                    ->with(
                        'error',
                        $result['sub_return_message']
                            ?? $result['return_message']
                            ?? 'Không tạo được giao dịch ZaloPay.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Lưu session giao dịch
            |--------------------------------------------------------------------------
            */

            session([
                'zalopay_order_id' =>
                    (string) $order->id,

                'zalopay_app_trans_id' =>
                    $appTransId,

                'zalopay_amount' =>
                    (int) $order->total,
            ]);

            /*
            |--------------------------------------------------------------------------
            | ZaloPay có thể trả:
            |
            | 1. URL gateway thật
            | 2. QR payload
            |--------------------------------------------------------------------------
            */

            $orderUrl = trim(
                (string) ($result['order_url'] ?? '')
            );

            $qrCode = trim(
                (string) ($result['qr_code'] ?? '')
            );

            /*
            |--------------------------------------------------------------------------
            | Nếu là URL thật → redirect tới ZaloPay
            |--------------------------------------------------------------------------
            */

            if (
                $orderUrl !== '' &&
                filter_var(
                    $orderUrl,
                    FILTER_VALIDATE_URL
                )
            ) {
                Log::info(
                    'ZaloPay redirect to gateway',
                    [
                        'order_id' => $order->id,
                        'app_trans_id' => $appTransId,
                    ]
                );

                return redirect()->away($orderUrl);
            }

            /*
            |--------------------------------------------------------------------------
            | Nếu là QR payload → hiển thị QR
            |--------------------------------------------------------------------------
            */

            if ($qrCode !== '') {
                session([
                    'zalopay_qr_code' => $qrCode,
                ]);

                return redirect()
                    ->route('zalopay.qr');
            }

            /*
            |--------------------------------------------------------------------------
            | Một số sandbox có thể đặt QR payload trong order_url
            |--------------------------------------------------------------------------
            */

            if (
                $orderUrl !== '' &&
                !filter_var(
                    $orderUrl,
                    FILTER_VALIDATE_URL
                )
            ) {
                session([
                    'zalopay_qr_code' => $orderUrl,
                ]);

                return redirect()
                    ->route('zalopay.qr');
            }

            Log::error(
                'ZaloPay missing payment target',
                [
                    'order_id' => $order->id,
                    'app_trans_id' => $appTransId,
                    'response' => $result,
                ]
            );

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'ZaloPay không trả về đường dẫn hoặc mã QR thanh toán.'
                );

        } catch (\Throwable $e) {
            Log::error(
                'ZaloPay create exception',
                [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]
            );

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Không thể kết nối tới ZaloPay. Vui lòng thử lại.'
                );
        }
    }

    /**
     * Hiển thị QR ZaloPay.
     */
    public function qr()
    {
        $qrCode = session('zalopay_qr_code');

        $orderId = session('zalopay_order_id');

        if (!$qrCode || !$orderId) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Không tìm thấy giao dịch ZaloPay.'
                );
        }

        $order = Order::find($orderId);

        if (!$order) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Không tìm thấy đơn hàng.'
                );
        }

        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('checkout.success');
        }

        return view(
            'checkout.zalopay-qr',
            compact(
                'qrCode',
                'order'
            )
        );
    }

    /**
     * AJAX kiểm tra trạng thái thanh toán.
     *
     * Nếu callback chưa tới thì query trực tiếp ZaloPay.
     */
    public function status()
    {
        $orderId = session(
            'zalopay_order_id'
        );

        $appTransId = session(
            'zalopay_app_trans_id'
        );

        if (!$orderId) {
            return response()->json([
                'paid' => false,
                'processing' => false,
                'message' =>
                    'Không tìm thấy giao dịch ZaloPay.',
            ], 404);
        }

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json([
                'paid' => false,
                'processing' => false,
                'message' =>
                    'Không tìm thấy đơn hàng.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Nếu DB chưa paid thì chủ động query ZaloPay
        |--------------------------------------------------------------------------
        */

        if (
            $order->payment_status !== 'paid' &&
            is_string($appTransId) &&
            $appTransId !== ''
        ) {
            $this->queryAndSyncOrder(
                $order,
                $appTransId
            );

            $order->refresh();
        }

        $paid =
            $order->payment_status === 'paid';

        if ($paid) {
            session([
                'checkout_order' => array_merge(
                    session(
                        'checkout_order',
                        []
                    ),
                    [
                        'id' =>
                            (string) $order->id,

                        'code' =>
                            $order->code,

                        'total' =>
                            $order->total,
                    ]
                ),

                'checkout_payment' => [
                    'status' => 'paid',

                    'transaction_id' =>
                        $appTransId,

                    'paid_at' =>
                        now()->toDateTimeString(),

                    'bank' =>
                        'ZaloPay',

                    'amount' =>
                        $order->total,

                    'content' =>
                        $order->code,
                ],
            ]);
        }

        return response()->json([
            'paid' => $paid,

            'processing' => !$paid,

            'redirect' => $paid
                ? route(
                    'checkout.success',
                    [],
                    false
                )
                : null,
        ]);
    }

    /**
     * Callback Server-to-Server từ ZaloPay.
     *
     * Đây là nguồn xác nhận thanh toán chính.
     */
    public function callback(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Ghi log callback tới
        |--------------------------------------------------------------------------
        */

        Log::info('ZaloPay callback received', [
            'type' => $request->input('type'),
            'has_data' =>
                $request->filled('data'),
            'has_mac' =>
                $request->filled('mac'),
        ]);

        $dataString = (string) $request->input(
            'data',
            ''
        );

        $receivedMac = (string) $request->input(
            'mac',
            ''
        );

        $type = (int) $request->input(
            'type',
            1
        );

        $key2 = (string) config(
            'services.zalopay.key2'
        );

        if (
            !$dataString ||
            !$receivedMac ||
            !$key2 ||
            $type !== 1
        ) {
            Log::warning(
                'ZaloPay callback invalid data'
            );

            return response()->json([
                'return_code' => 2,
                'return_message' =>
                    'Invalid callback data',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify MAC bằng KEY2
        |--------------------------------------------------------------------------
        */

        $expectedMac = hash_hmac(
            'sha256',
            $dataString,
            $key2
        );

        if (
            !hash_equals(
                strtolower($expectedMac),
                strtolower($receivedMac)
            )
        ) {
            Log::warning(
                'ZaloPay callback invalid MAC'
            );

            return response()->json([
                'return_code' => 2,
                'return_message' =>
                    'Invalid MAC',
            ]);
        }

        $data = json_decode(
            $dataString,
            true
        );

        if (!is_array($data)) {
            return response()->json([
                'return_code' => 2,
                'return_message' =>
                    'Invalid JSON',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra app_id
        |--------------------------------------------------------------------------
        */

        if (
            (int) ($data['app_id'] ?? 0)
            !==
            (int) config(
                'services.zalopay.app_id'
            )
        ) {
            return response()->json([
                'return_code' => 2,
                'return_message' =>
                    'Invalid app_id',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Lấy order_id từ embed_data
        |--------------------------------------------------------------------------
        */

        $embedData = json_decode(
            $data['embed_data'] ?? '{}',
            true
        );

        if (!is_array($embedData)) {
            $embedData = [];
        }

        $orderId =
            $embedData['order_id'] ?? null;

        if (!$orderId) {
            return response()->json([
                'return_code' => 2,
                'return_message' =>
                    'Missing order_id',
            ]);
        }

        $amount = (int) (
            $data['amount'] ?? 0
        );

        try {
            $success = DB::transaction(
                function () use (
                    $orderId,
                    $amount,
                    $data
                ) {
                    $order = Order::query()
                        ->lockForUpdate()
                        ->find($orderId);

                    if (!$order) {
                        Log::warning(
                            'ZaloPay callback order not found',
                            [
                                'order_id' =>
                                    $orderId,
                            ]
                        );

                        return false;
                    }

                    if (
                        $order->payment_method
                        !== 'zalopay'
                    ) {
                        Log::warning(
                            'ZaloPay callback wrong payment method',
                            [
                                'order_id' =>
                                    $order->id,

                                'payment_method' =>
                                    $order->payment_method,
                            ]
                        );

                        return false;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Kiểm tra số tiền
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int) $order->total
                        !==
                        $amount
                    ) {
                        Log::warning(
                            'ZaloPay amount mismatch',
                            [
                                'order_id' =>
                                    $order->id,

                                'db_amount' =>
                                    $order->total,

                                'callback_amount' =>
                                    $amount,
                            ]
                        );

                        return false;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Idempotent:
                    | callback nhiều lần cũng chỉ paid một lần
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $order->payment_status
                        !== 'paid'
                    ) {
                        $order->payment_status =
                            'paid';

                        $order->save();
                    }

                    Log::info(
                        'ZaloPay payment completed',
                        [
                            'order_id' =>
                                $order->id,

                            'order_code' =>
                                $order->code,

                            'app_trans_id' =>
                                $data[
                                    'app_trans_id'
                                ] ?? null,

                            'zp_trans_id' =>
                                $data[
                                    'zp_trans_id'
                                ] ?? null,

                            'amount' =>
                                $amount,
                        ]
                    );

                    return true;
                }
            );

            if (!$success) {
                return response()->json([
                    'return_code' => 2,

                    'return_message' =>
                        'Order validation failed',
                ]);
            }

            return response()->json([
                'return_code' => 1,
                'return_message' =>
                    'Success',
            ]);

        } catch (\Throwable $e) {
            Log::error(
                'ZaloPay callback exception',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );

            return response()->json([
                'return_code' => 2,
                'return_message' =>
                    'Internal error',
            ]);
        }
    }

    /**
     * ZaloPay redirect browser về đây.
     */
    public function result(Request $request)
    {
        $key2 = (string) config(
            'services.zalopay.key2'
        );

        if (!$key2) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Thiếu cấu hình ZaloPay Key2.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Các query params ZaloPay redirect
        |--------------------------------------------------------------------------
        */

        $appId = (string) $request->query(
            'appid',
            ''
        );

        $appTransId = (string) $request->query(
            'apptransid',
            ''
        );

        $pmcId = (string) $request->query(
            'pmcid',
            ''
        );

        $bankCode = (string) $request->query(
            'bankcode',
            ''
        );

        $amount = (string) $request->query(
            'amount',
            ''
        );

        $discountAmount = (string) $request->query(
            'discountamount',
            ''
        );

        $status = (string) $request->query(
            'status',
            ''
        );

        $receivedChecksum = (string)
            $request->query(
                'checksum',
                ''
            );

        /*
        |--------------------------------------------------------------------------
        | Verify checksum redirect
        |--------------------------------------------------------------------------
        */

        $checksumData = implode('|', [
            $appId,
            $appTransId,
            $pmcId,
            $bankCode,
            $amount,
            $discountAmount,
            $status,
        ]);

        $expectedChecksum = hash_hmac(
            'sha256',
            $checksumData,
            $key2
        );

        if (
            !$receivedChecksum ||
            !hash_equals(
                strtolower($expectedChecksum),
                strtolower($receivedChecksum)
            )
        ) {
            Log::warning(
                'ZaloPay redirect invalid checksum',
                [
                    'app_trans_id' =>
                        $appTransId,
                ]
            );

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Phản hồi ZaloPay không hợp lệ.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Verify app_id
        |--------------------------------------------------------------------------
        */

        if (
            (int) $appId !==
            (int) config(
                'services.zalopay.app_id'
            )
        ) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'App ID ZaloPay không hợp lệ.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Nếu redirect báo chưa thành công
        |--------------------------------------------------------------------------
        */

        if ($status !== '1') {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Giao dịch ZaloPay chưa thành công hoặc đã bị hủy.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Lấy order ID trong app_trans_id
        |--------------------------------------------------------------------------
        */

        $parts = explode(
            '_',
            $appTransId
        );

        $orderId =
            $parts[1] ?? null;

        if (!$orderId) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Không xác định được đơn hàng ZaloPay.'
                );
        }

        $order = Order::find($orderId);

        if (!$order) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Không tìm thấy đơn hàng.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Không tin redirect tuyệt đối.
        | Query trực tiếp ZaloPay nếu DB chưa paid.
        |--------------------------------------------------------------------------
        */

        if (
            $order->payment_status
            !== 'paid'
        ) {
            $this->queryAndSyncOrder(
                $order,
                $appTransId
            );

            $order->refresh();
        }

        if (
            $order->payment_status
            !== 'paid'
        ) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'warning',
                    'ZaloPay đang xác nhận giao dịch. Vui lòng không thanh toán lại đơn hàng.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Đồng bộ session checkout
        |--------------------------------------------------------------------------
        */

        session([
            'checkout_order' => array_merge(
                session(
                    'checkout_order',
                    []
                ),
                [
                    'id' =>
                        (string) $order->id,

                    'code' =>
                        $order->code,

                    'total' =>
                        $order->total,
                ]
            ),

            'checkout_payment' => [
                'status' =>
                    'paid',

                'transaction_id' =>
                    $appTransId,

                'paid_at' =>
                    now()->toDateTimeString(),

                'bank' =>
                    'ZaloPay',

                'amount' =>
                    $order->total,

                'content' =>
                    $order->code,
            ],
        ]);

        return redirect()
            ->route('checkout.success');
    }

    /**
     * Query trạng thái giao dịch trực tiếp từ ZaloPay.
     */
    private function queryAndSyncOrder(
        Order $order,
        string $appTransId
    ): bool {
        $appId = (int) config(
            'services.zalopay.app_id'
        );

        $key1 = (string) config(
            'services.zalopay.key1'
        );

        $endpoint = (string) config(
            'services.zalopay.query_endpoint',
            'https://sb-openapi.zalopay.vn/v2/query'
        );

        if (
            !$appId ||
            !$key1 ||
            !$endpoint
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Query MAC
        |--------------------------------------------------------------------------
        */

        $macInput =
            $appId
            . '|'
            . $appTransId
            . '|'
            . $key1;

        $mac = hash_hmac(
            'sha256',
            $macInput,
            $key1
        );

        try {
            $response = Http::timeout(20)
                ->asForm()
                ->post($endpoint, [
                    'app_id' =>
                        $appId,

                    'app_trans_id' =>
                        $appTransId,

                    'mac' =>
                        $mac,
                ]);

            $result =
                $response->json();

            if (!is_array($result)) {
                $result = [];
            }

            Log::info(
                'ZaloPay query order',
                [
                    'order_id' =>
                        $order->id,

                    'app_trans_id' =>
                        $appTransId,

                    'http_status' =>
                        $response->status(),

                    'response' =>
                        $result,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | return_code != 1:
            | pending / failed / system error
            |--------------------------------------------------------------------------
            */

            if (
                !$response->successful() ||
                (int) (
                    $result['return_code']
                    ?? 0
                ) !== 1
            ) {
                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | Verify amount
            |--------------------------------------------------------------------------
            */

            $paidAmount = (int) (
                $result['amount'] ?? 0
            );

            if (
                $paidAmount !==
                (int) $order->total
            ) {
                Log::warning(
                    'ZaloPay query amount mismatch',
                    [
                        'order_id' =>
                            $order->id,

                        'db_amount' =>
                            $order->total,

                        'zalopay_amount' =>
                            $paidAmount,
                    ]
                );

                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | Cập nhật paid an toàn
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use ($order) {
                    $lockedOrder =
                        Order::query()
                            ->lockForUpdate()
                            ->find($order->id);

                    if (
                        $lockedOrder &&
                        $lockedOrder
                            ->payment_method
                            === 'zalopay' &&
                        $lockedOrder
                            ->payment_status
                            !== 'paid'
                    ) {
                        $lockedOrder
                            ->payment_status =
                            'paid';

                        $lockedOrder->save();
                    }
                }
            );

            return true;

        } catch (\Throwable $e) {
            Log::error(
                'ZaloPay query exception',
                [
                    'order_id' =>
                        $order->id,

                    'app_trans_id' =>
                        $appTransId,

                    'message' =>
                        $e->getMessage(),
                ]
            );

            return false;
        }
    }
}