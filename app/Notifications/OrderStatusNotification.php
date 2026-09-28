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
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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
