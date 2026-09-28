<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;
use App\Models\NotificationLog;
use Throwable;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $tries = 1;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
        public string $newStatus,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof \App\Models\User) {
            return ['mail'];
        }
        $channels = ['database']; // giao dịch: luôn lưu trong app

        $pref = $notifiable->notificationPreferences->firstWhere('type', 'order_status');
        if ($pref?->mail ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->order->statusLabel();

        $mail = (new MailMessage)
            ->subject('Đơn hàng ' . $this->order->code . ': ' . $label['text'])
            ->greeting('Xin chào ' . $this->order->recipient_name . ',')
            ->line('Đơn hàng ' . $this->order->code . ' của bạn đã chuyển sang trạng thái: ' . $label['text'] . '.')
            ->salutation('Trân trọng, MommyKids');;

        // Trang chi tiết đơn yêu cầu đăng nhập nên chỉ gắn nút cho thành viên
        if ($notifiable instanceof \App\Models\User) {
            $mail->action('Xem đơn hàng', route('profile.orders.show', $this->order));
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable): array
    {
        $label = $this->order->statusLabel();

        return [
            'category' => 'transactional',
            'order_code' => $this->order->code,
            'title' => 'Đơn hàng ' . $this->order->code,
            'body' => 'Đơn hàng chuyển sang trạng thái: ' . $label['text'],
            'action_url' => route('profile.orders.show', $this->order, false),
        ];
    }
    public function failed(Throwable $e): void
    {
        NotificationLog::create([
            'user_id'       => $this->order->user_id,
            'channel'       => 'database',
            'status'        => 'failed',
            'error_message' => $e->getMessage(),
        ]);
    }
}
