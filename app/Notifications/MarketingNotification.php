<?php

namespace App\Notifications;

use App\Models\NotificationCampaign;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class MarketingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const DAILY_CAP = 50; // tối đa số thông báo marketing/ngày/người

    public function __construct(public NotificationCampaign $campaign)
    {
        $this->onQueue('marketing'); // queue riêng, không làm nghẽn thông báo đơn hàng
    }

    public function via($notifiable): array
    {
        if (! $notifiable instanceof User) {
            return ['mail'];
        }

        $pref = $notifiable->notificationPreferences->firstWhere('type', 'marketing');

        $channels = [];
        if ($this->campaign->send_in_app && ($pref?->database ?? true)) {
            $channels[] = 'database';
        }
        if ($this->campaign->send_mail && ($pref?->mail ?? false)) {
            $channels[] = 'mail';
        }

        if ($channels && $this->reachedDailyCap($notifiable)) {
            // Ghi log để biết vì sao người này không nhận được, thay vì im lặng
            NotificationLog::create([
                'campaign_id'   => $this->campaign->id,
                'user_id'       => $notifiable->id,
                'channel'       => 'skipped',
                'status'        => 'skipped',
                'error_message' => 'Đã đạt giới hạn ' . self::DAILY_CAP . ' thông báo marketing/ngày',
            ]);

            return [];
        }

        return $channels;
    }

    private function reachedDailyCap(User $user): bool
    {
        return $user->notifications()
            ->where('type', self::class)
            ->where('created_at', '>=', now()->startOfDay())
            ->count() >= self::DAILY_CAP;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'category'    => 'marketing',
            'campaign_id' => $this->campaign->id,
            'title'       => $this->campaign->title,
            'body'        => $this->campaign->body,
            'action_url'  => $this->campaign->action_url ?: route('notifications.index', [], false),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->campaign->title)
            ->greeting('Xin chào,')
            ->line($this->campaign->body);

        if ($this->campaign->action_url) {
            $mail->action('Xem ngay', url($this->campaign->action_url));
        }

        return $mail
            ->line('Bạn nhận email này vì đã đăng ký nhận tin khuyến mãi. '
                 . 'Tắt tại: ' . route('notifications.preferences'))
            ->salutation('Trân trọng, MommyKids');
    }

    public function failed(Throwable $e): void
    {
        NotificationLog::create([
            'campaign_id'   => $this->campaign->id,
            'channel'       => 'queue',
            'status'        => 'failed',
            'error_message' => $e->getMessage(),
        ]);
    }
}