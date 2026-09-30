<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;

class CreateDefaultNotificationPreferences
{
    public function handle(Registered $event): void
    {
        $event->user->notificationPreferences()->createMany([
            ['type' => 'order_status', 'database' => true, 'mail' => true],
            ['type' => 'marketing', 'database' => true, 'mail' => false],
        ]);
    }
}