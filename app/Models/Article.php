<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'handbook_category_id',
        'title',
        'slug',
        'thumbnail',
        'summary',
        'content',
        'category',
        'status',
        'views',
        'sort_order',
    ];

    // Liên kết bài viết với Chương/Mục Cẩm nang
    public function category()
    {
        return $this->belongsTo(HandbookCategory::class, 'handbook_category_id');
    }
}