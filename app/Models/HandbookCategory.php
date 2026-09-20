<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HandbookCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    // Lấy Chương cha
    public function parent()
    {
        return $this->belongsTo(HandbookCategory::class, 'parent_id');
    }

    // Lấy danh sách Mục con
    public function children()
    {
        return $this->hasMany(HandbookCategory::class, 'parent_id')->orderBy('sort_order', 'asc');
    }

    // Lấy danh sách bài viết thuộc mục này
    public function articles()
    {
        return $this->hasMany(Article::class, 'handbook_category_id')->orderBy('sort_order', 'asc');
    }
}