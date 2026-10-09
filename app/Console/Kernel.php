<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        /*
         * Refresh GHN 2 lần/ngày.
         *
         * --force-ghn ở bản stale-safe KHÔNG xóa cache cũ trước.
         * Nó chỉ bỏ qua cache đọc để lấy dữ liệu mới; nếu GHN lỗi
         * thì GHNService vẫn giữ/serve cache cũ.
         */
        $schedule
            ->command('cache:warm --force-ghn --ghn-delay=100')
            ->twiceDaily(0, 12)
            ->withoutOverlapping(180)
            ->onOneServer()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
