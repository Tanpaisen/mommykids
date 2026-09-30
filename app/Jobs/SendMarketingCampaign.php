<?php

namespace App\Jobs;

use App\Models\NotificationCampaign;
use App\Models\User;
use App\Notifications\MarketingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class SendMarketingCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 1;      // không retry: retry sẽ gửi lại cho người đã nhận
    public $timeout = 600;

    public function __construct(public string $campaignId) {}

    public function handle(): void
    {
        $campaign = NotificationCampaign::findOrFail($this->campaignId);
        $total = 0;

        User::query()
            ->where('is_active', true) // chỉnh theo cột trạng thái thật của bảng users
            ->whereHas('notificationPreferences', function ($q) use ($campaign) {
                $q->where('type', 'marketing')->where(function ($q) use ($campaign) {
                    if ($campaign->send_in_app) $q->orWhere('database', true);
                    if ($campaign->send_mail)   $q->orWhere('mail', true);
                });
            })
            ->chunkById(500, function ($users) use ($campaign, &$total) {
                foreach ($users as $user) {
                    $user->notify(new MarketingNotification($campaign));
                    $total++;
                }
            });

        $campaign->update(['status' => 'done', 'recipients_count' => $total, 'sent_at' => now()]);
    }

    public function failed(Throwable $e): void
    {
        NotificationCampaign::whereKey($this->campaignId)->update(['status' => 'failed']);
    }
}