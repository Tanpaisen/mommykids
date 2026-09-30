<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationCampaign extends Model
{
    use HasUlids;

    protected $fillable = ['title', 'body', 'action_url', 'send_in_app', 'send_mail',
                           'status', 'created_by', 'recipients_count', 'sent_at'];

    protected $casts = ['send_in_app' => 'boolean', 'send_mail' => 'boolean', 'sent_at' => 'datetime'];

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'campaign_id');
    }
}