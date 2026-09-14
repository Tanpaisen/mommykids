<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'age_from',
        'age_to',
        'description',
        'icon',
        'sort_order',
        'is_active',
        'deleted_by',
        'restored_by',
        'restored_at',
    ];

    protected $casts = [
        'age_from' => 'integer',
        'age_to' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_stage'
        )->withTimestamps();
    }
}