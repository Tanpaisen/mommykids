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
        $orders = Order::with(['user', 'shipment'])
            ->when($request->search, fn($q, $s) =>
                $q->where('code', 'like', "%$s%")
                  ->orWhere('recipient_name', 'like', "%$s%")
                  ->orWhere('recipient_phone', 'like', "%$s%")
            )
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /** Chi tiết đơn + tab vận chuyển */
    public function show(Order $order)
    {
        $order->load(['items.product', 'shipment', 'user']);
        return view('admin.orders.show', compact('order'));
    }

    /** Cập nhật trạng thái đơn */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipping,delivered,cancelled,refunded',
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Cập nhật trạng thái thành công.');
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
        if ($order->shipment?->ghn_order_code) {
            return back()->with('error', 'Đơn này đã có mã vận đơn GHN.');
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
                'expected_delivery_at' => isset($result['expected_delivery_time'])
                    ? \Carbon\Carbon::parse($result['expected_delivery_time'])
                    : null,
                'ghn_response'         => $result,
            ]
        );

        // Cập nhật trạng thái đơn
        $order->update([
            'status'       => 'shipping',
            'shipping_fee' => $result['total_fee'] ?? $order->shipping_fee,
        ]);

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
            $order->shipment->update([
                'status'       => strtolower($data['status']),
                'ghn_response' => $data,
            ]);
        }

        return back()->with('success', 'Đã cập nhật trạng thái vận đơn.');
    }

    /** In vận đơn — redirect tới URL PDF của GHN */
    public function printLabel(Order $order)
    {
        if (! $order->shipment?->ghn_order_code) {
            return back()->with('error', 'Đơn chưa có mã vận đơn GHN.');
        }

        try {
            $url = $this->ghn->getPrintUrl([$order->shipment->ghn_order_code]);
            return redirect($url);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Huỷ vận đơn GHN */
    public function cancelShipment(Order $order)
    {
        if (! $order->shipment?->ghn_order_code) {
            return back()->with('error', 'Đơn chưa có mã vận đơn GHN.');
        }

        $this->ghn->cancelOrder($order->shipment->ghn_order_code);

        $order->shipment->update(['status' => 'cancel']);
        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Đã huỷ vận đơn GHN.');
    }
}