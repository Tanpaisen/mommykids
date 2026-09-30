<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = ['notification_id', 'user_id', 'channel', 'status', 'error_message'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}