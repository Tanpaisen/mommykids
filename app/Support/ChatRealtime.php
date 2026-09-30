<?php

namespace App\Support;

final class ChatRealtime
{
    public static function conversationChannel(string $conversationId): string
    {
        return 'chat.conversation.' . hash_hmac(
            'sha256',
            $conversationId,
            (string) config('app.key')
        );
    }
}