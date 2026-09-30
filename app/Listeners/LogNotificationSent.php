<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\NotificationLog;
use Illuminate\Notifications\Events\NotificationSent;

class LogNotificationSent
{
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
    public function handle(NotificationSent $event): void
    {
        NotificationLog::create([
            'notification_id' => is_object($event->response) ? ($event->response->id ?? null) : null,
            'user_id'         => $event->notifiable->id ?? null,
            'campaign_id'     => $event->notification instanceof MarketingNotification
                                    ? $event->notification->campaign->id
                                    : null,
            'channel'         => $event->channel,
            'status'          => 'sent',
        ]);
    }
}
