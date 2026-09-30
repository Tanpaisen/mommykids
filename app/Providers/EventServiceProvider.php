<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\OrderStatusChanged;
use App\Listeners\SendOrderStatusNotification;
use Illuminate\Notifications\Events\NotificationSent;
use App\Listeners\CreateDefaultNotificationPreferences;
use App\Events\CancellationRequestProcessed;
use App\Listeners\SendCancellationResultNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            CreateDefaultNotificationPreferences::class,
        ],
        Login::class => [
            // Tạm thời ẩn do chưa tạo file MergeCartAfterLogin
        ],
        OrderStatusChanged::class => [
            SendOrderStatusNotification::class,
        ],
        NotificationSent::class => [
            \App\Listeners\LogNotificationSent::class,
        ],
        CancellationRequestProcessed::class => [
            SendCancellationResultNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}