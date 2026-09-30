<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Notification;

class OrderStatusNotificationTest extends TestCase
{
    public function test_notification_sent_on_status_change()
    {
        Notification::fake();

        $order = Order::factory()->create(['status' => 'pending']);
        $order->update(['status' => 'confirmed']);

        Notification::assertSentTo(
            $order->user,
            \App\Notifications\OrderStatusNotification::class,
            fn ($n) => $n->order->id === $order->id
        );
    }
}