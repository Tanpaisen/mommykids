<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use App\Services\CampaignService;
use App\Services\GHNService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private GHNService $ghn,
        private CampaignService $campaignService
    ) {}

    /** Danh sách đơn hàng */
    public function index(Request $request)
    {
        $query = Order::query()
            ->with(['user', 'shipment'])
            ->withCount([
                'items',
                'items as campaign_items_count' => function ($query) {
                    $query->whereNotNull('campaign_id');
                },
            ])
            ->withSum(
                'items as campaign_discount_total',
                'campaign_discount_amount'
            );

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_phone', 'like', "%{$search}%")
                    ->orWhere('recipient_email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        /*
         * Lọc đơn có/không có sản phẩm áp dụng Campaign.
         */
        $campaignFilter = (string) $request->input('campaign', '');

        if ($campaignFilter === 'yes') {
            $query->whereHas('items', function ($itemQuery) {
                $itemQuery->whereNotNull('campaign_id');
            });
        }

        if ($campaignFilter === 'no') {
            $query->whereDoesntHave('items', function ($itemQuery) {
                $itemQuery->whereNotNull('campaign_id');
            });
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipping' => Order::where('status', 'shipping')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'paid' => Order::where('payment_status', 'paid')->count(),
            'campaign_orders' => Order::query()
                ->whereHas('items', function ($itemQuery) {
                    $itemQuery->whereNotNull('campaign_id');
                })
                ->count(),
            'revenue' => Order::where('payment_status', 'paid')
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->sum('total'),
        ];

        return view(
            'admin.orders.index',
            compact('orders', 'stats')
        );
    }

    /** Chi tiết đơn + tab vận chuyển */
    public function show(Order $order)
    {
        $order->load([
            'items.product',
            'shipment',
            'user',
            'voucherUsages.voucher',
        ]);

        $ghnData = null;

        // Nếu đã có vận đơn → lấy thông tin realtime từ GHN
        if ($order->shipment?->ghn_order_code) {
            $ghnData = $this->ghn->trackOrder(
                $order->shipment->ghn_order_code
            );

            // Cập nhật status mới nhất vào shipment
            if (!empty($ghnData['status'])) {
                $order->shipment->update([
                    'status' => strtolower($ghnData['status']),
                    'ghn_response' => $ghnData,
                ]);
            }
        }

        return view(
            'admin.orders.show',
            compact('order', 'ghnData')
        );
    }

    /** Cập nhật trạng thái đơn */
    public function updateStatus(
        Request $request,
        Order $order
    ) {
        $data = $request->validate([
            'status' => [
                'required',
                'in:pending,confirmed,processing,shipping,delivered,cancelled',
            ],
        ]);

        $transitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['cancelled'],
            'shipping' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
            'refunded' => [],
        ];

        $nextStatus = $data['status'];

        if (
            !in_array(
                $nextStatus,
                $transitions[$order->status] ?? [],
                true
            )
        ) {
            return back()->with(
                'error',
                'Không thể chuyển trạng thái đơn hàng từ '
                    . $order->status
                    . ' sang '
                    . $nextStatus
                    . '.'
            );
        }

        /*
         * Cập nhật trạng thái qua một hàm chung để:
         * - chống cộng sold_count trùng
         * - tự cộng sold_count khi delivered
         * - tự trừ nếu rời delivered ở các luồng hệ thống khác
         * - COD delivered => paid
         */
        $this->persistOrderStatus(
            $order,
            $nextStatus
        );

        return back()->with(
            'success',
            'Cập nhật trạng thái đơn hàng thành công.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SOLD COUNT + ORDER STATUS
    |--------------------------------------------------------------------------
    */

    /**
     * Lưu trạng thái đơn an toàn trong transaction.
     *
     * Khi:
     * - non-delivered -> delivered: cộng sold_count
     * - delivered -> non-delivered: trừ sold_count
     * - delivered -> delivered: không cộng lần hai
     */
    private function persistOrderStatus(
        Order $order,
        string $newStatus
    ): void {
        DB::transaction(function () use ($order, $newStatus) {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            $oldStatus = (string) $lockedOrder->status;

            /*
             * Nếu trạng thái không đổi, không tác động sold_count.
             * Riêng COD delivered vẫn đảm bảo payment_status = paid.
             */
            if ($oldStatus === $newStatus) {
                if (
                    $newStatus === 'delivered'
                    && $lockedOrder->payment_method === 'cod'
                    && $lockedOrder->payment_status !== 'paid'
                ) {
                    $lockedOrder->payment_status = 'paid';
                    $lockedOrder->save();
                }

                return;
            }

            /*
             * Load items khi:
             * - thay đổi có liên quan delivered để cập nhật sold_count
             * - chuyển sang cancelled/refunded để hoàn lại quota Campaign
             */
            $isEnteringCancelledOrRefunded =
                !in_array(
                    $oldStatus,
                    ['cancelled', 'refunded'],
                    true
                )
                && in_array(
                    $newStatus,
                    ['cancelled', 'refunded'],
                    true
                );

            if (
                $oldStatus === 'delivered'
                || $newStatus === 'delivered'
                || $isEnteringCancelledOrRefunded
            ) {
                $lockedOrder->loadMissing('items');
            }

            if (
                $oldStatus !== 'delivered'
                && $newStatus === 'delivered'
            ) {
                $this->increaseSoldCount(
                    $lockedOrder
                );
            }

            if (
                $oldStatus === 'delivered'
                && $newStatus !== 'delivered'
            ) {
                $this->decreaseSoldCount(
                    $lockedOrder
                );
            }

            /*
             * Campaign được reserve ngay khi tạo đơn.
             * Khi đơn lần đầu chuyển sang cancelled/refunded,
             * hoàn lại sold_quantity của Campaign.
             */
            if ($isEnteringCancelledOrRefunded) {
                $this->releaseCampaignReservations(
                    $lockedOrder
                );
            }

            $lockedOrder->status = $newStatus;

            /*
             * COD chỉ được xem là đã thanh toán
             * khi giao thành công.
             */
            if (
                $newStatus === 'delivered'
                && $lockedOrder->payment_method === 'cod'
            ) {
                $lockedOrder->payment_status = 'paid';
            }

            $lockedOrder->save();
        });

        /*
         * Đồng bộ lại instance đang dùng trong controller.
         */
        $order->refresh();
    }

    /** Cộng lượt bán khi đơn chuyển sang delivered. */
    private function increaseSoldCount(
        Order $order
    ): void {
        foreach ($order->items as $item) {
            $quantity = max(
                0,
                (int) $item->quantity
            );

            if ($quantity <= 0) {
                continue;
            }

            /*
             * Lock product để tránh mất lượt bán nếu nhiều
             * đơn delivered cùng lúc.
             *
             * withTrashed() để vẫn xử lý đúng đơn lịch sử
             * nếu sản phẩm đã bị soft delete.
             */
            $product = Product::withTrashed()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                continue;
            }

            $product->sold_count = max(
                0,
                (int) $product->sold_count
            ) + $quantity;

            $product->save();
        }
    }

    /** Trừ lượt bán nếu đơn rời khỏi delivered. */
    private function decreaseSoldCount(
        Order $order
    ): void {
        foreach ($order->items as $item) {
            $quantity = max(
                0,
                (int) $item->quantity
            );

            if ($quantity <= 0) {
                continue;
            }

            $product = Product::withTrashed()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                continue;
            }

            $product->sold_count = max(
                0,
                (int) $product->sold_count - $quantity
            );

            $product->save();
        }
    }

    /**
     * Hoàn lại quota Campaign khi đơn bị hủy/hoàn tiền.
     *
     * Chỉ OrderItem có campaign_id mới được xử lý.
     * withTrashed() vẫn tìm được Campaign đã soft delete.
     */
    private function releaseCampaignReservations(
        Order $order
    ): void {
        foreach ($order->items as $item) {
            $campaignId = (int) ($item->campaign_id ?? 0);
            $productId = (int) ($item->product_id ?? 0);
            $quantity = max(
                0,
                (int) $item->quantity
            );

            if (
                $campaignId <= 0
                || $productId <= 0
                || $quantity <= 0
            ) {
                continue;
            }

            $campaign = Campaign::withTrashed()
                ->find($campaignId);

            if (!$campaign) {
                continue;
            }

            $this->campaignService
                ->releaseCampaignStock(
                    $campaign,
                    $productId,
                    $quantity
                );
        }
    }

    // ─── GHN ─────────────────────────────────────────────────────────────────

    /** Tính phí ship (AJAX) */
    public function calcFee(Request $request)
    {
        $request->validate([
            'district_id' => 'required|integer',
            'ward_code' => 'required|string',
            'weight' => 'integer|min:1',
        ]);

        $data = $this->ghn->calculateFee(
            toDistrictId: $request->district_id,
            toWardCode: $request->ward_code,
            weight: $request->integer('weight', 500),
            insuranceValue: $request->integer('insurance_value', 0),
        );

        return response()->json($data);
    }

    /** Tạo vận đơn GHN */
    public function createShipment(
        Request $request,
        Order $order
    ) {
        if (
            $order->shipment?->ghn_order_code
            && $order->shipment->status !== 'cancel'
        ) {
            return back()->with(
                'error',
                'Đơn này đã có mã vận đơn GHN đang hoạt động.'
            );
        }

        if ($order->status !== 'processing') {
            return back()->with(
                'error',
                'Chỉ có thể tạo vận đơn khi đơn hàng đang ở trạng thái Đang xử lý.'
            );
        }

        $request->validate([
            'weight' => 'required|integer|min:1',
            'length' => 'required|integer|min:1',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        /*
         * Đảm bảo items đã được load trước khi tạo payload.
         */
        $order->loadMissing('items');

        $payload = [
            'to_name' => $order->recipient_name,
            'to_phone' => $order->recipient_phone,
            'to_address' => $order->address_detail,
            'to_ward_code' => $order->ghn_ward_code,
            'to_district_id' => $order->ghn_district_id,
            'weight' => $request->integer('weight'),
            'length' => $request->integer('length'),
            'width' => $request->integer('width'),
            'height' => $request->integer('height'),
            'cod_amount' =>
                $order->payment_method === 'cod'
                    ? $order->total
                    : 0,
            'insurance_value' => $order->subtotal,
            'note' => $request->note ?? $order->note ?? '',
            'items' => $order->items
                ->map(
                    fn ($i) => [
                        'name' => $i->product_name,
                        'quantity' => $i->quantity,


                        'weight' => 200,
                    ]
                )
                ->toArray(),
        ];

        $result = $this->ghn->createOrder(
            $payload
        );

        if (empty($result['order_code'])) {
            return back()->with(
                'error',
                'Tạo vận đơn GHN thất bại: '
                    . ($result['message'] ?? 'unknown')
            );
        }

        // Lưu shipment
        $order->shipment()->updateOrCreate(
            [
                'order_id' => $order->id,
            ],
            [
                'ghn_order_code' => $result['order_code'],
                'tracking_number' => $result['order_code'],
                'shipping_fee' => $result['total_fee'] ?? 0,
                'weight' => $request->integer('weight'),
                'length' => $request->integer('length'),
                'width' => $request->integer('width'),
                'height' => $request->integer('height'),
                'status' => 'pending',
                'printed_at' => null,
                'expected_delivery_at' =>
                    isset($result['expected_delivery_time'])
                        ? \Carbon\Carbon::parse(
                            $result['expected_delivery_time']
                        )
                        : null,
                'ghn_response' => $result,
            ]
        );

        /*
         * Tạo vận đơn thành công => đơn chuyển sang shipping.
         * Không ảnh hưởng sold_count.
         */
        $this->persistOrderStatus(
            $order,
            'shipping'
        );

        return back()->with(
            'success',
            "Tạo vận đơn thành công! Mã GHN: {$result['order_code']}"
        );
    }

    /** Tra cứu trạng thái GHN realtime */
    public function trackShipment(Order $order)
    {
        if (!$order->shipment?->ghn_order_code) {
            return back()->with(
                'error',
                'Đơn chưa có mã vận đơn GHN.'
            );
        }

        $data = $this->ghn->trackOrder(
            $order->shipment->ghn_order_code
        );

        if (!empty($data['status'])) {
            $ghnStatus = strtolower(
                $data['status']
            );

            // Cập nhật trạng thái vận đơn
            $order->shipment->update([
                'status' => $ghnStatus,
                'ghn_response' => $data,
            ]);

            $newOrderStatus = (string) $order->status;

            if (
                in_array(
                    $ghnStatus,
                    [
                        'ready_to_pick',
                        'picking',
                        'picked',
                        'storing',
                        'transporting',
                        'sorting',
                        'delivering',
                        'money_collect_delivering',
                    ],
                    true
                )
            ) {
                $newOrderStatus = 'shipping';
            }

            if ($ghnStatus === 'delivered') {
                $newOrderStatus = 'delivered';
            }

            if (
                in_array(
                    $ghnStatus,
                    ['cancel', 'cancelled'],
                    true
                )
            ) {
                $newOrderStatus = 'cancelled';
            }

            $this->persistOrderStatus(
                $order,
                $newOrderStatus
            );
        }

        return back()->with(
            'success',
            'Đã cập nhật trạng thái vận đơn GHN.'
        );
    }

    /** In vận đơn GHN */
    public function printShipment(Order $order)
    {
        if (!$order->shipment?->ghn_order_code) {
            return back()->with(
                'error',
                'Chưa có vận đơn GHN để in.'
            );
        }

        try {
            // Lấy URL in từ GHN Service
            $url = $this->ghn->getPrintUrl(
                $order->shipment->ghn_order_code
            );

            // Cập nhật thời gian in
            $order->shipment->update([
                'printed_at' => now(),
            ]);

            return redirect($url);
        } catch (\Exception $e) {
            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    /** Hủy vận đơn GHN */
    public function cancelShipment(Order $order)
    {
        if (!$order->shipment?->ghn_order_code) {
            return back()->with(
                'error',
                'Không tìm thấy vận đơn để hủy.'
            );
        }

        try {
            $this->ghn->cancelOrder(
                $order->shipment->ghn_order_code
            );

            // Cập nhật trạng thái shipment
            $order->shipment->update([
                'status' => 'cancel',
            ]);


            $this->persistOrderStatus(
                $order,
                'processing'
            );

            return back()->with(
                'success',
                'Đã hủy vận đơn GHN thành công.'
            );
        } catch (\Exception $e) {
            return back()->with(
                'error',
                'Lỗi hủy vận đơn: ' . $e->getMessage()
            );
        }
    }
}
