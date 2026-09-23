<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasUlids;

    public const SENDER_CUSTOMER = 'customer';
    public const SENDER_BOT = 'bot';
    public const SENDER_STAFF = 'staff';
    public const SENDER_SYSTEM = 'system';

    protected $fillable = [
    'conversation_id',
    'sender_type',
    'sender_id',
    'admin_id',
    'message',
    'message_type',
    'is_read',
    'metadata',
];

    protected $casts = [
        'is_read' => 'boolean',
        'metadata' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function admin(): BelongsTo
{
    return $this->belongsTo(Admin::class, 'admin_id');
}
}
