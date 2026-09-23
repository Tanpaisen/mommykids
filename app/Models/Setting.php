<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}