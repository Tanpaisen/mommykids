<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $shipments = Shipment::query()
            ->with('order')
            ->when($request->filled('q'), fn ($q) =>
                $q->where('ghn_order_code', 'like', "%{$request->q}%")
                  ->orWhereHas('order', fn ($q2) =>
                      $q2->where('code', 'like', "%{$request->q}%")
                  )
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status)
            )
            ->latest()
            ->paginate(20);

        $counts = Shipment::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.orders.shipments', compact('shipments', 'counts'));
    }

    public function apiIndex(Request $request)
    {
        $shipments = Shipment::query()
            ->with('order')
            ->when($request->filled('q'), fn ($q) =>
                $q->where('ghn_order_code', 'like', "%{$request->q}%")
                ->orWhereHas('order', fn ($q2) =>
                    $q2->where('code', 'like', "%{$request->q}%")
                )
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status)
            )
            ->latest()
            ->paginate(20);

        $counts = Shipment::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'data' => $shipments->map(fn ($s) => [
                'ghn_order_code'       => $s->ghn_order_code,
                'order_code'           => $s->order->code,
                'recipient_name'       => $s->order->recipient_name,
                'recipient_phone'      => $s->order->recipient_phone,
                'shipping_fee'         => $s->shipping_fee,
                'status'               => $s->status,
                'expected_delivery_at' => $s->expected_delivery_at?->format('d/m/Y'),
            ]),
            'counts' => $counts,
            'total'  => $shipments->total(),
        ]);
    }
}