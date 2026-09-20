<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\GHNService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private GHNService $ghn) {}

    /** Danh sách đơn hàng */
    public function index(Request $request)
    {
        $query = Order::query()
            ->with(['user', 'shipment'])
            ->withCount('items');

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
            'revenue' => Order::where('payment_status', 'paid')
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->sum('total'),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
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
            $ghnData = $this->ghn->trackOrder($order->shipment->ghn_order_code);

            // Cập nhật status mới nhất vào DB
            if (!empty($ghnData['status'])) {
                $order->shipment->update([
                    'status'       => strtolower($ghnData['status']),
                    'ghn_response' => $ghnData,
                ]);
            }
        }

        return view('admin.orders.show', compact('order', 'ghnData'));
    }

    /** Cập nhật trạng thái đơn */
    public function updateStatus(Request $request, Order $order)
    {
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

        if (!in_array($nextStatus, $transitions[$order->status] ?? [], true)) {
            return back()->with(
                'error',
                'Không thể chuyển trạng thái đơn hàng từ ' . $order->status . ' sang ' . $nextStatus . '.'
            );
        }

        $order->status = $nextStatus;

        /*
         * COD được xem là đã thu tiền khi giao thành công.
         */
        if ($nextStatus === 'delivered' && $order->payment_method === 'cod') {
            $order->payment_status = 'paid';
        }

        $order->save();

        return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    // ─── GHN ─────────────────────────────────────────────────────────────────

    /** Tính phí ship (AJAX) */
    public function calcFee(Request $request)
    {
        $request->validate([
            'district_id' => 'required|integer',
            'ward_code'   => 'required|string',
            'weight'      => 'integer|min:1',
        ]);

        $data = $this->ghn->calculateFee(
            toDistrictId  : $request->district_id,
            toWardCode    : $request->ward_code,
            weight        : $request->integer('weight', 500),
            insuranceValue: $request->integer('insurance_value', 0),
        );

        return response()->json($data);
    }

    /** Tạo vận đơn GHN */
    public function createShipment(Request $request, Order $order)
    {
        if ($order->shipment?->ghn_order_code && $order->shipment->status !== 'cancel') {
            return back()->with('error', 'Đơn này đã có mã vận đơn GHN đang hoạt động.');
        }
        
        if ($order->status !== 'processing') {
            return back()->with('error', 'Chỉ có thể tạo vận đơn khi đơn hàng đang ở trạng thái Đang xử lý.');
        }

        $request->validate([
            'weight' => 'required|integer|min:1',
            'length' => 'required|integer|min:1',
            'width'  => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
            'note'   => 'nullable|string|max:500',
        ]);

        $payload = [
            'to_name'        => $order->recipient_name,
            'to_phone'       => $order->recipient_phone,
            'to_address'     => $order->address_detail,
            'to_ward_code'   => $order->ghn_ward_code,
            'to_district_id' => $order->ghn_district_id,
            'weight'         => $request->integer('weight'),
            'length'         => $request->integer('length'),
            'width'          => $request->integer('width'),
            'height'         => $request->integer('height'),
            'cod_amount'     => $order->payment_method === 'cod' ? $order->total : 0,
            'insurance_value'=> $order->subtotal,
            'note'           => $request->note ?? $order->note ?? '',
            'items'          => $order->items->map(fn($i) => [
                'name'     => $i->product_name,
                'quantity' => $i->quantity,
                'weight'   => 200, // gram mỗi món (có thể lấy từ product)
            ])->toArray(),
        ];

        $result = $this->ghn->createOrder($payload);

        if (empty($result['order_code'])) {
            return back()->with('error', 'Tạo vận đơn GHN thất bại: ' . ($result['message'] ?? 'unknown'));
        }

        // Lưu shipment
        $order->shipment()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'ghn_order_code'       => $result['order_code'],
                'tracking_number'      => $result['order_code'],
                'shipping_fee'         => $result['total_fee'] ?? 0,
                'weight'               => $request->integer('weight'),
                'length'               => $request->integer('length'),
                'width'                => $request->integer('width'),
                'height'               => $request->integer('height'),
                'status'               => 'pending',
                'printed_at'           => null,
                'expected_delivery_at' => isset($result['expected_delivery_time'])
                    ? \Carbon\Carbon::parse($result['expected_delivery_time'])
                    : null,
                'ghn_response'         => $result,
            ]
        );

        // Cập nhật trạng thái đơn
        $order->update(['status' => 'shipping']);

        return back()->with('success', "Tạo vận đơn thành công! Mã GHN: {$result['order_code']}");
    }

    /** Tra cứu trạng thái GHN realtime */
    public function trackShipment(Order $order)
    {
        if (! $order->shipment?->ghn_order_code) {
            return back()->with('error', 'Đơn chưa có mã vận đơn GHN.');
        }

        $data = $this->ghn->trackOrder($order->shipment->ghn_order_code);

        if (! empty($data['status'])) {
            $ghnStatus = strtolower($data['status']);

            // Cập nhật trạng thái vận đơn
            $order->shipment->update([
                'status' => $ghnStatus,
                'ghn_response' => $data,
            ]);

            /* Đồng bộ GHN -> trạng thái đơn hàng. */
            if (in_array($ghnStatus, [
                'ready_to_pick', 'picking', 'picked', 'storing',
                'transporting', 'sorting', 'delivering', 'money_collect_delivering',
            ], true)) {
                $order->status = 'shipping';
            }

            if ($ghnStatus === 'delivered') {
                $order->status = 'delivered';

                // COD chỉ được coi là đã thanh toán khi giao thành công
                if ($order->payment_method === 'cod') {
                    $order->payment_status = 'paid';
                }
            }

            if (in_array($ghnStatus, ['cancel', 'cancelled'], true)) {
                $order->status = 'cancelled';
            }

            $order->save();
        }

        return back()->with('success', 'Đã cập nhật trạng thái vận đơn GHN.');
    }

    /** In vận đơn GHN */
    public function printShipment(Order $order)
    {
        if (!$order->shipment?->ghn_order_code) {
            return back()->with('error', 'Chưa có vận đơn GHN để in.');
        }

        try {
            // Lấy URL in từ GHN Service
            $url = $this->ghn->getPrintUrl($order->shipment->ghn_order_code);
            
            // Cập nhật thời gian in
            $order->shipment->update(['printed_at' => now()]);

            return redirect($url);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Hủy vận đơn GHN */
    public function cancelShipment(Order $order)
    {
        if (!$order->shipment?->ghn_order_code) {
            return back()->with('error', 'Không tìm thấy vận đơn để hủy.');
        }

        try {
            $this->ghn->cancelOrder($order->shipment->ghn_order_code);
            
            // Cập nhật trạng thái shipment
            $order->shipment->update(['status' => 'cancel']);

            // Có thể đưa đơn hàng về lại trạng thái processing để cho phép tạo vận đơn mới
            $order->update(['status' => 'processing']);

            return back()->with('success', 'Đã hủy vận đơn GHN thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi hủy vận đơn: ' . $e->getMessage());
        }
    }
}