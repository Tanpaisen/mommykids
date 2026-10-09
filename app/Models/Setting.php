<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'site_name',
        'logo',
        'favicon',
        'copyright',
        'hotline',
        'email',
        'address',
        'facebook_url',
        'zalo_url',
        'instagram_url',
        'top_announcement',
        'default_location',
        'search_placeholder',
        'home_banner_title',
        'promo_title',
        'promo_subtitle',
        'promo_badge_1',
        'promo_badge_2',
        'promo_badge_3',
        'promo_button_text',
        'footer_description',
        'meta_description',
        'header_scripts',
    ];

    /**
     * URL hiển thị logo.
     *
     * Cloudinary mới -> dùng trực tiếp.
     * Local cũ -> asset storage.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl(
            $this->logo
        );
    }

    /**
     * URL hiển thị favicon.
     */
    public function getFaviconUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl(
            $this->favicon
        );
    }

    private function resolveMediaUrl(
        ?string $value
    ): ?string {
        if (!$value) {
            return null;
        }

        /*
         * Cloudinary hoặc URL ngoài.
         */
        if (
            Str::startsWith(
                $value,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return $value;
        }

        /*
         * Hỗ trợ ảnh local cũ trong database.
         */
        $cleanPath = ltrim(
            str_replace(
                [
                    'public/',
                    'storage/',
                ],
                '',
                $value
            ),
            '/'
        );

        return asset(
            'storage/' . $cleanPath
        );
    }
}