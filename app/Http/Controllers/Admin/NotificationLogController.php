<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'channel' => 'nullable|in:database,mail',
            'status'  => 'nullable|in:pending,sent,failed',
            'q'       => 'nullable|string|max:100',
            'from'    => 'nullable|date',
            'to'      => 'nullable|date',
        ]);

        $logs = NotificationLog::query()
            ->with('user:id,name,email')
            ->when($filters['channel'] ?? null, fn ($q, $v) => $q->where('channel', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($filters['q'] ?? null, function ($q, $v) {
                $q->whereHas('user', fn ($u) => $u
                    ->where('email', 'like', "%{$v}%")
                    ->orWhere('name', 'like', "%{$v}%"));
            })
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        // Thống kê 24h qua: ['sent' => 12, 'failed' => 1]
        $stats = NotificationLog::where('created_at', '>=', now()->subDay())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.notification.logs.index', compact('logs', 'stats', 'filters'));
    }
}
