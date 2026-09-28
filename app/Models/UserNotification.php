<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Cache;

class UserNotification extends DatabaseNotification
{
    protected static function booted(): void
    {
        $forget = function (UserNotification $n) {
            if ($n->notifiable_type === User::class) {
                Cache::forget(User::unreadCacheKey($n->notifiable_id));
            }
        };

        static::saved($forget);   // tạo mới + đánh dấu đã đọc
        static::deleted($forget); // xoá thật
    }
}