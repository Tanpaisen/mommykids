<?php

namespace App\Notifications;

use App\Models\OrderCancellationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CancellationResultNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $tries = 1;

    /**
     * Create a new notification instance.
     */
    public function __construct(public OrderCancellationRequest $request)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        $channels = ['database'];

        $pref = $notifiable->notificationPreferences->firstWhere('type', 'order_status');
        if ($pref?->mail ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    private function text(): string
    {
        return $this->request->status === 'approved'
            ? 'Yêu cầu huỷ đơn đã được chấp nhận.'
            : 'Yêu cầu huỷ đơn bị từ chối.' . ($this->request->admin_note ? ' Lý do: ' . $this->request->admin_note : '');
    }

    public function toDatabase($notifiable): array
    {
        $order = $this->request->order;

        return [
            'category'   => 'transactional',
            'order_code' => $order->code,
            'title'      => 'Yêu cầu huỷ đơn ' . $order->code,
            'body'       => $this->text(),
            'action_url' => route('profile.orders.show', $order, false),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $order = $this->request->order;

        return (new MailMessage)
            ->subject('Yêu cầu huỷ đơn ' . $order->code)
            ->greeting('Xin chào ' . $order->recipient_name . ',')
            ->line($this->text())
            ->action('Xem đơn hàng', route('profile.orders.show', $order))
            ->salutation('Trân trọng, MommyKids');
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
}
