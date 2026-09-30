<?php

namespace App\Events;

use App\Support\ChatRealtime;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $conversationId
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel(
                ChatRealtime::conversationChannel($this->conversationId)
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.conversation.updated';
    }

    public function broadcastWith(): array
    {
        // Không broadcast message/customer data ra WebSocket.
        // Client nhận tín hiệu rồi gọi endpoint có authorization.
        return [
            'updated' => true,
        ];
    }
}