<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderCancellationRequest;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Order::query()
            ->where('user_id', $userId)
            ->with('shipment')
            ->withCount('items');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_phone', 'like', "%{$search}%");
            });
        }

        $allowedStatuses = [
            'pending',
            'confirmed',
            'processing',
            'shipping',
            'delivered',
            'cancelled',
            'refunded',
        ];

        if (
            $request->filled('status')
            && in_array($request->input('status'), $allowedStatuses, true)
        ) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $base = Order::query()->where('user_id', $userId);

        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'processing' => (clone $base)
                ->where('status', 'processing')
                ->count(),
            'shipping' => (clone $base)->where('status', 'shipping')->count(),
            'delivered' => (clone $base)->where('status', 'delivered')->count(),
        ];

        return view('client.orders.index', compact('orders', 'stats'));
    }

    public function show(string $order)
    {
        $order = Order::query()
            ->where('user_id', Auth::id())
            ->where('code', $order)
            ->with([
                'items.product',
                'shipment',
                'voucherUsages.voucher',
            ])
            ->firstOrFail();
            $cancellationRequest = OrderCancellationRequest::query()
    ->where('order_id', $order->id)
    ->where('user_id', Auth::id())
    ->latest()
    ->first();

      return view(
    'client.orders.show',
    compact(
        'order',
        'cancellationRequest'
    )
);
    }

    public function requestCancellation(
        Request $request,
        string $order
    ) {
    $order = Order::query()
        ->where('user_id', Auth::id())
        ->where('code', $order)
        ->firstOrFail();

    if (! in_array(
        $order->status,
        [
            'pending',
            'confirmed',
            'processing',
        ],
        true
    )) {
        return back()->with(
            'error',
            'Đơn hàng này không còn đủ điều kiện để yêu cầu huỷ.'
        );
    }

    $data = $request->validate([
        'reason' => [
            'required',
            'string',
            'min:5',
            'max:1000',
        ],
    ], [
        'reason.required' => 'Vui lòng nhập lý do huỷ đơn.',
        'reason.min' => 'Lý do huỷ phải có ít nhất 5 ký tự.',
        'reason.max' => 'Lý do huỷ không được vượt quá 1000 ký tự.',
    ]);

    $existingRequest = OrderCancellationRequest::query()
        ->where('order_id', $order->id)
        ->where('user_id', Auth::id())
        ->where('status', 'pending')
        ->exists();

    if ($existingRequest) {
        return back()->with(
            'error',
            'Bạn đã gửi yêu cầu huỷ cho đơn hàng này.'
        );
    }

    OrderCancellationRequest::create([
        'order_id' => $order->id,
        'user_id' => Auth::id(),
        'reason' => $data['reason'],
        'status' => 'pending',
    ]);

    return back()->with(
        'success',
        'Yêu cầu huỷ đơn đã được gửi. Vui lòng chờ cửa hàng xác nhận.'
    );
}
}