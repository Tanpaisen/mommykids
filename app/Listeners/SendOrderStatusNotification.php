<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SendOrderStatusNotification implements ShouldQueue
{
    public $afterCommit = true; // chỉ chạy sau khi transaction cha commit
    public $tries = 3;
    public $backoff = [10, 30, 60];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    

    public function handle(OrderStatusChanged $event): void
    {
        $user = $event->order->user;
        if (!$user) {
            return;
        }

        $key = "notif_sent:{$order->id}:{$event->oldStatus}:{$event->newStatus}";
        if (! Cache::add($key, true, now()->addHours(1))) {
            return;
        }

        if ($event->newStatus === 'cancelled'
            && $order->cancellationRequests()->where('status', 'approved')->exists()) {
            return; // đã có thông báo kết quả yêu cầu huỷ
        }

        try {
            $notification = new OrderStatusNotification($order, $event->newStatus);

            if ($user) {
                $user->notify($notification);
            } else {
                Notification::route('mail', $order->recipient_email)->notify($notification);
            }
        } catch (Throwable $e) {
            Cache::forget($key);
            throw $e;
        }
    }
}
