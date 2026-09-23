<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    use HasUlids;

    public const STATUS_BOT = 'bot';
    public const STATUS_WAITING_STAFF = 'waiting_staff';
    public const STATUS_STAFF_CONNECTED = 'staff_connected';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id', 'guest_token', 'status', 'staff_id', 'bot_fail_count',
        'started_at', 'accepted_at', 'closed_at', 'last_message_at',
    ];

    protected $casts = [
        'bot_fail_count' => 'integer',
        'started_at' => 'datetime',
        'accepted_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function staff(): BelongsTo
{
    return $this->belongsTo(Admin::class, 'staff_id');
}

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
