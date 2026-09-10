<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',

        // Audit soft delete
        'deleted_by',
        'restored_by',
        'restored_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_tag'
        )->withTimestamps();
    }
}