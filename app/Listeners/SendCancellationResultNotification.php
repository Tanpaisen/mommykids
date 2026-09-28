<?php

namespace App\Listeners;

use App\Events\CancellationRequestProcessed;
use App\Notifications\CancellationResultNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SendCancellationResultNotification implements ShouldQueue
{
    public $afterCommit = true;
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
    public function handle(CancellationRequestProcessed $event): void
    {
        $request = $event->request;
        $user = $request->user;

        if (! $user) {
            return;
        }

        $key = "notif_sent:cancel:{$request->id}:{$request->status}";
        if (! Cache::add($key, true, now()->addHours(1))) {
            return;
        }

        try {
            $user->notify(new CancellationResultNotification($request));
        } catch (Throwable $e) {
            Cache::forget($key);
            throw $e;
        }
    }
}
